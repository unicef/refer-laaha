<?php

namespace Drupal\erpw_in_app_notification\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'NotificationBlock' block.
 *
 * @Block(
 *  id = "notification_block",
 *  admin_label = @Translation("Notification block"),
 * )
 */
class NotificationBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\erpw_in_app_notification\HelperServiceInterface definition.
   *
   * @var \Drupal\erpw_in_app_notification\HelperServiceInterface
   */
  protected $erpwInAppNotificationDefault;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->erpwInAppNotificationDefault = $container->get('erpw_in_app_notification.default');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#theme'] = 'notification_block';
     $build['notification_block']['#markup'] = 'Implement NotificationBlock.';

    return $build;
  }

}
