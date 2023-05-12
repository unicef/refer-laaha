<?php

namespace Drupal\erpw_webform\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Custom Views field plugin.
 *
 * @ViewsField("webform_submission_hotline_contact_field")
 */
class WebformSubmissionHotlineContactField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    if (isset($values->_entity->getData()['field_phone_number'])) {
      $output = $values->_entity->getData()['field_phone_number'];
    }
    else {
      $output = t('Not available.');
    }
    return $output;
  }

}
