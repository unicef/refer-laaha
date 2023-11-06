<?php

namespace Drupal\erpw_in_app_notification\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

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

      case in_array('interagency_gbv_coordinator', $roles) || in_array('country_admin', $roles):
        $role = 'gbv_coordination';
        break;

      default:
        $role = 'default';
    }
    // Fetch service notificaiton.
    $npestorage = \Drupal::entityTypeManager()->getStorage('notification_processed_entity');
    $nestorage = \Drupal::entityTypeManager()->getStorage('notification_entity');
    $query = $npestorage->getQuery();
    $notify = $query->accessCheck(FALSE)
      ->condition('field_recipient', \Drupal::currentUser()->id())
      ->sort('created', 'DESC')
      ->execute();
    $service = $user = [];
    if (!empty($notify)) {
      foreach ($notify as $item) {
        $npe = $npestorage->load($item);
        // Load notification entity.
        $ne = $nestorage->load($npe->get('field_notification_id')->getString());
        if ($ne->get('field_entity_type')->getString() == 'service') {
          $service[$item]['icon'] = $npe->get('field_icon')->getString();
          $service[$item]['message'] = $npe->get('field_message_string')->getString();
          $service[$item]['link'] = Url::fromRoute('erpw_webform.service_moderate_content', ['webform_submission' => $ne->get('field_entity_id')->getString()], ['query' => ['_npeid' => $npe->id()]])->toString();
          $service[$item]['behavior'] = $ne->get('field_behavior')->getString();
          $service[$item]['created'] = \Drupal::service('erpw_in_app_notification.default')->getDynamicDateFormate($ne->get('created')->getString());
          $service[$item]['read_status'] = $npe->get('field_read')->getString() ? 'read' : 'unread';
        }
        else {
          // @todo
        }
      }
    }
    return [
      '#theme' => 'user_notification',
      '#role' => $role,
      '#user' => $user,
      '#service' => $service,
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
