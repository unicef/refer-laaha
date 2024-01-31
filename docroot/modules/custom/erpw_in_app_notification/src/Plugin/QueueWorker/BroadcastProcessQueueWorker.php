<?php

namespace Drupal\erpw_in_app_notification\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\domain\DomainNegotiatorInterface;
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
   * Drupal\domain\DomainNegotiatorInterface definition.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * Constructure for NotificationProcessQueueWorker.
   */
  public function __construct(array $configuration,
  $plugin_id,
  $plugin_definition,
  EntityTypeManagerInterface $entityTypeManager,
  DomainNegotiatorInterface $domain_negotiator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entityTypeManager;
    $this->domainNegotiator = $domain_negotiator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('domain.negotiator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($id) {
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
    $org_type = $notification->get('field_organisation')->getString();
    $org = $notification->get('field_organisation')->getString();
    $location = $notification->get('field_location')->getString();
    $location_list = \Drupal::service('erpw_location.location_services')->getChildrenByParent($location);
    $uids = [];
    if ($notification->get('field_roles')[0] != '') {
      $roles = explode(', ', $notification->get('field_roles')->getString());
      if (in_array('service_provider_staff', $roles)) {
        $staffuserquery = $this->entityTypeManager->getStorage('user')->getQuery();
        $staffuserquery->condition('status', 1)
          ->condition('roles', 'service_provider_staff')
          ->condition('field_location', $location_list, 'IN')
          ->accessCheck(FALSE);
        if ($org_type != 'all') {
          $staffuserquery->condition('field_organisation', $org);
        }
        $staffuserquery_result = $staffuserquery->execute();
        if ($staffuserquery_result) {
          $uids = array_merge($uids, $staffuserquery_result);
        }
      }
      if (in_array('service_provider_focal_point', $roles)) {
        $spfpuserquery = $this->entityTypeManager->getStorage('user')->getQuery();
        $spfpuserquery->condition('status', 1)
          ->condition('roles', 'service_provider_focal_point')
          ->condition('field_location', $location_list, 'IN')
          ->accessCheck(FALSE);
        if ($org_type != 'all') {
          $spfpuserquery->condition('field_organisation', $org);
        }
        $spfpuserquery_result = $spfpuserquery->execute();
        if ($spfpuserquery_result) {
          $uids = array_merge($uids, $spfpuserquery_result);
        }
      }
      if (in_array('gbv_focal_point', $roles)) {
        $gbvfpuserquery = $this->entityTypeManager->getStorage('user')->getQuery();
        $gbvfpuserquery_result = $gbvfpuserquery
          ->condition('status', 1)
          ->condition('roles', 'gbv_focal_point')
          ->condition('field_location', $location_list, 'IN')
          ->accessCheck(FALSE)
          ->execute();

        if ($gbvfpuserquery_result) {
          $uids = array_merge($uids, $gbvfpuserquery_result);
        }
      }
      if (in_array('interagency_gbv_coordinator', $roles)) {
        $iauserquery = $this->entityTypeManager->getStorage('user')->getQuery();
        $iauserquery_result = $iauserquery
          ->condition('status', 1)
          ->condition('roles', 'interagency_gbv_coordinator')
          ->condition('field_location', $location_list, 'IN')
          ->accessCheck(FALSE)
          ->execute();

        if ($iauserquery_result) {
          $uids = array_merge($uids, $iauserquery_result);
        }
      }
      if (in_array('country_admin', $roles)) {
        $causerquery = $this->entityTypeManager->getStorage('user')->getQuery();
        $causerquery_result = $causerquery
          ->condition('status', 1)
          ->condition('roles', 'country_admin')
          ->condition('field_location', $location_list, 'IN')
          ->accessCheck(FALSE)
          ->execute();

        if ($causerquery_result) {
          $uids = array_merge($uids, $causerquery_result);
        }
      }
    }
    // Remove duplicates.
    $uids = array_unique($uids);
    return $uids;
  }

}
