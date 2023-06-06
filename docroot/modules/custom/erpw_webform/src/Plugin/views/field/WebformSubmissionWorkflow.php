<?php

namespace Drupal\erpw_webform\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Custom Views field plugin.
 *
 * @ViewsField("webform_submission_workflow")
 */
class WebformSubmissionWorkflow extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    if (isset($values->_entity->getData()['erpw_workflow'])) {
      $output = $values->_entity->getData()['erpw_workflow']['workflow_state'];
      if ($output == 'approve') {
        $output = 'Approved';
      }
      elseif ($output == 'reject') {
        $output = 'Rejected';
      }
      elseif ($output == 'draft') {
        $output = 'Draft';
      }
      elseif ($output == 'in_review') {
        $output = 'In Review with Country Admin';
      }
      elseif ($output == 'in_review_with_gbvi_coordinator'){
        $output = 'In Review with GBVi Coordinator';
      }
    }
    else {
      $output = t('Not available.');
    }
    return $output;
  }

}
