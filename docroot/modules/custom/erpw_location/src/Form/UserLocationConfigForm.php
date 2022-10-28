<?php

namespace Drupal\erpw_location\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * User Location Configuration Form that configures forms module settings.
 */
class UserLocationConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_location_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'erpw_location.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('erpw_location.settings');
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $config->get('title'),
    ];

    $form['description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#default_value' => $config->get('description'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->configFactory->getEditable('erpw_location.settings')

      // Set the submitted configuration setting.
      ->set('title', $form_state->getValue('title'))
      ->set('description', $form_state->getValue('description'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
