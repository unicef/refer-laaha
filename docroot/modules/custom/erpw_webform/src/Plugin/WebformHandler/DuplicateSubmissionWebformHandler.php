<?php

namespace Drupal\erpw_webform\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Duplicate Submission Webform handler.
 *
 * @WebformHandler(
 *   id = "handle_duplicate_services",
 *   label = @Translation("Duplicate Services Handler"),
 *   category = @Translation("eRPW Webform Handler"),
 *   description = @Translation("Prevent users from submitting Services with same Organisation for lowest level location."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class DuplicateSubmissionWebformHandler extends WebformHandlerBase {

  /**
   * Implements Drupal\webform\Plugin\WebformHandlerInterface::validateForm().
   */
  public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $location_values = $webform_submission->getElementData('location');
    $country_id = $location_values['location_options'];
    if ($country_id == '1' || $country_id == '16') {
      $last_level = 'level_3';     
    }
    else {
      $last_level = 'level_4';
    }
    $last_level_value = $location_values[$last_level] ?? '';
    $organisation_value = $webform_submission->getElementData('organisation');

    // Check if there is an existing submission with the same location
    // for level_4, and organisation values.
    $existing_submission_ids = [];
    if ($last_level_value != "") {
      $query = \Drupal::database()->select('webform_submission_data', 'wsd');
      $query->fields('wsd', ['sid']);
      $query->condition('wsd.webform_id', $webform_submission->getWebform()->id());
      $query->condition('wsd.property', $last_level);
      $query->condition('wsd.value', $last_level_value);

      $query->join('webform_submission_data', 'wsd2', 'wsd.sid = wsd2.sid');
      $query->condition('wsd2.name', 'organisation');
      $query->condition('wsd2.value', $organisation_value);

      $query->join('webform_submission_data', 'wsd3', 'wsd.sid = wsd3.sid');
      $query->condition('wsd3.property', 'workflow_state');
      $query->condition('wsd3.value', 'reject', '!=');

      $existing_submission_ids = $query->execute()->fetchAll();

      // Exclude current submission id from the list.
      $existing_sids = array_map(function ($submission) {
        return $submission->sid;
      }, $existing_submission_ids);

      $existing_submission_ids = array_diff($existing_sids, [$webform_submission->id()]);
    }

    // If an existing submission is found,
    // display an error message and stop the form validation.
    if (!empty($existing_submission_ids)) {
      $form_state->setError($form, $this->t('A service for your location and organisation already exists.'));
    }

    // Continue with the default form validation for the submission handler.
    parent::validateForm($form, $form_state, $webform_submission);
  }

}
