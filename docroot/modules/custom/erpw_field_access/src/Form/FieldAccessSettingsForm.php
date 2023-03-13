<?php

namespace Drupal\erpw_field_access\Form;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Configure Erpw field access settings for this site.
 */
class FieldAccessSettingsForm extends ConfigFormBase {

  /**
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

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
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $configFactory, EntityTypeManagerInterface $entityTypeManager, EntityFieldManagerInterface $entityFieldManager, AccountInterface $currentUser) {
    $this->configFactory = $configFactory;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entityFieldManager;
    $this->currentUser = $currentUser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('current_user')
    );
  }

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
    $nodeTypeConfig = $this->configFactory->getEditable('erpw_field_access.nodetype_settings')->get('nodeTypeConfig');
    $nodeConfig = array_key_exists($node_type->id(), $nodeTypeConfig) ? array_filter($nodeTypeConfig[$node_type->id()][$node_type->get('name')]) : NULL;
    // Get FieldAccess third party settings for default value.
    $settings = $node_type->getThirdPartySettings('erpw_field_access');
    /** @var \Drupal\domain\DomainStorage $domain_storage */
    $domain_storage = $this->entityTypeManager->getStorage('domain');
    $domains = $domain_storage->loadMultiple();
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $fields */
    $fields = $this->entityFieldManager->getFieldDefinitions('node', $node_type->id());
    $form_state->set('node_type', $node_type);
    $roles = $this->entityTypeManager->getStorage('user_role')->loadMultiple();
    $visibleFields = array_diff_key($fields, $nodeConfig);
    $user = $this->entityTypeManager->getStorage('user')->load(($this->currentUser->id()));
    $userDomains = [];
    foreach ($user->toArray()['field_domain_admin'] as $key => $domain) {
      $userDomains[$domain['target_id']] = $domain['target_id'];
    }
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
          if (array_key_exists($domain->id(), $userDomains)) {
            $form['fields'][$field->getName()]["{$field->getName()}_countries"][$domain->id()] = [
              '#type' => 'details',
              '#title' => $domain->label(),
              '#group' => "{$field->getName()}_countries",
            ];
          }
          else {
            $form['fields'][$field->getName()]["{$field->getName()}_countries"][$domain->id()] = [
              '#type' => 'details',
              '#title' => $domain->label(),
              '#group' => "{$field->getName()}_countries",
              '#disabled' => TRUE,
              '#prefix' => '<div class="hide-group-field">',
              '#suffix' => '</div>',
              '#attributes' => ['class' => ['hide-group-field']],
            ];
          }
          $operations = [
            'form' => 'Form Access - Field visibility to the selected user(s) role will be forbidden on the create/edit page(s).',
            'view' => 'View Access - Field visibility to the selected user(s) role will be forbidden on the view/listing page(s).',
          ];
          $options = [];
          foreach ($roles as $role) {
            $options[$role->id()] = $role->label();
          }
          foreach ($operations as $id => $label) {
            $form['fields'][$field->getName()]["{$field->getName()}_countries"][$domain->id()][$id] = [
              '#title' => $label,
              '#type' => 'checkboxes',
              '#options' => $options,
              '#default_value' => $settings['field_access'][$field->getName()]["{$field->getName()}_countries"][$domain->id()][$id] ?? $options,
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
    $node_type = $form_state->get('node_type');
    $node_type->setThirdPartySetting('erpw_field_access', 'field_access', $form_state->getValue('fields'));
    $node_type->save();
    parent::submitForm($form, $form_state);
  }

}
