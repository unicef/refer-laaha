<?php

namespace Drupal\erpw_in_app_notification\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Notification Process Queue Worker.
 *
 * @QueueWorker(
 *   id = "notification_process_queue_worker",
 *   title = @Translation("Notification Process Queue Worker."),
 *   cron = {"time" = 60}
 * )
 */
class NotificationProcessQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

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
   * Process item function.
   */
  public function processItem($id) {
    $notification = $this->entityTypeManager->getStorage('notification_entity')->load($id);
    $npstorage = $this->entityTypeManager->getStorage('notification_processed_entity');
    $userquery = $this->entityTypeManager->getStorage('user')->getQuery();
    // Service entity.
    if ($notification->get('field_entity_type')->getString() == 'service') {
      // Load event details.
      $event = \Drupal::service('erpw_in_app_notification.default')->getEventDetailsByEventId($notification->get('field_event_id')->getString());
      // Process the message.
      $message = $event['message'];
      $message = str_replace("@service_name", '<strong>' . $notification->get('field_title')->getString() . '</strong>', $message);
      $message = str_replace("@operation", '<strong>' . $notification->get('field_operation')->getString() . '</strong>', $message);
      if (!$notification->get('field_workflow_action')->isEmpty()) {
        $message = str_replace("@status", '<strong>' . $notification->get('field_workflow_action')->getString() . '</strong>', $message);
      }
      $author = $notification->getOwner();
      $name = $author->get('field_first_name')->getString();
      $name .= ' ' . $author->get('field_last_name')->getString();
      $message = str_replace("@name", '<strong>' . $name . '</strong>', $message);

      // Process the users.
      if (!$notification->get('field_specific_user')->getString()) {
        $roles = explode(', ', $notification->get('field_role')->getString());
        if (in_array('service_provider_staff', $roles)) {
          // @todo
        }
        if (in_array('service_provider_focal_point', $roles)) {

          // FP based on organisation.
          $org = $notification->get('field_organisation')->getString();
          $uids = $userquery->condition('status', 1)
            ->condition('roles', 'service_provider_focal_point')
            ->condition('field_organisation', $org)
            ->accessCheck(FALSE)
            ->execute();
          // Create notification.
          if (!empty($uids)) {
            foreach ($uids as $uid) {
              if ($uid == $notification->getOwnerId()) {
                continue;
              }
              $npstorage->create([
                'field_notification_id' => $notification->id(),
                'field_read' => 0,
                'field_message_long' => $message,
                'field_type_of_notification' => 'notification',
                'field_recipient' => $uid,
                'field_icon' => isset($event['icon_uri']) ? \Drupal::service('file_url_generator')->transformRelative($event['icon_uri']) : NULL,
                'name' => 'NID ' . $notification->id() . ' EID ' . $notification->get('field_entity_id')->getString() . ' - UID ' . $uid,
              ])->save();
            }
          }
        }
        if (in_array('gbv_focal_point', $roles)) {
          // Based on the root location or country.
          if (!$notification->get('field_location')->isEmpty()) {
            $locationid = $notification->get('field_location')->getString();
            $location_list_ia = \Drupal::service('erpw_location.location_services')->getChildrenByParent($locationid);
            $gbvfpuserquery = \Drupal::entityTypeManager()->getStorage('user')->getQuery();
            $uids = $gbvfpuserquery->condition('status', 1)
              ->condition('roles', 'gbv_focal_point')
              ->condition('field_location', $location_list_ia, 'IN')
              ->accessCheck(FALSE)
              ->execute();
          }
          // Create notification.
          if (!empty($uids)) {
            foreach ($uids as $uid) {
              if ($uid == $notification->getOwnerId()) {
                continue;
              }
              $npstorage->create([
                'field_notification_id' => $notification->id(),
                'field_read' => 0,
                'field_message_long' => $message,
                'field_type_of_notification' => 'notification',
                'field_recipient' => $uid,
                'field_icon' => isset($event['icon_uri']) ? \Drupal::service('file_url_generator')->transformRelative($event['icon_uri']) : NULL,
                'name' => 'NID ' . $notification->id() . ' EID ' . $notification->get('field_entity_id')->getString() . ' - UID ' . $uid,
              ])->save();
            }
          }
        }
        if (in_array('interagency_gbv_coordinator', $roles)) {
          // Based on the root location or country.
          if (!$notification->get('field_location')->isEmpty()) {
            $locationid = $notification->get('field_location')->getString();
            $location_list_ia = \Drupal::service('erpw_location.location_services')->getChildrenByParent($locationid);
            $uids = $userquery->condition('status', 1)
              ->condition('roles', 'interagency_gbv_coordinator')
              ->condition('field_location', $location_list_ia, 'IN')
              ->accessCheck(FALSE)
              ->execute();
          }
          // Create notification.
          if (!empty($uids)) {
            foreach ($uids as $uid) {
              if ($uid == $notification->getOwnerId()) {
                continue;
              }
              $npstorage->create([
                'field_notification_id' => $notification->id(),
                'field_read' => 0,
                'field_message_long' => $message,
                'field_type_of_notification' => 'notification',
                'field_recipient' => $uid,
                'field_icon' => isset($event['icon_uri']) ? \Drupal::service('file_url_generator')->transformRelative($event['icon_uri']) : NULL,
                'name' => 'NID ' . $notification->id() . ' EID ' . $notification->get('field_entity_id')->getString() . ' - UID ' . $uid,
              ])->save();
            }
          }
        }
        if (in_array('country_admin', $roles)) {
          // Based on the root location or country.
          if (!$notification->get('field_root_location')->isEmpty()) {
            $eid = $notification->get('field_root_location')->getString();
            $location_entity = \Drupal::entityTypeManager()->getStorage('location')->load($eid);
            if ($location_entity) {
              if ($location_entity->hasField('field_location_taxonomy_term') && !$location_entity->get('field_location_taxonomy_term')->isEmpty()) {
                $locationid = $location_entity->get('field_location_taxonomy_term')->getValue()[0]['target_id'];
                $location_list = \Drupal::service('erpw_location.location_services')->getChildrenByParent($locationid);
                $countryuserquery = \Drupal::entityTypeManager()->getStorage('user')->getQuery();
                $uids = $countryuserquery->condition('status', 1)
                  ->condition('roles', 'country_admin')
                  ->condition('field_location', $location_list, 'IN')
                  ->accessCheck(FALSE)
                  ->execute();
              }
            }
          }
          // Create notification.
          if (!empty($uids)) {
            foreach ($uids as $uid) {
              if ($uid == $notification->getOwnerId()) {
                continue;
              }
              $npstorage->create([
                'field_notification_id' => $notification->id(),
                'field_read' => 0,
                'field_message_long' => $message,
                'field_type_of_notification' => 'notification',
                'field_recipient' => $uid,
                'field_icon' => isset($event['icon_uri']) ? \Drupal::service('file_url_generator')->transformRelative($event['icon_uri']) : NULL,
                'name' => 'NID ' . $notification->id() . ' EID ' . $notification->get('field_entity_id')->getString() . ' - UID ' . $uid,
              ])->save();
            }
          }
        }
      }
      else {
        // Never this two field have value together.
        if (!$notification->get('field_users_list')->isEmpty()) {
          $userstring = $notification->get('field_users_list')->getString();
          $uids = explode(", ", $userstring);
          // Create notification.
          if (!empty($uids)) {
            foreach ($uids as $uid) {
              if ($uid == $notification->getOwnerId()) {
                continue;
              }
              $npstorage->create([
                'field_notification_id' => $notification->id(),
                'field_read' => 0,
                'field_message_long' => $message,
                'field_type_of_notification' => 'notification',
                'field_recipient' => $uid,
                'field_icon' => isset($event['icon_uri']) ? \Drupal::service('file_url_generator')->transformRelative($event['icon_uri']) : NULL,
                'name' => 'NID ' . $notification->id() . ' EID ' . $notification->get('field_entity_id')->getString() . ' - UID ' . $uid,
              ])->save();
            }
          }
        }
        if (!$notification->get('field_excluded_users')->isEmpty()) {
          $userstring = $notification->get('field_excluded_users')->getString();
          $excludeuids = explode(", ", $userstring);
          $roles = explode(', ', $notification->get('field_role')->getString());
          if (in_array('service_provider_staff', $roles)) {
            // @todo
          }
          if (in_array('service_provider_focal_point', $roles)) {

            // FP based on organisation.
            $org = $notification->get('field_organisation')->getString();
            $users = $userquery->condition('status', 1)
              ->condition('roles', 'service_provider_focal_point')
              ->condition('field_organisation', $org)
              ->accessCheck(FALSE)
              ->execute();
            // Exclude the users.
            if (!empty($users)) {
              $uids = array_diff($users, $excludeuids);
            }
            // Create notification.
            if (!empty($uids)) {
              foreach ($uids as $uid) {
                if ($uid == $notification->getOwnerId()) {
                  continue;
                }
                $npstorage->create([
                  'field_notification_id' => $notification->id(),
                  'field_read' => 0,
                  'field_message_long' => $message,
                  'field_type_of_notification' => 'notification',
                  'field_recipient' => $uid,
                  'field_icon' => isset($event['icon_uri']) ? \Drupal::service('file_url_generator')->transformRelative($event['icon_uri']) : NULL,
                  'name' => 'NID ' . $notification->id() . ' EID ' . $notification->get('field_entity_id')->getString() . ' - UID ' . $uid,
                ])->save();
              }
            }
          }
          if (in_array('gbv_focal_point', $roles)) {
            // Based on the root location or country.
            if (!$notification->get('field_location')->isEmpty()) {
              $locationid = $notification->get('field_location')->getString();
              $location_list_ia = \Drupal::service('erpw_location.location_services')->getChildrenByParent($locationid);
              $gbvfpuserquery = \Drupal::entityTypeManager()->getStorage('user')->getQuery();
              $uids = $gbvfpuserquery->condition('status', 1)
                ->condition('roles', 'gbv_focal_point')
                ->condition('field_location', $location_list_ia, 'IN')
                ->accessCheck(FALSE)
                ->execute();
            }
            // Exclude the users.
            if (!empty($users)) {
              $uids = array_diff($users, $excludeuids);
            }
            // Create notification.
            if (!empty($uids)) {
              foreach ($uids as $uid) {
                if ($uid == $notification->getOwnerId()) {
                  continue;
                }
                $npstorage->create([
                  'field_notification_id' => $notification->id(),
                  'field_read' => 0,
                  'field_message_long' => $message,
                  'field_type_of_notification' => 'notification',
                  'field_recipient' => $uid,
                  'field_icon' => isset($event['icon_uri']) ? \Drupal::service('file_url_generator')->transformRelative($event['icon_uri']) : NULL,
                  'name' => 'NID ' . $notification->id() . ' EID ' . $notification->get('field_entity_id')->getString() . ' - UID ' . $uid,
                ])->save();
              }
            }
          }
          if (in_array('interagency_gbv_coordinator', $roles)) {
            // Based on the root location or country.
            if (!$notification->get('field_location')->isEmpty()) {
              $locationid = $notification->get('field_location')->getString();
              $location_list_ia = \Drupal::service('erpw_location.location_services')->getChildrenByParent($locationid);
              $users = $userquery->condition('status', 1)
                ->condition('roles', 'interagency_gbv_coordinator')
                ->condition('field_location', $location_list_ia, 'IN')
                ->accessCheck(FALSE)
                ->execute();
            }
            // Exclude the users.
            if (!empty($users)) {
              $uids = array_diff($users, $excludeuids);
            }
            // Create notification.
            if (!empty($uids)) {
              foreach ($uids as $uid) {
                if ($uid == $notification->getOwnerId()) {
                  continue;
                }
                $npstorage->create([
                  'field_notification_id' => $notification->id(),
                  'field_read' => 0,
                  'field_message_long' => $message,
                  'field_type_of_notification' => 'notification',
                  'field_recipient' => $uid,
                  'field_icon' => isset($event['icon_uri']) ? \Drupal::service('file_url_generator')->transformRelative($event['icon_uri']) : NULL,
                  'name' => 'NID ' . $notification->id() . ' EID ' . $notification->get('field_entity_id')->getString() . ' - UID ' . $uid,
                ])->save();
              }
            }
          }
          if (in_array('country_admin', $roles)) {
            // Based on the root location or country.
            if (!$notification->get('field_root_location')->isEmpty()) {
              $eid = $notification->get('field_root_location')->getString();
              $location_entity = \Drupal::entityTypeManager()->getStorage('location')->load($eid);
              $tid = '';
              if ($location_entity) {
                if ($location_entity->hasField('field_location_taxonomy_term') && !$location_entity->get('field_location_taxonomy_term')->isEmpty()) {
                  $locationid = $location_entity->get('field_location_taxonomy_term')->getValue()[0]['target_id'];
                  $location_list = \Drupal::service('erpw_location.location_services')->getChildrenByParent($locationid);
                  $countryuserquery = \Drupal::entityTypeManager()->getStorage('user')->getQuery();
                  $uids = $countryuserquery->condition('status', 1)
                    ->condition('roles', 'country_admin')
                    ->condition('field_location', $location_list, 'IN')
                    ->accessCheck(FALSE)
                    ->execute();
                }
              }
            }
            // Exclude the users.
            if (!empty($users)) {
              $uids = array_diff($users, $excludeuids);
            }
            // Create notification.
            if (!empty($uids)) {
              foreach ($uids as $uid) {
                if ($uid == $notification->getOwnerId()) {
                  continue;
                }
                $npstorage->create([
                  'field_notification_id' => $notification->id(),
                  'field_read' => 0,
                  'field_message_long' => $message,
                  'field_type_of_notification' => 'notification',
                  'field_recipient' => $uid,
                  'field_icon' => isset($event['icon_uri']) ? \Drupal::service('file_url_generator')->transformRelative($event['icon_uri']) : NULL,
                  'name' => 'NID ' . $notification->id() . ' EID ' . $notification->get('field_entity_id')->getString() . ' - UID ' . $uid,
                ])->save();
              }
            }
          }
        }

      }
    }
    // User entity.
    if ($notification->get('field_entity_type')->getString() == 'user') {
      // Load event details.
      $event = \Drupal::service('erpw_in_app_notification.default')->getEventDetailsByEventId($notification->get('field_event_id')->getString());
      // Process the message.
      $message = $event['message'];
      // Generate username.
      $ruserid = $notification->get('field_entity_id')->getString();
      $ruser = User::load($ruserid);
      $username = $ruser->get('field_first_name')->getString() . ' ' . $ruser->get('field_last_name')->getString();
      $message = str_replace("@username", '<strong>' . $username . '</strong>', $message);
      if (!$notification->get('field_workflow_action')->isEmpty()) {
        $message = str_replace("@status", '<strong>' . $notification->get('field_workflow_action')->getString() . '</strong>', $message);
      }
      $author = $notification->getOwner();
      $name = $author->get('field_first_name')->getString();
      $name .= ' ' . $author->get('field_last_name')->getString();
      $message = str_replace("@name", '<strong>' . $name . '</strong>', $message);

      // Process the users.
      if (!$notification->get('field_specific_user')->getString()) {
        $roles = explode(', ', $notification->get('field_role')->getString());
        if (in_array('service_provider_staff', $roles)) {
          // @todo
        }
        if (in_array('service_provider_focal_point', $roles)) {

          // FP based on organisation.
          $org = $notification->get('field_organisation')->getString();
          $uids = $userquery->condition('status', 1)
            ->condition('roles', 'service_provider_focal_point')
            ->condition('field_organisation', $org)
            ->accessCheck(FALSE)
            ->execute();
          // Create notification.
          if (!empty($uids)) {
            foreach ($uids as $uid) {
              $npstorage->create([
                'field_notification_id' => $notification->id(),
                'field_read' => 0,
                'field_message_long' => $message,
                'field_type_of_notification' => 'notification',
                'field_recipient' => $uid,
                'field_icon' => isset($event['icon_uri']) ? \Drupal::service('file_url_generator')->transformRelative($event['icon_uri']) : NULL,
                'name' => 'NID ' . $notification->id() . ' EID ' . $notification->get('field_entity_id')->getString() . ' - UID ' . $uid,
              ])->save();
            }
          }
        }
        if (in_array('gbv_focal_point', $roles)) {
          // Based on the root location or country.
          if (!$notification->get('field_location')->isEmpty()) {
            $locationid = $notification->get('field_location')->getString();
            $location_list_ia = \Drupal::service('erpw_location.location_services')->getChildrenByParent($locationid);
            $gbvfpuserquery = \Drupal::entityTypeManager()->getStorage('user')->getQuery();
            $uids = $gbvfpuserquery->condition('status', 1)
              ->condition('roles', 'gbv_focal_point')
              ->condition('field_location', $location_list_ia, 'IN')
              ->accessCheck(FALSE)
              ->execute();
          }
          // Create notification.
          if (!empty($uids)) {
            foreach ($uids as $uid) {
              $npstorage->create([
                'field_notification_id' => $notification->id(),
                'field_read' => 0,
                'field_message_long' => $message,
                'field_type_of_notification' => 'notification',
                'field_recipient' => $uid,
                'field_icon' => isset($event['icon_uri']) ? \Drupal::service('file_url_generator')->transformRelative($event['icon_uri']) : NULL,
                'name' => 'NID ' . $notification->id() . ' EID ' . $notification->get('field_entity_id')->getString() . ' - UID ' . $uid,
              ])->save();
            }
          }
        }
        if (in_array('interagency_gbv_coordinator', $roles)) {
          // Based on the root location or country.
          if (!$notification->get('field_location')->isEmpty()) {
            $locationid = $notification->get('field_location')->getString();
            $location_list_ia = \Drupal::service('erpw_location.location_services')->getChildrenByParent($locationid);
            $uids = $userquery->condition('status', 1)
              ->condition('roles', 'interagency_gbv_coordinator')
              ->condition('field_location', $location_list_ia, 'IN')
              ->accessCheck(FALSE)
              ->execute();
          }
          // Create notification.
          if (!empty($uids)) {
            foreach ($uids as $uid) {
              $npstorage->create([
                'field_notification_id' => $notification->id(),
                'field_read' => 0,
                'field_message_long' => $message,
                'field_type_of_notification' => 'notification',
                'field_recipient' => $uid,
                'field_icon' => isset($event['icon_uri']) ? \Drupal::service('file_url_generator')->transformRelative($event['icon_uri']) : NULL,
                'name' => 'NID ' . $notification->id() . ' EID ' . $notification->get('field_entity_id')->getString() . ' - UID ' . $uid,
              ])->save();
            }
          }
        }
        if (in_array('country_admin', $roles)) {
          // Based on the root location or country.
          if (!$notification->get('field_root_location')->isEmpty()) {
            $eid = $notification->get('field_root_location')->getString();
            $location_entity = \Drupal::entityTypeManager()->getStorage('location')->load($eid);
            if ($location_entity) {
              if ($location_entity->hasField('field_location_taxonomy_term') && !$location_entity->get('field_location_taxonomy_term')->isEmpty()) {
                $locationid = $location_entity->get('field_location_taxonomy_term')->getValue()[0]['target_id'];
                $location_list = \Drupal::service('erpw_location.location_services')->getChildrenByParent($locationid);
                $countryuserquery = \Drupal::entityTypeManager()->getStorage('user')->getQuery();
                $uids = $countryuserquery->condition('status', 1)
                  ->condition('roles', 'country_admin')
                  ->condition('field_location', $location_list, 'IN')
                  ->accessCheck(FALSE)
                  ->execute();
              }
            }
          }
          // Create notification.
          if (!empty($uids)) {
            foreach ($uids as $uid) {
              $npstorage->create([
                'field_notification_id' => $notification->id(),
                'field_read' => 0,
                'field_message_long' => $message,
                'field_type_of_notification' => 'notification',
                'field_recipient' => $uid,
                'field_icon' => isset($event['icon_uri']) ? \Drupal::service('file_url_generator')->transformRelative($event['icon_uri']) : NULL,
                'name' => 'NID ' . $notification->id() . ' EID ' . $notification->get('field_entity_id')->getString() . ' - UID ' . $uid,
              ])->save();
            }
          }
        }
      }
      else {
        // Never this two field have value together.
        if (!$notification->get('field_users_list')->isEmpty()) {
          $userstring = $notification->get('field_users_list')->getString();
          $uids = explode(", ", $userstring);
          // Create notification.
          if (!empty($uids)) {
            foreach ($uids as $uid) {
              $npstorage->create([
                'field_notification_id' => $notification->id(),
                'field_read' => 0,
                'field_message_long' => $message,
                'field_type_of_notification' => 'notification',
                'field_recipient' => $uid,
                'field_icon' => isset($event['icon_uri']) ? \Drupal::service('file_url_generator')->transformRelative($event['icon_uri']) : NULL,
                'name' => 'NID ' . $notification->id() . ' EID ' . $notification->get('field_entity_id')->getString() . ' - UID ' . $uid,
              ])->save();
            }
          }
        }
        if (!$notification->get('field_excluded_users')->isEmpty()) {
          $userstring = $notification->get('field_excluded_users')->getString();
          $excludeuids = explode(", ", $userstring);
          $roles = explode(', ', $notification->get('field_role')->getString());
          if (in_array('service_provider_staff', $roles)) {
            // @todo
          }
          if (in_array('service_provider_focal_point', $roles)) {

            // FP based on organisation.
            $org = $notification->get('field_organisation')->getString();
            $users = $userquery->condition('status', 1)
              ->condition('roles', 'service_provider_focal_point')
              ->condition('field_organisation', $org)
              ->accessCheck(FALSE)
              ->execute();
            // Exclude the users.
            if (!empty($users)) {
              $uids = array_diff($users, $excludeuids);
            }
            // Create notification.
            if (!empty($uids)) {
              foreach ($uids as $uid) {
                $npstorage->create([
                  'field_notification_id' => $notification->id(),
                  'field_read' => 0,
                  'field_message_long' => $message,
                  'field_type_of_notification' => 'notification',
                  'field_recipient' => $uid,
                  'field_icon' => isset($event['icon_uri']) ? \Drupal::service('file_url_generator')->transformRelative($event['icon_uri']) : NULL,
                  'name' => 'NID ' . $notification->id() . ' EID ' . $notification->get('field_entity_id')->getString() . ' - UID ' . $uid,
                ])->save();
              }
            }
          }
          if (in_array('gbv_focal_point', $roles)) {
            // Based on the root location or country.
            if (!$notification->get('field_location')->isEmpty()) {
              $locationid = $notification->get('field_location')->getString();
              $location_list_ia = \Drupal::service('erpw_location.location_services')->getChildrenByParent($locationid);
              $gbvfpuserquery = \Drupal::entityTypeManager()->getStorage('user')->getQuery();
              $uids = $gbvfpuserquery->condition('status', 1)
                ->condition('roles', 'gbv_focal_point')
                ->condition('field_location', $location_list_ia, 'IN')
                ->accessCheck(FALSE)
                ->execute();
            }
            // Exclude the users.
            if (!empty($users)) {
              $uids = array_diff($users, $excludeuids);
            }
            // Create notification.
            if (!empty($uids)) {
              foreach ($uids as $uid) {
                $npstorage->create([
                  'field_notification_id' => $notification->id(),
                  'field_read' => 0,
                  'field_message_long' => $message,
                  'field_type_of_notification' => 'notification',
                  'field_recipient' => $uid,
                  'field_icon' => isset($event['icon_uri']) ? \Drupal::service('file_url_generator')->transformRelative($event['icon_uri']) : NULL,
                  'name' => 'NID ' . $notification->id() . ' EID ' . $notification->get('field_entity_id')->getString() . ' - UID ' . $uid,
                ])->save();
              }
            }
          }
          if (in_array('interagency_gbv_coordinator', $roles)) {
            // Based on the root location or country.
            if (!$notification->get('field_location')->isEmpty()) {
              $locationid = $notification->get('field_location')->getString();
              $location_list_ia = \Drupal::service('erpw_location.location_services')->getChildrenByParent($locationid);
              $users = $userquery->condition('status', 1)
                ->condition('roles', 'interagency_gbv_coordinator')
                ->condition('field_location', $location_list_ia, 'IN')
                ->accessCheck(FALSE)
                ->execute();
            }
            // Exclude the users.
            if (!empty($users)) {
              $uids = array_diff($users, $excludeuids);
            }
            // Create notification.
            if (!empty($uids)) {
              foreach ($uids as $uid) {
                $npstorage->create([
                  'field_notification_id' => $notification->id(),
                  'field_read' => 0,
                  'field_message_long' => $message,
                  'field_type_of_notification' => 'notification',
                  'field_recipient' => $uid,
                  'field_icon' => isset($event['icon_uri']) ? \Drupal::service('file_url_generator')->transformRelative($event['icon_uri']) : NULL,
                  'name' => 'NID ' . $notification->id() . ' EID ' . $notification->get('field_entity_id')->getString() . ' - UID ' . $uid,
                ])->save();
              }
            }
          }
          if (in_array('country_admin', $roles)) {
            // Based on the root location or country.
            if (!$notification->get('field_root_location')->isEmpty()) {
              $eid = $notification->get('field_root_location')->getString();
              $location_entity = \Drupal::entityTypeManager()->getStorage('location')->load($eid);
              $tid = '';
              if ($location_entity) {
                if ($location_entity->hasField('field_location_taxonomy_term') && !$location_entity->get('field_location_taxonomy_term')->isEmpty()) {
                  $locationid = $location_entity->get('field_location_taxonomy_term')->getValue()[0]['target_id'];
                  $location_list = \Drupal::service('erpw_location.location_services')->getChildrenByParent($locationid);
                  $countryuserquery = \Drupal::entityTypeManager()->getStorage('user')->getQuery();
                  $users = $countryuserquery->condition('status', 1)
                    ->condition('roles', 'country_admin')
                    ->condition('field_location', $location_list, 'IN')
                    ->accessCheck(FALSE)
                    ->execute();
                }
              }
            }
            // Exclude the users.
            if (!empty($users)) {
              $uids = array_diff($users, $excludeuids);
            }
            // Create notification.
            if (!empty($uids)) {
              foreach ($uids as $uid) {
                $npstorage->create([
                  'field_notification_id' => $notification->id(),
                  'field_read' => 0,
                  'field_message_long' => $message,
                  'field_type_of_notification' => 'notification',
                  'field_recipient' => $uid,
                  'field_icon' => isset($event['icon_uri']) ? \Drupal::service('file_url_generator')->transformRelative($event['icon_uri']) : NULL,
                  'name' => 'NID ' . $notification->id() . ' EID ' . $notification->get('field_entity_id')->getString() . ' - UID ' . $uid,
                ])->save();
              }
            }
          }
        }

      }
    }
    // Make the Notification entity processed.
    $notification->set('field_processed', TRUE);
    $notification->save();
  }

}
