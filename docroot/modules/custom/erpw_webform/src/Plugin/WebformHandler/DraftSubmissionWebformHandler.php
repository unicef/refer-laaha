<?php
namespace Drupal\erpw_webform\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Draft submission Webform handler.
 *
 * @WebformHandler(
 *   id = "redirect_to_drafts",
 *   label = @Translation("Redirect to Drafts"),
 *   category = @Translation("eRPW Webform Handler"),
 *   description = @Translation("Redirect to Drafts on a 'Save as Draft' submission."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */

class DraftSubmissionWebformHandler extends WebformHandlerBase{
  
  /**
   * Implements Drupal\webform\Plugin\WebformHandlerInterface::confirmForm().
   */

  public function confirmForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission){

    $workflow_selected = $webform_submission->getElementData('erpw_workflow');
    
    // Redirect the user to the submission results page if transition is Save as Draft
    if($workflow_selected['transition'] == "save_as_draft"){
      $form_state->setRedirect('entity.webform.results_submissions',
      ['webform' => $webform_submission->getWebform()->id()],
      ['query' => ['workflow-erpw_workflow' => 'draft']]
    );
    }
    else {
      // Default behavior for all other transitions
      parent::submitForm($form, $form_state, $webform_submission);
    }
    
  }

}
