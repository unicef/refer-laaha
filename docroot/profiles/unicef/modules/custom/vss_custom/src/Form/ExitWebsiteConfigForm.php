<?php

namespace Drupal\vss_custom\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\State;

/**
 * Defines a form that configures forms module settings.
 */
class ExitWebsiteConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'exit_website_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() : array {
    return ['exit_website.config'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    //ptl_productivity_scans_per_hour
    $exit_url = \Drupal::state()->get('exit_url');

    $form['#prefix'] = '<div id = "exit-website">';
    $form['#suffix'] = '</div>';
    $form['exit_url'] = [
        '#type' => 'textfield',
        '#title' => t('Exit Website URL'),
        '#default_value' => $exit_url,
        //'#description' => t('Enter multiple post code and cut-off time for aramex in json format. for eg. {"400 067":"17:40:00", "400 068":"12:30:00'),
        '#required' => FALSE,
        '#attributes' => ['placeholder' => t('Enter URL after clicking on exit website')],
      ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::state()->set('exit_url', $form_state->getValue('exit_url')); 
    parent::submitForm($form, $form_state);
  }

}