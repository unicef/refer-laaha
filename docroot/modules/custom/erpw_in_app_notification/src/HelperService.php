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
   * Get times ago/date from timestamp.
   *
   * @return void
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

}
