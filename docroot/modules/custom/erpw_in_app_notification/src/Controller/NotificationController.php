<?php

namespace Drupal\erpw_in_app_notification\Controller;

use Drupal\Core\Controller\ControllerBase;
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

      case in_array('interagency_gbv_coordinator', $roles) || in_array('country_admin', $roles):
        $role = 'gbv_coordination';
        break;

      default:
        $role = 'default';
    }
    return [
      '#theme' => 'user_notification',
      '#role' => $role,
      '#user' => [1, 2, 3, 4, 5],
      '#service' => [1, 2, 3, 4, 5],
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
