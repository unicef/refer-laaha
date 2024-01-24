<?php

namespace Drupal\erpw_webform\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Calulate and display the averages grouped by service type of the Feedback form.
 */
class ServiceRatingServiceTypeController extends ControllerBase {

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
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Drupal\domain\DomainNegotiatorInterface definition.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->routeMatch = $container->get('current_route_match');
    $instance->serviceRating = $container->get('erpw_webform.service_rating_service');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->domainNegotiator = $container->get('domain.negotiator');
    return $instance;
  }

  /**
   * Function to calulate and display the averages grouped by service type of the Feedback form.
   */
  public function displayAverageWebformRatings() {
    // Get all webforms.
    $webforms = \Drupal::entityTypeManager()->getStorage('webform')->loadMultiple();
    $org_id = $this->serviceRating->organisationForFiltering();

    // Initialize an array to store webform names and their average ratings.
    $webform_ratings = [];

    // Iterate through all webforms.
    foreach ($webforms as $webform) {

      $webform_id = $webform->id();
      // Check if the webform's machine name matches the pattern.
      if ($webform->isOpen() && preg_match('/^webform_service_rating_\d+$/', $webform_id)) {
        $element = [
          'key' => 'service_organisation',
          'value' => $org_id,
        ];
        // Get all submissions for the webform.
        $submission_ids = $this->serviceRating->getSubmissionIdsForSingleElement($webform_id, $element);

        // Initialize an array to store normalized element values.
        $normalized_element_values = [];

        // Serialize and hash the sorted submission IDs.
        $submission_ids_hash = hash('sha256', Json::encode($submission_ids));
        $active_domain_id = $this->domainNegotiator->getActiveDomain()->id();
        $cache_tags = ['webform_submission:' . $submission_ids_hash];
        $cache_id = 'service_rating_location_normalized_values_' . $active_domain_id . $webform_id . $org_id;
        $cache_data = \Drupal::cache()->get($cache_id);

        if (!$cache_data) {
          // Iterate through each submission.
          foreach ($submission_ids as $submission_id) {
            $submission = WebformSubmission::load($submission_id);

            // Get all elements of the webform.
            $elements = $webform->getElementsDecodedAndFlattened();

            // Initialize an array to store normalized values for this submission.
            $normalized_values = [];

            // Iterate through each element of the webform.
            foreach ($elements as $element_key => $element) {
              if ($element['#type'] == 'radios') {
                $element_value = $submission->getElementData($element_key);

                // Check if the element value is numeric.
                if (!empty($element_value) && is_numeric($element_value)) {
                  // Normalize the rating to the range of 1 to 5.
                  $normalized_rating = $this->serviceRating->normalizeRating($element_value, $element['#options']);
                  $normalized_values[] = $normalized_rating;
                }
              }
            }

            // Store the normalized values for this submission.
            $normalized_element_values[] = $normalized_values;
          }
          // Cache the computed value.
          \Drupal::cache()->set($cache_id, $normalized_element_values, Cache::PERMANENT, $cache_tags);
        }
        else {
          // Retrieve the data from the cache.
          $normalized_element_values = $cache_data->data;
        }

        // Calculate the average rating for this webform and round to the nearest whole number.
        $average_rating = round($this->serviceRating->calculateAverageRating($normalized_element_values));
        $webform_ratings[$webform_id] = $average_rating;
      }
    }

    // Create an HTML list for displaying the webform ratings.
    $node = Node::load($org_id);
    if ($node) {
      $organisation_name = $node->getTitle();
    }
    else {
      $organisation_name = $this->t('Invalid Organisation');
    }
    $organisational_average = $this->serviceRating->calculateTotalAverageRating($webform_ratings);

    $totalReviewsCount = 0;
    $servicesCount = 0;
    $organisation_services_list = [];
    foreach ($webform_ratings as $webform_id => $average_rating) {

      // Omit the webform if it does not belong to current domain.
      $service_type_domain = $this->serviceRating->fetchServiceTypeDomains($webform_id);
      if (in_array($active_domain_id, $service_type_domain)) {
        $webform = Webform::load($webform_id);
        // Review Count:
        $element = [
          'key' => 'service_organisation',
          'value' => $org_id,
        ];
        $submission_ids = $this->serviceRating->getSubmissionIdsForSingleElement($webform->id(), $element);
        $serviceReviewsCount = count($submission_ids) > 1 ? count($submission_ids) . ' Reviews' : count($submission_ids) . ' Review';
        $totalReviewsCount += count($submission_ids);
        $webform_name = str_replace('Service Rating ', '', $webform->label());
        $url = Url::fromRoute('erpw_webform.questions.calculate_average_location_rating', ['webform_id' => $webform_id]);
        $href = $url->toString();
        $servicesCount += 1;

        $organisation_services_list[] = [
          'service_name' => $webform_name,
          'service_link' => $href,
          'service_rating' => $average_rating,
          'service_review_count' => $serviceReviewsCount,
        ];
      }
      else {
        continue;
      }
    }

    // @todo Cache computed value. - Done
    return [
      '#theme' => 'service_rating_page',
      '#title' => $this->t('Service Ratings'),
      '#organisation_name' => $organisation_name,
      '#organisation_average' => round($organisational_average),
      '#organisation_total_services_count' => $servicesCount,
      '#organisation_total_reviews' => $totalReviewsCount > 1 ? $totalReviewsCount . ' Reviews' : $totalReviewsCount . ' Review',
      '#service_list' => $organisation_services_list,
    ];
  }

}
