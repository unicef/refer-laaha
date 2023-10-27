<?php

namespace Drupal\erpw_webform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\Entity\Webform;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Calulate and display the averages grouped by location of the services,
 * for which the Feedback formis filled.
 */
class ServiceRatingLocationController extends ControllerBase {

  /**
   * The RouteMatch service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The Service Rating Service.
   *
   * @var \Drupal\erpw_webform\ServiceRatingService
   */
  protected $serviceRating;

  /**
   * The Location Service.
   *
   * @var \Drupal\erpw_location\LocationService
   */
  protected $location;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->routeMatch = $container->get('current_route_match');
    $instance->serviceRating = $container->get('erpw_webform.service_rating_service');
    $instance->location = $container->get('erpw_location.location_services');
    return $instance;
  }

  /**
   * Function to calulate and display the averages grouped by location of the services,
   * for which the Feedback form is filled.
   */
  public function displayServiceLocationAverages() {
    // Load the webform based on the provided webform ID.
    $webform_id = $this->routeMatch->getParameter('webform_id');
    $webform = Webform::load($webform_id);
    $org_id = $this->serviceRating->organisationForFiltering();

    // Check if the webform exists and matches the service rating form pattern.
    if ($webform && preg_match('/^webform_service_rating_\d+$/', $webform->id())) {
      // Organisation filter.
      $element = [
        'key' => 'service_organisation',
        'value' => $org_id,
      ];
      // Get all submissions for the webform.
      $submission_ids = $this->serviceRating->getSubmissionIdsForSingleElement($webform_id, $element);

      // Initialize an array to store normalized element values.
      $normalized_element_values = [];

      // Iterate through each submission.
      foreach ($submission_ids as $submission_id) {
        $submission = WebformSubmission::load($submission_id);

        // Get the service_location_tid element value for this submission.
        $service_location_tid = $submission->getElementData('service_location_tid');

        // Initialize an array to store normalized values for this submission.
        $normalized_values = [];

        // Get all elements of the webform.
        $elements = $webform->getElementsDecodedAndFlattened();

        // Iterate through each element of the webform.
        foreach ($elements as $element_key => $element) {
          if ($element['#type'] == 'radios') {
            $element_value = $submission->getElementData($element_key);

            // Check if the element value is numeric.
            if (is_numeric($element_value)) {
              // Normalize the rating to the range of 1 to 5.
              $normalized_rating = $this->serviceRating->normalizeRating($element_value, $element['#options']);
              $normalized_values[] = $normalized_rating;
            }
          }
        }

        // Store the normalized values for this submission under the service_location_tid value.
        $normalized_element_values[$service_location_tid][] = $normalized_values;
      }

      // Calculate the average rating for each service_location_tid group and round to the nearest integer.
      $averages = [];
      foreach ($normalized_element_values as $location_tid => $ratings) {
        $average_rating = round($this->serviceRating->calculateAverageRating($ratings));
        $averages[$location_tid] = $average_rating;
      }

      $service_type_id = str_replace("webform_service_rating_", "", $webform_id);
      if (is_numeric($service_type_id)) {
        $node = Node::load($service_type_id);
      }
      else {
        $node = NULL;
      }
      if ($node) {
        $service_type_name = $node->getTitle();
      }
      else {
        $service_type_name = $this->t('Invalid Organisation');
      }
      $service_type_average = $this->serviceRating->calculateTotalAverageRating($averages);
      $country_ids = array_keys($this->location->getLocationEntities());

      $total_review_count = 0;
      $locations_list = [];
      foreach ($averages as $location_id => $average_rating) {
        $location_review_count = 0;
        // Location filter.
        $element2 = [
          'key' => 'service_location_tid',
          'value' => $location_id,
        ];
        // Organisation filter.
        $element3 = [
          'key' => 'service_organisation',
          'value' => $org_id,
        ];

        $submission_ids = $this->serviceRating->getSubmissionIdsForMultipleElements($webform_id, NULL, $element2, $element3);
        $location_review_count = count($submission_ids) > 1 ? count($submission_ids) . ' Reviews' : count($submission_ids) . ' Review';
        $total_review_count += count($submission_ids);

        if (in_array($location_id, $country_ids)) {
          $location_tid = $this->location->getLocationSingleEntityIdByTid($location_id);
        }
        else {
          $location_tid = $location_id;
        }
        $location_name = $this->location->getTaxonomyTermById($location_id);

        // Generate the URL with the route name and the location_id parameter.
        $url = Url::fromRoute('erpw_webform.questions.calculate_average_questions_rating', [
          'location_id' => $location_id,
          'webform_id' => $webform_id,
        ]);
        $href = $url->toString();
        $locations_list[] = [
          'location_name' => $location_name,
          'location_link' => $href,
          'location_rating' => $average_rating,
          'location_review_count' => $location_review_count,      
        ];
      }


      return [
        '#theme' => 'location_rating_page',
        '#title' => $this->t('Service Ratings'),
        '#service_type_name' => $service_type_name,
        '#service_type_average' => round($service_type_average),
        '#service_total_reviews' =>  $total_review_count > 1 ? $total_review_count . ' Reviews' : $total_review_count . ' Review',
        '#location_list' => $locations_list,
      ];
    }
  }

}
