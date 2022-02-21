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
use Drupal\erpw_pathway\Services\ErpwPathwayService;

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
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
    LocationService $location_service,
    RouteMatchInterface $route_match,
    ErpwPathwayService $erpw_pathway_service) {
    self::$entityTypeManager = $entity_type_manager;
    self::$locationService = $location_service;
    $this->erpwPathwayService = $erpw_pathway_service;
    $this->routeMatch = $route_match;
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
      $form = $this->erpwPathwayService->getLocationForm($form, $form_state, $parent_list);
      // Change button name of section.
      $form['field_section']['widget']['add_more']['add_more_button_sections']['#value'] = $this->t('Add a new section');
      $form['#title'] = $this->t('Add New Referral Pathway');
      $form['actions']['preview']['#attributes']['class'][] = 'button-border';
      $form['field_section']['widget']['#title'] = '';
      if (in_array($form_id,
      [
        'node_service_provider_form',
        'node_service_provider_edit_form',
      ])) {
        $form['#validate'][] = 'erpwCustomServiceProviderValidation';
      }
      // Form submit handler.
      $form['actions']['submit']['#submit'][] = [$this, 'eprwSubmitHandler'];
    }

    if ($form_id == 'node_referral_path_way_edit_form') {
      $form['#title'] = $this->t('Update RPW');
      $form['actions']['submit']['#value'] = $this->t('UPDATE');
      $form['actions']['delete']['#access'] = FALSE;
      $form['actions']['preview']['#access'] = FALSE;
      $form['actions']['delete_translation']['#access'] = FALSE;
      $form['actions']['cancel'] = [
        '#type' => 'submit',
        '#submit' => ['eprwCancelHandler'],
        '#limit_validation_errors' => [],
        '#attributes' => [
          'class' => [
            'button-border',
          ],
        ],
        '#value' => $this->t('CANCEL'),
      ];
    }
  }

  /**
   * Validation for allowing only integer and '+' in phone number fields.
   */
  public function erpwCustomServiceProviderValidation(array $form, FormStateInterface $form_state) {
    $message = $this->t('Only numberic values are allowed');
    $field_phone_number = $form_state->getValue('field_phone_number')[0]['value'];
    if (!preg_match('/^[+-]?\d+$/', $field_phone_number)) {
      $form_state->setError($form['field_phone_number'], $message);
    }
    $field_phone_number_backup_focalp = $form_state->getValue('field_phone_number_backup_focalp')[0]['value'];
    if (!preg_match('/^[+-]?\d+$/', $field_phone_number_backup_focalp)) {
      $form_state->setError($form['field_phone_number_backup_focalp'], $message);
    }
    $field_phone_number_of_focal_poin = $form_state->getValue('field_phone_number_of_focal_poin')[0]['value'];
    if (!preg_match('/^[+-]?\d+$/', $field_phone_number_of_focal_poin)) {
      $form_state->setError($form['field_phone_number_of_focal_poin'], $message);
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

    return _erpw_custom_redirect('view.referral_pathway_listing.page_1');
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
  public function eprwCancelHandler(&$form, $form_state) {
    return _erpw_custom_redirect('view.referral_pathway_listing.page_1', 'rpw_listing');
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
