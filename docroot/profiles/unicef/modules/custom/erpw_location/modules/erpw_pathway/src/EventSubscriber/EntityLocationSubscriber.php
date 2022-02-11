<?php

namespace Drupal\erpw_pathway\EventSubscriber;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
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
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
  protected $locationService;

  /**
   * Max level.
   *
   * @var maxLevel
   */
  protected $maxLevel = 4;

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
  public function __construct(EntityTypeManagerInterface $entityTypeManager,
    MessengerInterface $messenger,
    LocationService $location_service,
    RouteMatchInterface $route_match) {
    $this->entityTypeManager = $entityTypeManager;
    $this->messenger = $messenger;
    $this->locationService = $location_service;
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
      ])) {
      // Build location Form.
      $location_entities = $this->entityTypeManager->getStorage('location')->loadByProperties(
      ['type' => 'country', 'status' => 1]);
      $location_options = [];
      $form_state->setRebuild(TRUE);

      if (!empty($location_entities)) {
        $parent_list = [];
        $node = $this->routeMatch->getParameter('node');
        if (empty($form_state->getTriggeringElement()['#level']) && $node instanceof NodeInterface) {
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
          '#title' => $this->t('Select Country'),
          '#required' => TRUE,
          '#level' => 1,
          '#attributes' => ['class' => ['loc-dropdown'], 'data-level' => 1],
          '#default_value' => $parent_list[0] ?? '',
          '#ajax' => [
            'callback' => [$this, 'getLocationDetail'],
            'event' => 'change',
            'method' => 'replace',
            'wrapper' => 'location-level-1',
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
      $form['field_section']['widget']['add_more']['add_more_button_sections']['#value'] = $this->t('Add a new section');
      $form['#title'] = $this->t('Add new template for RPW');
      $form['actions']['preview']['#attributes']['class'][] = 'button-border';

      // Form submit handler.
      $form['actions']['submit']['#submit'][] = [$this, 'eprwSubmitHandler'];
    }

    if ($form_id == 'node_referral_path_way_edit_form') {
      $form['#title'] = $this->t('Update RPW template');
      $form['actions']['submit']['#cancel'][] = [$this, 'eprwCancelHandler'];
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
  public function getTermParents(NodeInterface $node) {
    $referenced_entities = $node->get('field_location')->referencedEntities();
    $parents = [];
    $loc_tid = [];
    foreach ($referenced_entities as $loc) {
      $loc_tid[] = $loc->id();
      $parents = $this->locationService->getAllAncestors($loc->id());
    }
    $location_entities = $this->entityTypeManager->getStorage('location')->loadByProperties([
      'type' => 'country',
      'status' => 1,
      'field_location_taxonomy_term' => $parents[0],
    ]);

    for ($i = $this->maxLevel; $i >= 0; $i--) {
      $level_id = 'level_' . $i;
      if (isset($location_entities[$parents[0]]->get($level_id)->getValue()[0])) {
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
      $childs = ['0' => $this->t("Select") . ' ' . $i . ' ' . $this->t("Label")];
      $class = '';
      if (isset($location->get($level_id)->getValue()[0])) {
        $this->levelLabel[$id][$i] = $this->levelLabel[$id][$i] ?? $location->get($level_id)->getValue()[0]['value'];
        $location_tid = !empty($parent_list[$i - 1]) ? $parent_list[$i - 1] : $form_state->getValue('level_' . ($i - 1));

        $childs += $this->locationService->getChildrenByTid($location_tid);
        if (count($childs) <= 1) {
          $class = 'hidden';
        }
      }
      $child_location['level_' . $i] = [
        '#prefix' => '<div id="location-level-' . $i . '" class="' . $class . '">',
        '#suffix' => '</div>',
        '#options' => $childs,
        '#type' => 'select',
        '#title' => $this->levelLabel[$id][$i] ?? '',
        '#attributes' => ['class' => ['loc-dropdown'], 'data-level' => ($i + 1)],
        '#multiple' => ($i == $this->maxLevel) ? TRUE : FALSE,
        '#level' => ($i + 1),
        '#default_value' => $parent_list[$i] ?? '',
        '#ajax' => [
          'callback' => [$this, 'getLocationDetail'],
          'event' => 'change',
          'method' => 'replace',
          'wrapper' => 'location-level-' . ($i + 1),
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
    for ($i = $this->maxLevel; $i >= 0; $i--) {
      $location_level = $form_state->getValue('level_' . $i);
      if (!empty($location_level)) {
        break;
      }
    }
    // Saving the location data.
    $node = $this->routeMatch->getParameter('node');
    if ($node instanceof NodeInterface) {
      $node->set('field_location', []);
      $this->saveLocationField($node, $location_level);
    }
    else {
      $form_object = $form_state->getFormObject();
      if ($form_object instanceof EntityForm) {
        $entity = $form_object->getEntity();
        $this->saveLocationField($entity, $location_level);
      }
    }

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
    if (is_array($location)) {
      foreach ($location as $value) {
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
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::FORM_ALTER => 'alterForm',
    ];
  }

}
