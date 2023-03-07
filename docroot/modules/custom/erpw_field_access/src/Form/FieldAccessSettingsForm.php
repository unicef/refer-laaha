<?php

namespace Drupal\erpw_field_access\Form;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Erpw field access settings for this site.
 */
class FieldAccessSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'erpw_field_access_field_access_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['erpw_field_access.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ConfigEntityInterface $node_type = NULL) {
    $form['fields'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Fields'),
      '#tree' => TRUE,
    ];

    /** @var \Drupal\domain\DomainStorage $domain_storage */
    $domain_storage = \Drupal::entityTypeManager()->getStorage('domain');
    $domains = $domain_storage->loadMultiple();
    $entityFieldManager = \Drupal::service('entity_field.manager');
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $fields */
    $fields = $entityFieldManager->getFieldDefinitions('node', $node_type->id());
    $roles = \Drupal::entityTypeManager()->getStorage('user_role')->loadMultiple();
    foreach ($fields as $field) {
      $form['fields'][$field->getName()] = [
        '#type' => 'details',
        '#title' => $field->getName(),
        '#description' => $field->getLabel(),
        '#group' => 'fields',
      ];
      $form['fields'][$field->getName()]["{$field->getName()}_countries"] = [
        '#type' => 'horizontal_tabs',
        '#title' => $field->getLabel(),
      ];
      foreach ($domains as $domain) {
        $form['fields'][$field->getName()]["{$field->getName()}_countries"][$domain->id()] = [
          '#type' => 'details',
          '#title' => $domain->label(),
          '#group' => "{$field->getName()}_countries",
        ];
        $operations = ['form' => 'Form Access', 'view' => 'View Access'];
        $options = [];
        foreach ($roles as $role) {
          $options[$role->id()] = $role->label();
        }
        foreach ($operations as $id => $label) {
          $form['fields'][$field->getName()]["{$field->getName()}_countries"][$domain->id()][$id] = [
            '#title' => $label,
            '#type' => 'checkboxes',
            '#options' => $options
          ];
        }
      }

    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Loop through each field and store the settings in it's third party settings.
    dump($form_state->getValue('fields'));
    $this->config('erpw_field_access.settings')
      ->set('example', $form_state->getValue('example'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
