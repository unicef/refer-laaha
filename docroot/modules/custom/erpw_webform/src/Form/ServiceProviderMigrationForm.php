<?php

namespace Drupal\erpw_webform\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Implements a configuration form for the migration.
 */
class ServiceProviderMigrationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'service_provider_migration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description'] = [
      '#markup' => $this->t('This form allows you to migrate service_provider content to webform_submissions.'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['migrate'] = [
      '#type' => 'submit',
      '#value' => $this->t('Migrate'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Add any validation logic here if needed.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Queue the batch process for service_provider migration.
    $queue = \Drupal::queue('migrate_service_providers');
    $queue->createItem();

    $form_state->setRedirectUrl(Url::fromRoute('<front>'));
  }

}
