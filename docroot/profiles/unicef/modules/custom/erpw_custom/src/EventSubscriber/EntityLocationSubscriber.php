<?php

namespace Drupal\erpw_custom\EventSubscriber;

use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\Event\Form\FormBaseAlterEvent;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\erpw_location\LocationService;

/**
 * Class EntityLocationSubscriber.
 *
 * To alter the node forms.
 */
class EntityLocationSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * A LocationService instance.
   *
   * @var Drupal\erpw_location\LocationService
   */
  protected $locationService;

  /**
   * Array of levels.
   *
   * @var levelLabel
   */
  protected $levelLabel = [];

  /**
   * Max level.
   *
   * @var maxLevel
   */
  protected $maxLevel = 4;

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $database,
    EntityTypeManagerInterface $entityTypeManager,
    MessengerInterface $messenger,
    LocationService $location_service) {
    $this->database = $database;
    $this->entityTypeManager = $entityTypeManager;
    $this->messenger = $messenger;
    $this->locationService = $location_service;
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
      ])) {
      // Build location Form.
      $location_entities = $this->entityTypeManager->getStorage('location')->loadByProperties(
      ['type' => 'country', 'status' => 1]);
      $location_options = [];
      $form_state->setRebuild(TRUE);

      $node = \Drupal::routeMatch()->getParameter('node');
      if (!empty($location_entities)) {
        $parent_list = [];

        if (!isset($form_state->getTriggeringElement()['#level'])
        && $node instanceof NodeInterface) {
          $parent_list = $this->getTermParents($node);
        }

        foreach ($location_entities as $location) {
          $id = isset($location->get('field_location_taxonomy_term')->getValue()[0]) ?
          $location->get('field_location_taxonomy_term')->getValue()[0]['target_id'] : '';
          $location_options[$id] = $location->get('name')->getValue()[0]['value'];
          $child_location = $this->getChildLocation($location, $form, $form_state, $parent_list);
        }
        if ($country_id = $form_state->getValue('level_0')) {
          $level_count = count($this->levelLabel[$country_id]);
          unset($child_location['level_' . $level_count]['#ajax']);

          $child_location['level_' . $level_count]['#attributes']['class'][] = 'add_multiple';
        }
        $form['location']['level_0'] = [
          '#type' => 'select',
          '#options' => $location_options,
          '#empty_option' => $this->t('Select Country'),
          '#title' => $this->t('Country'),
          '#required' => TRUE,
          '#level' => 1,
          '#attributes' => ['class' => ['loc-dropdown']],
          '#default_value' => isset($parent_list[0]) ? $parent_list[0] : '',
          '#ajax' => [
            'callback' => [$this, 'getLocationDetail'],
            'event' => 'change',
            'method' => 'replace',
            'wrapper' => 'edit-level-1',
            'progress' => [
              'type' => 'throbber',
            ],
          ],
          '#attached' => [
            'drupalSettings' => [
              'leveldetails' => [
                'level' => 1,
                'levelcount' => $this->maxLevel,
              ],
            ],
          ],
        ];
        $form['location'] += $child_location;
      }

      $form['#cache']['max-age'] = 0;
      $form['#attached']['library'][] = 'erpw_location/erpw_location_js';

      // Change button name of section.
      $form['field_section']['widget']['add_more']['add_more_button_sections']['#value'] = $this->t('Add New Section');

      // Form submit handler.
      $form['actions']['submit']['#submit'][] = [$this, 'eprwSubmitHandler'];
    }

    if ($form_id == 'node_referral_path_way_edit_form') {
      $form['actions']['submit']['#cancel'][] = [$this, 'eprwCancelHandler'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getTermParents($node) {
    $referenced_entities = $node->get('field_location')->referencedEntities();
    $parents = [];
    foreach ($referenced_entities as $loc) {
      $loc_tid[] = $loc->id();
      $parents = $this->locationService->getAllAncestors($loc->id());
    }

    $location_entities = $this->entityTypeManager->getStorage('location')->loadByProperties([
      'type' => 'country',
      'status' => 1,
      'field_location_taxonomy_term' => $parents[0],
    ]);

    for ($i = $this->maxLevel; $i > 0; $i--) {
      $level_id = 'level_' . $i;
      if (isset($location_entities[$parents[0]]->get($level_id)->getValue()[0])) {
        if (count($parents) == $i) {
          $index = count($parents) - 1;
          $parents[$index] = $loc_tid;
          break;
        }
      }
    }

    return $parents;
  }

  /**
   * {@inheritdoc}
   */
  public function getDetails($form, $form_state, $level) {
    $country_id = $form_state->getValue('level_0');
    $form['location']['level_' . $level]['#title'] = $this->levelLabel[$country_id][$level];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getLocationDetail(&$form, $form_state) {
    $level = $form_state->getTriggeringElement()['#level'];
    $form = $this->getDetails($form, $form_state, $level);

    return $form['location']['level_' . $level];
  }

  /**
   * Get child location array.
   *
   * @param object $location
   *   Location Array.
   * @param array $form
   *   Array of form element.
   * @param mixed $form_state
   *   Form state.
   * @param array $parent_list
   *   List of parents.
   *
   * @return array
   *   Return Child location.
   */
  protected function getChildLocation($location, array $form, $form_state, array $parent_list = []) {
    if (isset($parent_list[0])) {
      $id = $parent_list[0];
    }
    else {
      $id = isset($location->get('field_location_taxonomy_term')->getValue()[0]) ?
      $location->get('field_location_taxonomy_term')->getValue()[0]['target_id'] : '';
    }

    for ($i = 1; $i <= $this->maxLevel; $i++) {
      $level_id = 'level_' . $i;
      $childs = [];
      $childs[] = $this->t("Select") . ' ' . $i . ' ' . $this->t("Label");
      $class = '';
      if (isset($location->get($level_id)->getValue()[0])) {
        $this->levelLabel[$id][$i] = $location->get($level_id)->getValue()[0]['value'];
        $location_tid = !empty($parent_list[$i - 1]) ? $parent_list[$i - 1] : $form_state->getValue('level_' . ($i - 1));

        $childs += $this->locationService->getChildrenByTid($location_tid);
        if (count($childs) <= 1) {
          $class = 'hidden';
        }
      }
      $child_location['level_' . $i] = [
        '#prefix' => '<div id="edit-level-' . $i . '" class="' . $class . '">',
        '#suffix' => '</div>',
        '#options' => $childs,
        '#type' => 'select',
        '#title' => $this->levelLabel[$id][$i],
        '#attributes' => ['class' => ['loc-dropdown']],
        '#multiple' => ($i == $this->maxLevel) ? TRUE : FALSE,
        '#level' => ($i + 1),
        '#default_value' => isset($parent_list[$i]) ? $parent_list[$i] : '',
        '#ajax' => [
          'callback' => [$this, 'getLocationDetail'],
          'event' => 'change',
          'method' => 'replace',
          'wrapper' => 'edit-level-' . ($i + 1),
          'progress' => [
            'type' => 'throbber',
          ],
        ],
        '#attached' => [
          'drupalSettings' => [
            'leveldetails' => [
              'level' => ($i + 1),
              'levelcount' => $this->maxLevel,
            ],
          ],
        ],
      ];
    }

    return $child_location;
  }

  /**
   * {@inheritdoc}
   */
  public function eprwSubmitHandler(&$form, $form_state) {
    $node = \Drupal::routeMatch()->getParameter('node');
    $locaton_level = '';
    for ($i = $this->maxLevel; $i > 0; $i--) {
      $locaton_level = $form_state->getValues()['level_' . $i];
      if (!empty($locaton_level)) {
        break;
      }
    }

    // Saving the location data.
    if ($node instanceof NodeInterface) {
      $this->saveLocationField($node, $locaton_level);
    }
    else {
      $form_object = $form_state->getFormObject();
      if ($form_object instanceof EntityForm) {
        $entity = $form_object->getEntity();
        $this->saveLocationField($entity, $locaton_level);
      }
    }

  }

  /**
   * Save erpw location reference field.
   */
  public function saveLocationField($entity, $location) {
    if (is_array($location)) {
      foreach ($location as $key => $value) {
        $entity->field_location[] = ['target_id' => $value];
      }
    }
    else {
      $entity->field_location->target_id = $location;
    }
    $entity->save();
  }

  /**
   * {@inheritdoc}
   */
  public function eprwCancelHandler(&$form, $form_state) {
    // @todo Replace route name when RPW view list is done.
    return _erpw_custom_redirect('view.manage_service_types.page_1');
  }

  /**
   * Alter node form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormBaseAlterEvent $event
   *   The event.
   */
  public function alterNodeForm(FormBaseAlterEvent $event): void {
    $form = &$event->getForm();
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::FORM_ALTER => 'alterForm',
      // React on all forms with base id "node_form".
      'hook_event_dispatcher.form_base_node_form.alter' => 'alterNodeForm',
    ];
  }

}
