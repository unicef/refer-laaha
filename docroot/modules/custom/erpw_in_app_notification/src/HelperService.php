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
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $cid = 'notification.events_list.' . $language;
    $eventlist = [];
    if ($cache = \Drupal::cache()->get($cid)) {
      $eventlist = $cache->data;
    }
    else {
      $config_pages = \Drupal::service('config_pages.loader');
      $events = $config_pages->getValue('notification_events', 'field_items');
      if (!empty($events)) {
        $paragraph_storage = $this->entityTypeManager->getStorage('paragraph');
        foreach ($events as $item) {
          $pobject = $paragraph_storage->load($item['target_id']);
          $eventlist[$pobject->get('field_machine_name')->getString()] = [
            'message' => $pobject->get('field_notification_message')->getString(),
            'icon_uri' => $pobject->get('field_notification_icon')->entity->getFileUri(),
          ];
        }
        \Drupal::cache()->set($cid, $eventlist);
      }
    }
    return $eventlist[$machine_name];
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
