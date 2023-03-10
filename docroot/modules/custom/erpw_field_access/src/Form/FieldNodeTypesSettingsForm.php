<?php

namespace Drupal\erpw_field_access\Form;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Erpw field access settings for different node types.
 */
class FieldNodeTypesSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'erpw_field_access.nodetype_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'erpw_field_access_node_type_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ConfigEntityInterface $node_type = NULL) {
    $config = $this->config('erpw_field_access.nodetype_settings');
    $configOptions = $config->get('nodeTypeConfig');
    $form['fields'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Content Types'),
      '#tree' => TRUE,
    ];
    $entityFieldManager = \Drupal::service('entity_field.manager');
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $fields */
    $nodeTypes = \Drupal::entityTypeManager()->getStorage('node_type')->loadMultiple();
    foreach ($nodeTypes as $nodeType => $node) {
      $form['fields'][$nodeType] = [
        '#type' => 'details',
        '#title' => $node->get('name'),
        '#group' => 'fields',
      ];
      if (isset($configOptions)) {
        $form['fields'][$nodeType]['exclude_all'] = [
          '#type' => 'checkbox',
          '#title' => 'Exclude all the fields',
          '#default_value' => $configOptions[$nodeType]['exclude_all'],
        ];
      }
      else {
        $form['fields'][$nodeType]['exclude_all'] = [
          '#type' => 'checkbox',
          '#title' => 'Exclude all the fields',
          '#default_value' => 0,
        ];
      }
      $fields = $entityFieldManager->getFieldDefinitions('node', $node->id());
      foreach ($fields as $field) {
        $options[$field->getFieldStorageDefinition()->getName()] = $field->getName();
      }
      if (isset($configOptions)) {
        $form['fields'][$nodeType][$node->get('name')] = [
          '#title' => 'Select fields not to be displayed in the Field Access form.',
          '#type' => 'checkboxes',
          '#options' => $options,
          '#default_value' => $configOptions[$nodeType][$node->get('name')],
        ];
      }
      else {
        $form['fields'][$nodeType][$node->get('name')] = [
          '#title' => 'Select fields not to be displayed in the Field Access form.',
          '#type' => 'checkboxes',
          '#options' => $options,
        ];
      }
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $configArray = [];
    foreach ($form_state->getValue('fields') as $nodeType => $values) {
      $nodeName = is_array($values) ? array_keys($values)[1] : NULL;
      foreach ($values[$nodeName] as $key => $value) {
        if (isset($values['exclude_all']) && $values['exclude_all'] == 1) {
          $configArray[$nodeType]['exclude_all'] = 1;
          $configArray[$nodeType][$nodeName][$key] = $key;
        }
        else {
          $configArray[$nodeType] = $form_state->getValue('fields')[$nodeType];
        }
      }
    }
    $configArray['fields__active_tab'] = $form_state->getValue('fields')['fields__active_tab'];
    $this->config('erpw_field_access.nodetype_settings')
      ->set('nodeTypeConfig', $configArray)
      ->save();
    parent::submitForm($form, $form_state);
  }

}
