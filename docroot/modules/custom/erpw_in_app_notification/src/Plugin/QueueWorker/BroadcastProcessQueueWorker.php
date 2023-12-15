<?php

namespace Drupal\erpw_in_app_notification\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines 'broadcast_process_queue_worker' queue worker.
 *
 * @QueueWorker(
 *   id = "broadcast_process_queue_worker",
 *   title = @Translation("BroadcastProcessQueueWorker"),
 *   cron = {"time" = 60}
 * )
 */
class BroadcastProcessQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructure for NotificationProcessQueueWorker.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($id) {
    // @todo Process data here.
    $notification = $this->entityTypeManager->getStorage('broadcast_notification_entity')->load($id);
    $npstorage = $this->entityTypeManager->getStorage('notification_processed_entity');
    // Process the users.
    $lastSent = $notification->get('field_last_sent')->getString();
    $scheduledOptions = $notification->get('field_scheduled_options')->getString();
    // Current Unix timestamp.
    $currentTimestamp = time();
    $uids = [];
    if ($notification->get('field_type')->getString() == 'scheduled') {
      if ($lastSent == '1701367200') {
        $uids = $this->roleBasedNotification($notification);
      }
      else {
        if ($scheduledOptions == 'weekly') {
          // Compare if the last sent is less than a week ago.
          $isPastInterval = ($currentTimestamp - $lastSent) > (7 * 24 * 60 * 60);
        }
        elseif ($scheduledOptions == 'daily') {
          // Compare if the last sent is less than a day ago.
          $isPastInterval = ($currentTimestamp - $lastSent) > (24 * 60 * 60);
        }
        elseif ($scheduledOptions == 'quaterly') {
          // Compare if the last sent is less than three months ago.
          $isPastInterval = ($currentTimestamp - $lastSent) > (3 * 30 * 24 * 60 * 60);
        }
        elseif ($scheduledOptions == 'monthly') {
          // Compare if the last sent is less than a month ago.
          $isPastInterval = ($currentTimestamp - $lastSent) > (30 * 24 * 60 * 60);
        }
        if ($isPastInterval) {
          $uids = $this->roleBasedNotification($notification);
        }
      }
    }
    if ($notification->get('field_type')->getString() == 'norecurrence') {
      if ($lastSent == '1701367200') {
        $uids = $this->roleBasedNotification($notification);
      }
    }
    $message = \Drupal::service('erpw_in_app_notification.default')->getBroadcastNotificationMessage($notification);
    // Create notification.
    if (!empty($uids)) {
      foreach ($uids as $uid) {
        $npstorage->create([
          'field_notification_id' => $notification->id(),
          'field_read' => 0,
          'field_message_string' => $message['message'],
          'field_type_of_notification' => 'broadcast',
          'field_recipient' => $uid,
          'field_color' => $message['color'],
          'field_icon' => $message['icon'],
          'name' => 'NID ' . $notification->id() . ' Type Broadcast ' . ' - UID ' . $uid,
        ])->save();
      }
    }
    // Make the Notification entity processed.
    $notification->set('field_last_sent', time());
    $notification->save();
  }

  /**
   * Role based notification processing.
   */
  public function roleBasedNotification($notification) : array {
    $userquery = $this->entityTypeManager->getStorage('user')->getQuery();
    $org = $notification->get('field_organisation')->getString();
    $location = $notification->get('field_location')->getString();
    $location_list = \Drupal::service('erpw_location.location_services')->getChildrenByParent($location);
    $uids = [];
    if ($notification->get('field_roles')[0] != '') {
      $roles = explode(', ', $notification->get('field_roles')->getString());
      if (in_array('service_provider_staff', $roles)) {
        $uids = array_merge($uids, $userquery->condition('status', 1)
          ->condition('roles', 'service_provider_staff')
          ->condition('field_location', $location, 'IN')
          ->condition('field_organisation', $org)
          ->accessCheck(FALSE)
          ->execute());
      }
      if (in_array('service_provider_focal_point', $roles)) {
        $uids = array_merge($uids, $userquery->condition('status', 1)
          ->condition('roles', 'service_provider_focal_point')
          ->condition('field_location', $location, 'IN')
          ->condition('field_organisation', $org)
          ->accessCheck(FALSE)
          ->execute());
      }
      if (in_array('interagency_gbv_coordinator', $roles)) {
        // Based on the root location or country.
        $uids = array_merge($uids, $userquery->condition('status', 1)
          ->condition('roles', 'interagency_gbv_coordinator')
          ->condition('field_location', $location_list, 'IN')
          ->accessCheck(FALSE)
          ->execute());
      }
      if (in_array('country_admin', $roles)) {
        $uids = array_merge($uids, $userquery->condition('status', 1)
          ->condition('roles', 'country_admin')
          ->condition('field_location', $location_list, 'IN')
          ->accessCheck(FALSE)
          ->execute());
      }
    }
    else {
      $uids = array_merge($uids, $userquery->condition('status', 1)
        ->condition('field_location', $location, 'IN')
        ->condition('field_organisation', $org)
        ->accessCheck(FALSE)
        ->execute());
    }
    // Remove duplicates.
    $uids = array_unique($uids);
    return $uids;
  }

}
