<?php

namespace Drupal\erpw_in_app_notification;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class HelperService.
 */
class HelperService implements HelperServiceInterface {

  use StringTranslationTrait;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Datetime\DateFormatterInterface definition.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a new HelperService object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, DateFormatterInterface $date_formatter) {
    $this->entityTypeManager = $entity_type_manager;
    $this->dateFormatter = $date_formatter;
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
      ->accessCheck(FALSE)
      ->condition('field_processed', FALSE)
      ->execute();
    return $ids ?? [];
  }

  /**
   * Returns the broadcast entity ids.
   */
  public function getBroadcastNotificationIds() : array {
    // Fetch new notifications.
    $entity_storage = $this->entityTypeManager->getStorage('broadcast_notification_entity');
    $ids = $entity_storage->getQuery()
      ->accessCheck(FALSE)
      ->execute();
    return $ids ?? [];
  }

  /**
   * Returns the message of the broadcast notification.
   */
  public function getBroadcastNotificationMessage($notification) : array {
    $messageType = $notification->get('field_message_type')->getString();
    $message = [];
    if ($messageType == 'custom') {
      $customID = explode(', ', $notification->get('field_customised_message')->getString())[0];
      $paragraph = $this->entityTypeManager->getStorage('paragraph')->load($customID);
      $message['message'] = $paragraph->get('field_message')->getString();
      $message['color'] = $paragraph->get('field_color')->getString();
      $message['icon'] = $paragraph->get('field_icon')->getString();
    }
    if ($messageType == 'predefined') {
      $predefinedID = $notification->get('field_predefined_messages')->getString();
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($predefinedID);
      $message['message'] = $term->get('name')->getString();
      $message['color'] = $term->get('field_color')->getString();
      $message['icon'] = $term->get('field_icon')->getString();
    }
    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicDateFormate($timestamp, $formate = 'd F Y'): string {

    // Check the timestamp not morethan 24 hours.
    $currentTimestamp = \Drupal::time()->getRequestTime();

    // Calculate the difference in seconds.
    $timeDifference = $currentTimestamp - $timestamp;

    // Check if the timestamp is not more than 24 hours old (24 hours * 60 minutes * 60 seconds).
    if ($timeDifference <= 24 * 60 * 60) {
      $date = $this->dateFormatter->formatInterval($currentTimestamp - $timestamp);
      return $date . ' ' . $this->t('ago');
    }
    else {
      $timezone = date_default_timezone_get();
      $date = $this->dateFormatter->format($timestamp, 'custom', $formate, $timezone);
      return $date;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getEvaluatorOfUser($uid, $transition) {
    $entity_storage = $this->entityTypeManager->getStorage('user_workflow_history_entity');
    $id = $entity_storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('field_workflow_status_after', $transition)
      ->condition('field_user', $uid)
      ->execute();
    if (!empty($id)) {
      $obj = $entity_storage->load(reset($id));
      $uid = $obj->getOwner()->id();
      return $uid;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCountByUid($uid = NULL, $type = NULL): int {
    if (empty($uid)) {
      $uid = \Drupal::currentUser()->id();
    }
    if (!in_array($type, ['service', 'user'])) {
      $type = NULL;
    }
    // Fetch notificaitons.
    if ($type == NULL) {
      $npestorage = \Drupal::entityTypeManager()->getStorage('notification_processed_entity');
      $query = $npestorage->getQuery();
      $count = $query->accessCheck(FALSE)
        ->condition('field_recipient', $uid)
        ->condition('field_read', 0)
        ->count()
        ->execute();
    }
    else {
      $count = 0;
      $npestorage = \Drupal::entityTypeManager()->getStorage('notification_processed_entity');
      $nestorage = \Drupal::entityTypeManager()->getStorage('notification_entity');
      $query = $npestorage->getQuery();
      $notify = $query->accessCheck(FALSE)
        ->condition('field_recipient', $uid)
        ->condition('field_read', 0)
        ->execute();
      if (!empty($notify)) {
        foreach ($notify as $item) {
          $npe = $npestorage->load($item);
          // Load notification entity.
          $ne = $nestorage->load($npe->get('field_notification_id')->getString());
          if ($ne->get('field_entity_type')->getString() == $type) {
            $count++;
          }
          else {
            $count++;
          }
        }
      }
    }
    return $count;
  }

}
