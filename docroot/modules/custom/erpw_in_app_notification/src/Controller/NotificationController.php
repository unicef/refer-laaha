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
    return [
      '#theme' => 'user_notification',
      '#user' => [1,2,3,4,5],
      '#service' => [1,2,3,4,5],
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
