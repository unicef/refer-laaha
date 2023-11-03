<?php

namespace Drupal\erpw_in_app_notification;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class HelperService.
 */
class HelperService implements HelperServiceInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new HelperService object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getEventDetailsByEventId($machine_name) {
    $config_pages = \Drupal::service('config_pages.loader');
    $notification_events = $config_pages->getValue('notification_events', 'field_items');
    // @todo
  }

  /**
   * {@inheritdoc}
   */
  public function getNotificationIds(): array {
    // Fetch new notifications.
    $entity_storage = $this->entityTypeManager->getStorage('notification_entity');
    $ids = $entity_storage->getQuery()
      ->condition('field_processed', FALSE)
      ->execute();
    return $ids ?? [];
  }

}
