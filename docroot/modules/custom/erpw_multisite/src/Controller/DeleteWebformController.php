<?php

namespace Drupal\erpw_multisite\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\webform\Entity\Webform;

/**
 * Controller to delete webforms and submissions of other domains after Multisite.
 */
class DeleteWebformController extends ControllerBase {

  /**
   * Function to delete webforms and submissions of other domains after Multisite.
   */
  public function deleteWebformsAndSubmissions() {
    // Get a list of webforms matching the pattern.
    $current_domain = \Drupal::service('domain.negotiator')->getActiveDomain()->id();
    $pattern = '/^(.*?)_erefer_org$/';

    if (preg_match($pattern, $current_domain, $matches)) {
      $subdomain = $matches[1];
      if ($subdomain == 'zm') {
        $subdomain = 'zw';
      }
    }

    $query = \Drupal::database()->select('webform', 'w')
      ->fields('w', ['webform_id'])
      ->condition('webform_id', $subdomain . '%', 'NOT LIKE')
      ->condition('webform_id', 'webform_service_rating_%', 'NOT LIKE')
      ->condition('webform_id', 'template_%', 'NOT LIKE')
      ->execute();

    $webform_ids = $query->fetchCol();

    $message = '';

    // Delete submissions for each webform.
    foreach ($webform_ids as $index => $webform_id) {
      $webform = Webform::load($webform_id);
      if ($webform) {
        // Get the submission storage for the webform.
        $submission_storage = \Drupal::entityTypeManager()->getStorage('webform_submission');

        // Get submissions for the webform.
        $submission_ids = $submission_storage->getQuery()
          ->condition('webform_id', $webform_id)
          ->accessCheck(TRUE)
          ->execute();

        $submission_count = count($submission_ids);

        // Delete each submission.
        $submissions = $submission_storage->loadMultiple($submission_ids);
        foreach ($submissions as $submission) {
          $submission->delete();
        }

        $message .= '<div>' . $this->t('@count Submissions deleted successfully for webform id: @webform', [
          '@count' => $submission_count,
          '@webform' => $webform_id,
        ]) . '</div><br>';
        // Delete the webform.
        $webform->delete();
        $message .= '<div>' . $this->t('Webform @webform_id deleted successfully', [
          '@webform_id' => $webform_id,
        ]) . '</div><br>';
      }
    }
    if ($message == '') {
      $message = 'No webforms left to delete.';
    }

    $render_array = [
      '#markup' => $message,
    ];
    return $render_array;
  }

}
