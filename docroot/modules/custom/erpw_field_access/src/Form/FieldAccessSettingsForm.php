<?php

namespace Drupal\erpw_field_access\Form;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
    // Get NodeType form configurations.
    $nodeTypeConfig = \Drupal::config('erpw_field_access.nodetype_settings')->get('nodeTypeConfig');
    $nodeConfig = array_key_exists($node_type->id(), $nodeTypeConfig) ? array_filter($nodeTypeConfig[$node_type->id()][$node_type->get('name')]) : NULL;
    /** @var \Drupal\domain\DomainStorage $domain_storage */
    $domain_storage = \Drupal::entityTypeManager()->getStorage('domain');
    $domains = $domain_storage->loadMultiple();
    $entityFieldManager = \Drupal::service('entity_field.manager');
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $fields */
    $fields = $entityFieldManager->getFieldDefinitions('node', $node_type->id());
    $form_state->set('node_type', $node_type);
    $roles = \Drupal::entityTypeManager()->getStorage('user_role')->loadMultiple();
    $visibleFields = array_diff_key($fields, $nodeConfig);
    if (empty($visibleFields)) {
      $url = Url::fromRoute('system.403');
      $response = new RedirectResponse($url->toString());
      $response->send();
      return;
    }
    else {
      foreach ($visibleFields as $field) {
        $form['fields'][$field->getName()] = [
          '#type' => 'details',
          '#title' => $field->getLabel(),
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
          $operations = ['form' => 'Form Access - Field visibility to the selected user(s) role will be forbidden on the create/edit pages.', 'view' => 'View Access - Field visibility to the selected user(s) role will be forbidden on the view/listing page(s).'];
          $options = [];
          foreach ($roles as $role) {
            $options[$role->id()] = $role->label();
          }
          foreach ($operations as $id => $label) {
            $form['fields'][$field->getName()]["{$field->getName()}_countries"][$domain->id()][$id] = [
              '#title' => $label,
              '#type' => 'checkboxes',
              '#options' => $options,
            ];
          }
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
    $settingsArray = \Drupal::service('erpw_field_access.settings_array')->minify($form_state->getValue('fields'));
    $node_type = $form_state->get('node_type');
    $node_type->setThirdPartySetting('erpw_field_access', 'field_access', $settingsArray);
    $node_type->save();
    $this->config('erpw_field_access.settings')
      ->set('example', $form_state->getValue('example'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
