<?php

namespace Drupal\erpw_in_app_notification\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NotificationController.
 */
class NotificationController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * Usernotification.
   *
   * @return string
   *   Return Hello string.
   */
  public function userNotification() {

    $cu = \Drupal::currentUser();
    $roles = $cu->getRoles();

    switch (TRUE) {
      case in_array('service_provider_staff', $roles):
        $role = 'staff';
        break;

      case in_array('service_provider_focal_point', $roles):
        $role = 'focal_point';
        break;

      case in_array('gbv_focal_point', $roles):
        $role = 'gbv_focal_point';
        break;

      case in_array('interagency_gbv_coordinator', $roles) || in_array('country_admin', $roles):
        $role = 'gbv_coordination';
        break;

      default:
        $role = 'default';
    }
    // Fetch service notificaiton.
    $npestorage = \Drupal::entityTypeManager()->getStorage('notification_processed_entity');
    $nestorage = \Drupal::entityTypeManager()->getStorage('notification_entity');
    $broadcaststorage = \Drupal::entityTypeManager()->getStorage('broadcast_notification_entity');
    $query = $npestorage->getQuery();
    $notify = $query->accessCheck(FALSE)
      ->condition('field_recipient', \Drupal::currentUser()->id())
      ->sort('id', 'DESC')
      ->execute();
    $service = $user = $broadcasts = [];
    $servicecount = $usercount = $broadcastcount = 0;
    if (!empty($notify)) {
      foreach ($notify as $item) {
        $npe = $npestorage->load($item);
        if ($npe->get('field_type_of_notification')->getString() == 'broadcast') {
          // Load notification entity.
          $broadcast = $broadcaststorage->load($npe->get('field_notification_id')->getString());
          $broadcasts[$item]['icon'] = $npe->get('field_icon')->getString();
          $broadcasts[$item]['message'] = $npe->get('field_message_string')->getString();
          $broadcasts[$item]['color'] = $npe->get('field_color')->getString();
          $broadcasts[$item]['created'] = \Drupal::service('erpw_in_app_notification.default')->getDynamicDateFormate($broadcast->get('created')->getString());
          $broadcasts[$item]['read_status'] = $npe->get('field_read')->getString() ? 'read' : 'unread';
          $broadcasts[$item]['notification_type'] = 'broadcast';
          if (!$npe->get('field_read')->getString()) {
            $broadcastcount++;
          }
          // Once the info notification delivered, that mark as read.
          if ($npe->get('field_read')->getString() == 0) {
            $npe->set('field_read', 1);
            $npe->save();
          }
        }
        if ($npe->get('field_type_of_notification')->getString() == 'notification') {
          // Load notification entity.
          $ne = $nestorage->load($npe->get('field_notification_id')->getString());
          if ($ne->get('field_entity_type')->getString() == 'service') {
            $service[$item]['icon'] = $npe->get('field_icon')->getString();
            $service[$item]['message'] = $npe->get('field_message_string')->getString();
            $service[$item]['link'] = Url::fromRoute('erpw_webform.service_moderate_content', ['webform_submission' => $ne->get('field_entity_id')->getString()], ['query' => ['_npeid' => $npe->id()]])->toString();
            $service[$item]['behavior'] = $ne->get('field_behavior')->getString();
            $service[$item]['created'] = \Drupal::service('erpw_in_app_notification.default')->getDynamicDateFormate($ne->get('created')->getString());
            $service[$item]['read_status'] = $npe->get('field_read')->getString() ? 'read' : 'unread';
            $service[$item]['notification_type'] = 'notification';
            if (!$npe->get('field_read')->getString()) {
              $servicecount++;
            }
          }
          else {
            $user[$item]['icon'] = $npe->get('field_icon')->getString();
            $user[$item]['message'] = $npe->get('field_message_string')->getString();
            $user[$item]['link'] = Url::fromRoute('entity.user.canonical', ['user' => $ne->get('field_entity_id')->getString()], ['query' => ['_npeid' => $npe->id()]])->toString();
            $user[$item]['behavior'] = $ne->get('field_behavior')->getString();
            $user[$item]['created'] = \Drupal::service('erpw_in_app_notification.default')->getDynamicDateFormate($ne->get('created')->getString());
            $user[$item]['read_status'] = $npe->get('field_read')->getString() ? 'read' : 'unread';
            $user[$item]['notification_type'] = 'notification';
            if (!$npe->get('field_read')->getString()) {
              $usercount++;
            }
          }
          // Once the info notification delivered, that mark as read.
          if ($ne->get('field_behavior')->getString() == 'info') {
            if ($npe->get('field_read')->getString() == 0) {
              $npe->set('field_read', 1);
              $npe->save();
            }
          }
        }
      }
    }
    return [
      '#theme' => 'user_notification',
      '#role' => $role,
      '#user' => $user,
      '#service' => $service,
      '#broadcast' => $broadcasts,
      '#count' => [
        'service' => $servicecount,
        'user' => $usercount,
        'broadcast' => $broadcastcount,
      ],
      '#common_var' => [
        'module_path' => \Drupal::service('extension.list.module')->getPath('erpw_in_app_notification'),
      ],
      '#attached' => [
        'library' => [
          'erpw_in_app_notification/notification',
          'unicef_iconpicker/unicef-iconpicker',
        ],
      ],
    ];
  }

  /**
   * Usernotification.
   *
   * @return string
   *   Return Hello string.
   */
  public function userNotificationModal() {
    // Maximum of 3 items remainings.
    return [
      '#theme' => 'user_notification_modal',
      '#combined' => [1, 2, 3],
      '#common_var' => [
        'module_path' => \Drupal::service('extension.list.module')->getPath('erpw_in_app_notification'),
      ],
      '#attached' => [
        'library' => [
          'erpw_in_app_notification/notification',
        ],
      ],
    ];
  }

}
