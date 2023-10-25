<?php

namespace Drupal\erpw_webform\Controller;

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->routeMatch = $container->get('current_route_match');
    $instance->serviceRating = $container->get('erpw_webform.service_rating_service');
    $instance->entityTypeManager = $container->get('entity_type.manager');
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
      // Check if the webform's machine name matches the pattern.
      if ($webform->isOpen() && preg_match('/^webform_service_rating_\d+$/', $webform->id())) {
        $element = [
          'key' => 'service_organisation',
          'value' => $org_id,
        ];
        // Get all submissions for the webform.
        $submission_ids = $this->serviceRating->getSubmissionIdsForSingleElement($webform->id(), $element);

        // Initialize an array to store normalized element values.
        $normalized_element_values = [];

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

        // Calculate the average rating for this webform and round to the nearest whole number.
        $average_rating = round($this->serviceRating->calculateAverageRating($normalized_element_values));
        $webform_ratings[$webform->id()] = $average_rating;
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
    $output = '<div class="service-ratings-location-header">';
    $output .= '<h1>' . $organisation_name . '</h1>';
    $output .= '<p>' . $organisational_average . ' Services</p>';
    $output .= '</div>';
    $output .= '<ul class="service-ratings-services-list">';
    foreach ($webform_ratings as $webform_id => $average_rating) {
      $webform = Webform::load($webform_id);
      $query = $this->entityTypeManager->getStorage('webform_submission')
        ->getQuery();
      $query->condition('webform_id', $webform_id);
      $submissionIds = $query->execute();
      $totalReviews = count($submissionIds) > 1 ? count($submissionIds) . ' Reviews' : count($submissionIds) . ' Review';
      $webform_name = str_replace('Service Rating ', '', $webform->label());
      $url = Url::fromRoute('erpw_webform.questions.calculate_average_location_rating', ['webform_id' => $webform_id]);
      $href = $url->toString();

      // Create the anchor element with the webform name as the text and the generated URL.
      $anchor = '<a href="' . $href . '">' . $webform_name . '</a>';
      $output .= '<li><p class="service-name">' . $anchor . '</p><p class="service-average-rating">' . $average_rating . '</p>
        <div id="service-star-rating-' . $average_rating . '" class="star-rating">
          <span class="star">&#9733;</span>
          <span class="star">&#9733;</span>
          <span class="star">&#9733;</span>
          <span class="star">&#9733;</span>
          <span class="star">&#9733;</span>
        </div>
      <p class="reviews">(' . $totalReviews . ')</a>
      </li>';
    }
    $output .= '</ul>';

    return [
      '#title' => $this->t('Service Ratings'),
      '#type' => 'markup',
      '#markup' => $output,
    ];
  }

}
