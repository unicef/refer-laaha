<?php

namespace Drupal\erpw_in_app_notification\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class NotificationsSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['erpw_in_app_notification.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'erpw_in_app_notification_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('erpw_in_app_notification.settings');

    $form['disable_notifications'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Disable Notifications'),
      '#default_value' => $config->get('disable_notifications'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('erpw_in_app_notification.settings');
    $config->set('disable_notifications', $form_state->getValue('disable_notifications'))->save();
    parent::submitForm($form, $form_state);
    $this->messenger()->addMessage($this->t('Notification settings saved.'));
  }
}
