<?php

namespace Drupal\erpw_webform\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Custom Views field plugin.
 *
 * @ViewsField("webform_submission_mode_field")
 */
class WebformSubmissionModeField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    if (isset($values->_entity->getData()['field_mode'])) {
      $output = $values->_entity->getData()['field_mode'];
      if (is_array($output)) {
        $newOutput = implode(', ', $output);
        return $newOutput . '.';
      }
    }
    else {
      $output = t('Not available.');
    }
    return $output;
  }

}
