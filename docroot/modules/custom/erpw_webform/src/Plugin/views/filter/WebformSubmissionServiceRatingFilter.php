<?php

namespace Drupal\erpw_webform\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\views\ViewExecutable;

/**
 * Custom filter for the service rating type webforms.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("webform_submission_service_rating_filter")
 */
class WebformSubmissionServiceRatingFilter extends FilterPluginBase {

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->valueTitle = t('Webform ID starts with');
  }

  /**
   * {@inheritdoc}
   */
  public function operatorOptions() {
    // You can define custom operators here.
    return [
      '=' => $this->t('Is equal to'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $form['value']['#type'] = 'textfield';
    $form['value']['#title'] = $this->t('Value');
    $form['value']['#default_value'] = $this->value;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    if (!empty($this->value)) {
      if ($this->value === 'webform_service_rating_') {
        $this->ensureMyTable();
        $this->query->addWhereExpression($this->options['group'], "SUBSTRING({$this->tableAlias}.webform_id, 1, LENGTH('webform_service_rating_')) = 'webform_service_rating_'");
      }
    }
  }

}
