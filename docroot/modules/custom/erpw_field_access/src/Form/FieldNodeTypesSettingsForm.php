<?php

namespace Drupal\erpw_field_access\Form;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;

/**
 * Configure Erpw field access settings for different node types.
 */
class FieldNodeTypesSettingsForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, EntityFieldManagerInterface $entityFieldManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager')
    );
  }

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
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $fields */
    $nodeTypes = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
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
      $fields = $this->entityFieldManager->getFieldDefinitions('node', $node->id());
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
