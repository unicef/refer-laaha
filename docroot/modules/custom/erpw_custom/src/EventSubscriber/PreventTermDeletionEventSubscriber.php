<?php

namespace Drupal\erpw_custom\EventSubscriber;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Drupal\domain\DomainNegotiatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PreventTermDeletionEventSubscriber.
 *
 * Prevent term deletion if associated with any entities.
 *
 * @package Drupal\erpw_custom\EventSubscriber
 */
class PreventTermDeletionEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Database Connection instance.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Database Connection instance.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * A currentPathStack instance.
   *
   * @var Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPathStack;

  /**
   * Drupal\domain\DomainNegotiatorInterface definition.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * A entityManager instance.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * PreventTermDeletionEventSubscriber constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Connection Object.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   Drupal Configuration Object.
   * @param Drupal\Core\Path\CurrentPathStack $current_path_stack
   *   The Current Path service.
   * @param \Drupal\domain\DomainNegotiatorInterface $domain_negotiator
   *   DomainNegotiator object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityManager object.
   */
  public function __construct(Connection $connection,
    ConfigFactory $config_factory,
    CurrentPathStack $current_path_stack,
    DomainNegotiatorInterface $domain_negotiator,
    EntityTypeManagerInterface $entity_type_manager) {
    $this->connection = $connection;
    $this->configFactory = $config_factory;
    $this->currentPathStack = $current_path_stack;
    $this->domainNegotiator = $domain_negotiator;
    $this->entityManager = $entity_type_manager;
  }

  /**
   * Alter form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormAlterEvent $event
   *   The event.
   */
  public function alterForm(FormAlterEvent $event) {
    $form = &$event->getForm();
    $form_id = $event->getFormId();
    $form_state = $event->getFormState();
    // Checking for Taxonomy delete form.
    if ($form_id == 'taxonomy_term_country_delete_form') {
      $entity = $form_state->getFormObject()->getEntity();
      $preventDelete = FALSE;

      // Check to see if the term is already being used.
      if ($this->hasAssociatedWithContent($entity->id())) {
        $preventDelete = TRUE;
      }
      if ($this->hasAssociatedWithUser($entity->id())) {
        $preventDelete = TRUE;
      }
      if ($this->hasAssociatedLocationEntity($entity->id())) {
        $preventDelete = TRUE;
      }
      // Displaying message when prevent delete is true.
      if ($preventDelete == TRUE) {
        $form['#title'] = new TranslatableMarkup('This Term is being used by other entity, therefore cannot be deleted.');
        $form['description']['#markup'] = new TranslatableMarkup('Please first remove the associated entity to this term so it can be deleted.');
        unset($form['actions']['submit']);
      }
    }

    // Checking for location delete.
    if ($form_id == 'delete_location_forms') {
      $current_path = substr($this->currentPathStack->getPath(), 1);
      $path_args = explode('/', $current_path);
      $preventDelete = FALSE;
      // Check to see if the location is already being used.
      if (!empty($path_args[1])) {
        if ($this->hasAssociatedWithNode($path_args[1])) {
          $preventDelete = TRUE;
        }
        if ($this->hasAssociatedWithUser($path_args[1])) {
          $preventDelete = TRUE;
        }
        if ($this->hasAssociatedLocationWithDomain($path_args[1])) {
          $preventDelete = TRUE;
        }
      }
      // Displaying message when prevent delete is true.
      if ($preventDelete == TRUE) {
        $form['#title'] = new TranslatableMarkup('This location is being used by other entity, therefore cannot be deleted.');
        $form['description']['#markup'] = new TranslatableMarkup('Please first remove the associated entity to this location so it can be deleted.');
        unset($form['actions']['delete_location']);
        unset($form['msg_note']);
      }
    }

    if ($form_id == 'domain_edit_form') {
      $default_value = '';
      if ($id = $form['id']['#default_value']) {
        $config = $this->configFactory->getEditable('domain.location.' . $id);
        $default_value = ($config->get('location')) ? $this->entityManager->getStorage('taxonomy_term')->load($config->get('location')) : '';
      }
      $form['location'] = [
        '#type' => 'entity_autocomplete',
        '#target_type' => 'taxonomy_term',
        '#title' => $this->t('Location'),
        '#required' => TRUE,
        '#default_value' => $default_value,
        '#placeholder' => $this->t('Select Location'),
        '#attributes' => [],
        '#selection_settings' => [
          'target_bundles' => ['country'],
          'sort' => [
            'field' => 'name',
            'direction' => 'ASC',
          ],
          'match_operator' => 'STARTS_WITH',
          'match_limit' => 10,
        ],
      ];
      $form['actions']['submit']['#submit'][] = [$this, 'erpwDomainMapping'];
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      FormHookEvents::FORM_ALTER => 'alterForm',
    ];
  }

  /**
   * Check if this term is used on any nodes.
   *
   * @param int $term_id
   *   Term id.
   *
   * @return bool
   *   Return true/false.
   */
  private function hasAssociatedWithContent($term_id) {
    $query = $this->connection->select('taxonomy_index', 'ti');
    $query->fields('ti', ['nid']);
    $query->condition('ti.tid', $term_id);
    $query->distinct(TRUE);
    $result = $query->execute();

    if ($result->fetchCol()) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Check if this term has been used with user entity.
   *
   * @param int $term_id
   *   Term id.
   *
   * @return bool
   *   Return true/false.
   */
  private function hasAssociatedWithUser($term_id) {
    $query = $this->connection->select('users', 'u');
    $query->innerJoin('user__field_location', 'ufl', 'ufl.entity_id = u.uid');
    $query->fields('ufl', ['entity_id']);
    $query->condition('ufl.field_location_target_id', $term_id);
    $query->distinct(TRUE);
    $result = $query->execute();

    if ($result->fetchCol()) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Check if this term has been used with location entity.
   *
   * @param int $term_id
   *   Term id.
   *
   * @return bool
   *   Return true/false.
   */
  private function hasAssociatedLocationEntity($term_id) {
    $query = $this->connection->select('location', 'loc');
    $query->innerJoin('location__field_location_taxonomy_term', 'ltt', 'ltt.entity_id = loc.id');
    $query->fields('ltt', ['entity_id']);
    $query->condition('ltt.field_location_taxonomy_term_target_id', $term_id);
    $query->distinct(TRUE);
    $result = $query->execute();

    if ($result->fetchCol()) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Check if this location has been used with node entity.
   *
   * @param int $id
   *   Term id.
   *
   * @return bool
   *   Return true/false.
   */
  private function hasAssociatedWithNode($id) {
    $query = $this->connection->select('node', 'n');
    $query->innerJoin('node__field_location', 'nfl', 'nfl.entity_id = n.nid');
    $query->fields('nfl', ['entity_id']);
    $query->condition('nfl.field_location_target_id', $id);
    $query->distinct(TRUE);
    $result = $query->execute();

    if ($result->fetchCol()) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Check if this location has been used with domain entity.
   *
   * @param int $id
   *   Term id.
   *
   * @return bool
   *   Return true/false.
   */
  private function hasAssociatedLocationWithDomain($id) {
    $domain = $this->domainNegotiator->getActiveDomain();
    if ($domain->get('status')) {
      $configuration = $this->configFactory->getEditable('domain.location.' . $domain->id());
      $location = $configuration->get('location');
    }
    if (!empty($location) && $location == $id) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Callback to save location in domain.
   */
  public function erpwDomainMapping(&$form, $form_state) {
    $id = $form_state->getValue('id');
    $location = $form_state->getValue('location');
    $configuration = $this->configFactory->getEditable('domain.location.' . $id);
    $configuration->set('location', $location);
    $configuration->save();
  }

}
