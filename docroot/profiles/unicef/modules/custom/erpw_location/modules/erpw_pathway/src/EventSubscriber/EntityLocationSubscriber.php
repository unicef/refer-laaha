<?php

namespace Drupal\erpw_pathway\EventSubscriber;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\erpw_location\LocationService;
use Drupal\erpw_custom\Services\ErpwCustomService;
use Drupal\erpw_pathway\Services\ErpwPathwayService;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Class EntityLocationSubscriber.
 *
 * To alter the node forms.
 */
class EntityLocationSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  const MAX_LEVEL = 4;

  /**
   * The custom service.
   *
   * @var \Drupal\erpw_custom\Services\ErpwCustomService
   */
  protected $erpwCustomService;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected static $entityTypeManager;

  /**
   * Array of levels.
   *
   * @var levelLabel
   */
  protected $levelLabel = [];

  /**
   * A Location Service instance.
   *
   * @var Drupal\erpw_location\LocationService
   */
  protected $locationEntity;

  /**
   * A LocationService instance.
   *
   * @var Drupal\erpw_location\LocationService
   */
  protected static $locationService;

  /**
   * ERP PATHWAY service.
   *
   * @var \Drupal\erpw_pathway\Services\ErpwPathwayService
   */
  protected $erpwPathwayService;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The Current user service.
   *
   * @var Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
    LocationService $location_service,
    RouteMatchInterface $route_match,
    ErpwPathwayService $erpw_pathway_service,
    AccountProxyInterface $current_user,
    ErpwCustomService $erpw_custom_service) {
    self::$entityTypeManager = $entity_type_manager;
    self::$locationService = $location_service;
    $this->erpwPathwayService = $erpw_pathway_service;
    $this->routeMatch = $route_match;
    $this->currentUser = $current_user;
    $this->locationEntity = $location_service;
    $this->erpwCustomService = $erpw_custom_service;
  }

  /**
   * Alter form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormAlterEvent $event
   *   The event.
   */
  public function alterForm(FormAlterEvent $event): void {
    $form = &$event->getForm();
    $form_id = $event->getFormId();
    $form_state = $event->getFormState();

    if (in_array($form_id,
      [
        'node_referral_path_way_form',
        'node_referral_path_way_edit_form',
        'node_service_provider_form',
        'node_service_provider_edit_form',
      ])) {
      $parent_list = [];
      $node = $this->routeMatch->getParameter('node');
      if (empty($form_state->getTriggeringElement()['#level']) && $node instanceof NodeInterface) {
        $parent_list = $this->getTermParents($node);
      }
      $current_user = self::$entityTypeManager->getStorage('user')->load($this->currentUser->id());
      $ptids = [];
      switch ($form_id) {
        case 'node_referral_path_way_form':
          $permission = 'add referral pathway of their own location';
          break;

        case 'node_referral_path_way_edit_form':
          $permission = 'edit referral pathway of their own location';
          break;

        case 'node_service_provider_form':
          $permission = 'add service of their own location';
          break;

        case 'node_service_provider_edit_form':
          $permission = 'edit service of their own location';
          break;

        default:
          $permission = '';
          break;
      }
      if ($this->currentUser->id() != 1 && !$current_user->hasRole('administrator') && $current_user->hasPermission($permission)) {
        $location_id = '';
        if ($current_user->hasField('field_location') && !$current_user->get('field_location')->isEmpty()) {
          $location_id = $current_user->get('field_location')->getValue()[0]['target_id'];
        }
        $ptids = self::$locationService->getAllAncestors($location_id);
        $parent_list = empty($parent_list) ? array_values($ptids) : $parent_list;
      }
      $form = $this->erpwPathwayService->getLocationForm($form, $form_state, $parent_list, $ptids);
      // Form submit handler.
      $form['actions']['submit']['#submit'][] = [$this, 'eprwSubmitHandler'];
    }
    // Manage service validation.
    if (in_array($form_id,
    [
      'node_service_provider_form',
      'node_service_provider_edit_form',
    ])) {
      $form['#validate'][] = [$this, 'erpwCustomServiceProviderValidation'];
    }
    // RPW basic elements alter.
    if (in_array($form_id,
    [
      'node_referral_path_way_form',
      'node_referral_path_way_edit_form',
    ])) {
      // Change button name of the section.
      $form['field_section']['widget']['add_more']['add_more_button_sections']['#value'] = $this->t('Add a new section');
      $form['field_section']['widget']['#title'] = '';
    }
    // Only alter for RPW Add Form.
    if ($form_id == 'node_referral_path_way_form') {
      $form['#title'] = $this->t('Add New Referral Pathway');
      $form['actions']['submit']['#value'] = $this->t('Publish');
      $form['#validate'][] = [$this, 'eprwValidationHandler'];
    }
    // Only alter for RPW Edit Form.
    if ($form_id == 'node_referral_path_way_edit_form') {
      $form['#title'] = $this->t('Update Referral Pathway');
      $form['actions']['submit']['#value'] = $this->t('UPDATE');
      $form['#attached']['library'][] = 'erpw_pathway/erpw_pathway_autocomplete';
    }
  }

  /**
   * Validation for duplicate location hierarchy.
   */
  public function eprwValidationHandler(array &$form, $form_state) {
    $message = $this->t('The selected location is already associated with a RPW.');
    $this->validationHandler($form, $form_state, $message);
  }

  /**
   * Callback for unique location hierarchy validation.
   */
  protected function validationHandler($form, $form_state, $message = NULL) {
    for ($i = self::MAX_LEVEL; $i >= 0; $i--) {
      $location_level = $form_state->getValue('level_' . $i);
      if (!empty($location_level)) {
        break;
      }
    }
    $current_lang = $this->erpwCustomService->getCurrentLanguage();
    $saved_loc_id = $this->locationEntity->getSavedLocation($location_level, $current_lang);
    if (!empty($saved_loc_id)) {
      $form_state->setError($form['location'], $message);
    }
  }

  /**
   * Validation for allowing only integer and '+' in phone number fields.
   */
  public function erpwCustomServiceProviderValidation(&$form, $form_state) {
    // Checking unique location hierarchy validation.
    $message = $this->t('The selected location is already associated with a service.');
    $this->validationHandler($form, $form_state, $message);

    $message = $this->t('Only numberic values are allowed');
    $fields = [
      'field_phone_number',
      'field_phone_number_backup_focalp',
      'field_phone_number_of_focal_poin',
    ];
    foreach ($fields as $field) {
      $field_value = $form_state->getValue($field)[0]['value'];
      if (!empty($field_value) && !preg_match('/^[+-]?\d+$/', $field_value)) {
        $form_state->setErrorByName($field, $message);
        $form_state->setRebuild();
      }
    }
  }

  /**
   * Get the list of parents of the child.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node instance.
   *
   * @return array
   *   Return the list of parents.
   */
  public static function getTermParents(NodeInterface $node) {
    $referenced_entities = $node->get('field_location')->referencedEntities();
    $parents = [];
    $loc_tid = [];
    foreach ($referenced_entities as $loc) {
      $loc_tid[] = $loc->id();
      $parents = self::$locationService->getAllAncestors($loc->id());
    }
    $location_entities = self::$entityTypeManager->getStorage('location')->loadByProperties([
      'type' => 'country',
      'status' => 1,
      'field_location_taxonomy_term' => $parents[0],
    ]);
    $location_entities = array_values($location_entities);
    for ($i = self::MAX_LEVEL; $i >= 0; $i--) {
      $level_id = 'level_' . $i;
      if (isset($location_entities[0]->get($level_id)->getValue()[0])) {
        break;
      }
    }
    if (count($parents) == ($i + 1)) {
      $index = count($parents) - 1;
      $parents[$index] = $loc_tid;
    }

    return $parents;
  }

  /**
   * {@inheritdoc}
   */
  public function eprwSubmitHandler(&$form, $form_state) {
    for ($i = self::MAX_LEVEL; $i >= 0; $i--) {
      $location_level = $form_state->getValue('level_' . $i);
      if (!empty($location_level)) {
        break;
      }
    }
    // Saving the location data.
    $form_object = $form_state->getFormObject();
    if ($form_object instanceof EntityForm) {
      $entity = $form_object->getEntity();
      $this->saveLocationField($entity, $location_level);
    }

    return _erpw_custom_redirect('view.referral_pathway_listing.page_1', 'rpw_listing');
  }

  /**
   * Save eRPW location field.
   *
   * @param object $entity
   *   Entity instance.
   * @param array|string $location
   *   Location data.
   */
  protected function saveLocationField($entity, $location) {
    $node = $this->routeMatch->getParameter('node');
    // Removing the previous location to avoid duplicates.
    if ($node instanceof NodeInterface) {
      $entity->set('field_location', []);
    }
    if (is_array($location)) {
      foreach ($location as $value) {
        $entity->field_location[] = ['target_id' => $value];
      }
    }
    else {
      $entity->field_location[] = ['target_id' => $location];
    }
    $entity->save();
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::FORM_ALTER => 'alterForm',
    ];
  }

}
