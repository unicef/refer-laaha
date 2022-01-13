<?php

namespace Drupal\visitors\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Date Filter.
 */
class DateFilter extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'visitors_date_filter_form';
  }

  /**
   * Form builder.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $from = \Drupal::request()->query->get('timestamp');
    $form = [];

    $form['visitors_date_from_filter'] = [
      '#title'            => t('Filter By'),
      '#type'             => 'select',
      '#default_value'    => $from,
      '#options'          => [
        '' => t('Today'),
        'cur_week' => t('This Week'),
        'cur_mon' => t('This Month'),
        'cur_yr' => t('This Year'),
      ],
    ];

    $form['submit'] = [
      '#type'             => 'submit',
      '#value'            => t('Apply'),
    ];

    return $form;
  }

  /**
   * Validate form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $fromvalue = $form_state->getValue('visitors_date_from_filter');
    $tovalue   = $form_state->getValue('to');
    $from      = [];
    $to        = [];
  }

  /**
   * Submit form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $from = $form_state->getValue('visitors_date_from_filter');
    $url = Url::fromRoute('visitors.top_pages', [], ['query' => ['timestamp' => $from]]);
    $form_state->setRedirectUrl($url);
  }

}
