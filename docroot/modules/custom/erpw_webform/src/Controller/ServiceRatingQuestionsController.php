<?php

namespace Drupal\erpw_webform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\Entity\Webform;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Calulate and display the averages grouped by questions of the Feedback form.
 */
class ServiceRatingQuestionsController extends ControllerBase {

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
   * Function to calulate and display the averages grouped by questions of the Feedback form.
   */
  public function displayAverageRatings() {
    $org_id = $this->serviceRating->organisationForFiltering();
    $webform_id = $this->routeMatch->getParameter('webform_id');
    $webform = Webform::load($webform_id);
    $elements = $webform->getElementsDecodedAndFlattened();
    $location_id = $this->routeMatch->getParameter('location_id');

    // Initialize an array to store element titles and average ratings.
    $feedback_normalized_values = [];
    foreach ($elements as $element_key => $element) {
      if ($element['#type'] == 'radios') {
        if (isset($element['#feedback_area'])) {
          $element_feedback = $element['#feedback_area'];

          // Initialize an array to store element values for this feedback.
          $normalized_element_values = [];
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

          $submission_ids = $this->serviceRating->getSubmissionIdsForMultipleElements($webform_id, $element_key, $element2, $element3);

          foreach ($submission_ids as $submission_id) {
            $submission = WebformSubmission::load($submission_id);
            $element_value = $submission->getElementData($element_key);

            if (is_numeric($element_value)) {
              // Normalize the rating to the range of 1 to 5.
              $normalized_rating = $this->serviceRating->normalizeRating($element_value, $element['#options']);
              $normalized_element_values[] = $normalized_rating;
            }
          }

          if (!empty($normalized_element_values)) {
            // Group the normalized element values by feedback value.
            $feedback_normalized_values[$element_feedback][] = $normalized_element_values;
          }
        }
      }
      else {
        continue;
      }
    }

    // Initialize an array to store the average ratings.
    $average_ratings = [];

    // Calculate the average rating for each feedback group.
    foreach ($feedback_normalized_values as $feedback => $element_values) {
      $average_ratings[$feedback] = $this->serviceRating->calculateAverageRating($element_values);
    }

    $country_ids = array_keys($this->location->getLocationEntities());
    if (in_array($location_id, $country_ids)) {
      $location_tid = $this->location->getLocationSingleEntityIdByTid($location_id);
    }
    else {
      $location_tid = $location_id;
    }
    $location_name = $this->location->getTaxonomyTermById($location_tid);
    $location_average = $this->serviceRating->calculateTotalAverageRating($average_ratings);

    // Review Count:
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
    $total_review_count = count($submission_ids) > 1 ? count($submission_ids) . ' Reviews' : count($submission_ids) . ' Review';

    $feedback_area_list = [];
    foreach ($average_ratings as $feedback => $average_rating) {
      $node = Node::load($feedback);
      if ($node) {
        $feedback_name = $node->getTitle();
      }
      else {
        $feedback_name = NULL;
      }

      $feedback_area_list[] = [
        'feedback_name' => $feedback_name,
        'feedback_rating' => $average_rating,   
      ];
    }

    $feedback_area_data = [
      'location_name' => $location_name,
      'location_average' => round($location_average),
      'location_total_reviews' => $total_review_count,
      'feedback_area_list' => $feedback_area_list,
    ];

    return [
      '#theme' => 'page__ratings_by_feedback_area',
      '#title' => $this->t('Feedback Area Ratings'),
      'data' => $feedback_area_data,
    ];
  }

}
