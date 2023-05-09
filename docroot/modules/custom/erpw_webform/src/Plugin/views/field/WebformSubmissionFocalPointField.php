<?php

namespace Drupal\erpw_webform\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Custom Views field plugin.
 *
 * @ViewsField("webform_submission_focal_point_field")
 */
class WebformSubmissionFocalPointField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    if (isset($values->_entity->getData()['field_name_of_the_focal_point'])) {
      $output = $values->_entity->getData()['field_name_of_the_focal_point'];
    }
    else {
      $output = 'Not available.';
    }
    return $output;
  }

}
