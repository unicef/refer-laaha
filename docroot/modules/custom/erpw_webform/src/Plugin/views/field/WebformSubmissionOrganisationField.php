<?php

namespace Drupal\erpw_webform\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Custom Views field plugin.
 *
 * @ViewsField("webform_submission_organisation_field")
 */
class WebformSubmissionOrganisationField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    if (isset($values->_entity->getData()['organisation'])) {
      if (!is_null(\Drupal::entityTypeManager()->getStorage('node')->load($values->_entity->getData()['organisation']))) {
        $output = \Drupal::entityTypeManager()->getStorage('node')->load($values->_entity->getData()['organisation'])->get('title')->getValue()[0]['value'];
      }
      else {
        $output = t('Not available.');
      }
    }
    else {
      $output = t('Not available.');
    }
    return $output;
  }

}
