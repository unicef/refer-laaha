<?php

namespace Drupal\erpw_webform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\Entity\Webform;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Calulate and display the averages grouped by questions of the Feedback form
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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->routeMatch = $container->get('current_route_match');
    $instance->serviceRating = $container->get('erpw_webform.service_rating_service');
    return $instance;
  }

  /**
   * Function to calulate and display the averages grouped by questions of the Feedback form.
   */
  public function displayAverageRatings() {
    // Replace the 'webform_id' with the actual webform ID.
    $webform_id = 'webform_service_rating_301';
    $webform = Webform::load($webform_id);
    $elements = $webform->getElementsDecodedAndFlattened();

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
            'value' => 10421,
          ];
          // Organisation filter.
          $element3 = [
            'key' => 'service_organisation',
            'value' => 336,
          ];

          $submission_ids = $this->serviceRating->getSubmissionIds($webform_id, $element_key, $element2, $element3);

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

    $build = [
      '#markup' => '<ul>',
    ];

    foreach ($average_ratings as $feedback => $average_rating) {
      $node = Node::load($feedback);
      if ($node) {
        $feedback_name = $node->getTitle();
      }
      else {
        $feedback_name = NULL;
      }
      $build['#markup'] .= '<li>' . $feedback_name . ': ' . $average_rating . '</li>';
    }

    $build['#markup'] .= '</ul>';

    return $build;
  }

}
