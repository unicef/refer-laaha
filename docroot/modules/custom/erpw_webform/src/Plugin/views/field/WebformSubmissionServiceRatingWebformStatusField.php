<?php

namespace Drupal\erpw_webform\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Custom Views field plugin.
 *
 * @ViewsField("webform_submission_service_rating_status_field")
 */
class WebformSubmissionServiceRatingWebformStatusField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    if ($values->_entity->get('webform_id')->getValue() != NULL && str_contains($values->_entity->get('webform_id')->getValue()[0]["target_id"], "webform_service_rating_")) {
      $webform_id = $values->_entity->get('webform_id')->getValue()[0]["target_id"];
      $webform = \Drupal::entityTypeManager()->getStorage('webform')->load($webform_id);
      if ($webform->isOpen()) {
        $output = t("Published");
      }
      else {
        $output = t("Draft");
      }
    }
    else {
      $output = t("Not available.");
    }

    return $output;
  }

}
