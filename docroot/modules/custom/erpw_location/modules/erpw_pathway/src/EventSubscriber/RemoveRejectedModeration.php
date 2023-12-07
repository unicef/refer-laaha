<?php

namespace Drupal\erpw_pathway\EventSubscriber;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

// @todo Check the impact of removing this file since Services are migrated to Webforms.

/**
 * Class RemoveRejectedModeration.
 *
 * Remove the rejected moderated content.
 */
class RemoveRejectedModeration implements EventSubscriberInterface {

  /**
   * Database instance.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * A entityTypeManager instance.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $request;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * LocationCookie constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match service.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database service.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityManager object.
   */
  public function __construct(RequestStack $request_stack,
    RouteMatchInterface $route_match,
    Connection $connection,
    EntityTypeManagerInterface $entity_type_manager) {
    $this->request = $request_stack->getCurrentRequest();
    $this->routeMatch = $route_match;
    $this->connection = $connection;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::RESPONSE => 'onResponse',
    ];
  }

  /**
   * Response Callback.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event to process.
   */
  public function onResponse(ResponseEvent $event) {
    $route_name = $this->routeMatch->getRouteName();
    if ($route_name == 'view.manage_service.page_1' || $route_name == 'view.service_list.page_1') {
      $this->removeModeratedContent();
    }
  }

  /**
   * Callback to remove rejected moderated content.
   */
  public function removeModeratedContent() {
    // Get the rejected content nids.
    $query = $this->connection->select('content_moderation_state_field_revision', 'cm');
    $query->leftjoin('node_revision', 'n', 'n.vid = cm.content_entity_revision_id');
    $query->fields('cm', ['content_entity_id']);
    $query->condition('n.revision_default', '0');
    $query->condition('moderation_state', 'reject', '=');
    $query->condition('workflow', 'service_editorial');
    $query = $query->execute();
    $nids = $query->fetchCol();

    // Remove the rejected version of content.
    if (!empty($nids)) {
      $query = $this->connection->select('content_moderation_state_field_revision', 'cm');
      $query->leftjoin('node_revision', 'n', 'n.vid = cm.content_entity_revision_id');
      $query->fields('cm', ['content_entity_revision_id']);
      $query->condition('cm.content_entity_id', $nids, 'IN');
      $query->condition('n.revision_default', '0');
      $query->condition('cm.moderation_state', ['reject', 'draft'], 'IN');
      $query->condition('cm.workflow', 'service_editorial');
      $query = $query->execute();
      $revision_id = $query->fetchCol();

      // @todo Cache the query executed result.
      if (empty($revision_id)) {
        return;
      }
      foreach ($revision_id as $vid) {
        $this->entityTypeManager->getStorage('node')->deleteRevision($vid);
      }
    }

  }

}
