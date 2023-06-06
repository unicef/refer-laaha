<?php

namespace Drupal\erpw_webform\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Custom Views field plugin.
 *
 * @ViewsField("webform_submission_location_field")
 */
class WebformSubmissionLocationField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    if (isset($values->_entity->getData()['location'])) {
      $location = $values->_entity->getData()['location'];
      $loadTerm = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
      $output = '';
      if (is_array($location)) {
        if ($location['location_options'] != '') {
          $country = \Drupal::entityTypeManager()->getStorage('location')->load($location['location_options'])->getName();
          $output = $country . '.';
        }
      }
      if (!empty($location['level_1'])) {
        $level_1 = $loadTerm->load($location['level_1'])->getName();
        $output = $level_1 . ', ' . $output;
      }
      if (!empty($location['level_2'])) {
        $level_2 = $loadTerm->load($location['level_2'])->getName();
        $output = $level_2 . ', ' . $output;
      }
      if (!empty($location['level_3'])) {
        $level_3 = $loadTerm->load($location['level_3'])->getName();
        $output = $level_3 . ', ' . $output;
      }
      if (!empty($location['level_4'])) {
        $level_4 = $loadTerm->load($location['level_4'])->getName();
        $output = $level_4 . ', ' . $output;
      }
    }
    else {
      $output = t('Not available.');
    }
    return $output;
  }

}
