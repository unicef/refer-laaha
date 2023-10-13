<?php

namespace Drupal\erpw_webform\Controller;

use Drupal\Core\Controller\ControllerBase;
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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->routeMatch = $container->get('current_route_match');
    $instance->serviceRating = $container->get('erpw_webform.service_rating_service');
    return $instance;
  }

  /**
   * Function to calulate and display the averages grouped by service type of the Feedback form.
   */
  public function displayAverageWebformRatings() {
    // Get all webforms.
    $webforms = \Drupal::entityTypeManager()->getStorage('webform')->loadMultiple();

    // Initialize an array to store webform names and their average ratings.
    $webform_ratings = [];

    // Iterate through all webforms.
    foreach ($webforms as $webform) {
      // Check if the webform's machine name matches the pattern.
      if ($webform->isOpen() && preg_match('/^webform_service_rating_\d+$/', $webform->id())) {
        // Get all submissions for the webform.
        $submission_ids = $this->serviceRating->getSubmissionIds($webform->id());

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
        $webform_ratings[$webform->label()] = $average_rating;
      }
    }

    // Create an HTML list for displaying the webform ratings.
    $output = '<ul>';
    foreach ($webform_ratings as $webform_name => $average_rating) {
      $output .= '<li>' . $webform_name . ': ' . $average_rating . '</li>';
    }
    $output .= '</ul';

    return [
      '#markup' => $output,
    ];
  }

}
