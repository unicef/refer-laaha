<?php

namespace Drupal\erpw_webform\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Custom Views field plugin.
 *
 * @ViewsField("webform_submission_service_rating_question_count_field")
 */
class WebformSubmissionServiceRatingQuestionCountField extends FieldPluginBase {

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
            $questions_count = $this->getRatingQuestionCount($webform->getElementsDecoded());
            if (isset($questions_count)) {
                $output = [
                    '#markup' => '<div class="service-rating-feedback-question-count">' . t('Number of questions : ') . '<span>' . $questions_count . '</span></div>',
                ];
            } 
        }
        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function getRatingQuestionCount(array $webform_elements) {
        $count = 0;
        foreach ($webform_elements as $element_key => $element) {
            if (str_contains($element_key, "rating_question_") || str_contains($element_key, "multiple_choice_question_")) {
                $count++;
            }
        }
        return $count;
    }

}


