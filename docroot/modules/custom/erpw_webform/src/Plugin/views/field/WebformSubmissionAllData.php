<?php

namespace Drupal\erpw_webform\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Custom Views field plugin.
 *
 * @ViewsField("webform_submission_all_data")
 */
class WebformSubmissionAllData extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    if ($values->_entity->getData() !== NULL) {
      $data = $values->_entity->getData();
      $data['sid'] = $values->_entity->get('sid')->getValue()[0]['value'];
      $output = json_encode($data);
    }
    else {
      $output = t('Not available.');
    }
    return $output;
  }

}
