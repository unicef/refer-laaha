<?php

namespace Drupal\erpw_webform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\Entity\Webform;

class ServiceRatingQuestionsController extends ControllerBase {

  public function displayAverageRatings() {
    // Replace 'your_webform_id' with the actual webform ID.
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

          $submission_ids = $this->getSubmissionIds($webform_id, $element_key);

          foreach ($submission_ids as $submission_id) {
            $submission = WebformSubmission::load($submission_id);
            $element_value = $submission->getElementData($element_key);

            if (is_numeric($element_value)) {
              // Normalize the rating to the range of 1 to 5.
              $normalized_rating = $this->normalizeRating($element_value, $element['#options']);
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
      $average_ratings[$feedback] = $this->calculateAverageRating($element_values);
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

  private function getSubmissionIds($webform_id, $element_key) {
    // Get the database connection.
    $database = \Drupal::database();

    // Use the "query" method to build the database query.
    $query = $database->select('webform_submission_data', 'wsd')
      ->fields('wsd', ['sid'])
      ->condition('wsd.webform_id', $webform_id)
      ->condition('wsd.name', $element_key, '=')
      ->condition('wsd.value', NULL, 'IS NOT NULL');

    // Execute the query and fetch the result.
    $result = $query->execute();

    // Initialize an array to store submission IDs.
    $submission_ids = [];

    foreach ($result as $row) {
      $submission_ids[] = $row->sid;
    }

    return $submission_ids;
  }

  private function normalizeRating($value, $options) {
    $max_value = count($options);
    return (($value - 1) / ($max_value - 1)) * 4 + 1;
  }

  private function calculateAverageRating($normalized_element_values) {
    $flattened_values = array_merge(...$normalized_element_values);
    $average_rating = 0;

    if (!empty($flattened_values)) {
      // Calculate the average rating and round it to one decimal point.
      $average_rating = round(array_sum($flattened_values) / count($flattened_values), 1);
    }

    return $average_rating;
  }


}
