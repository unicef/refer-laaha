<?php

namespace Drupal\erpw_custom\EventSubscriber;

use Drupal\Core\Database\Connection;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Class PreventTermDeletionEventSubscriber.
 *
 * Prevent term deletion if associated with any entities.
 *
 * @package Drupal\erpw_custom\EventSubscriber
 */
class PreventTermDeletionEventSubscriber implements EventSubscriberInterface {

  /**
   * Database Connection instance.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * PreventTermDeletionEventSubscriber constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Connection Object.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
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

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::FORM_ALTER => 'alterForm',
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

}