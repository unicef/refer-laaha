<?php

namespace Drupal\erpw_in_app_notification\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Notification processed entity entities.
 *
 * @ingroup erpw_in_app_notification
 */
interface NotificationProcessedEntityInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Notification processed entity name.
   *
   * @return string
   *   Name of the Notification processed entity.
   */
  public function getName();

  /**
   * Sets the Notification processed entity name.
   *
   * @param string $name
   *   The Notification processed entity name.
   *
   * @return \Drupal\erpw_in_app_notification\Entity\NotificationProcessedEntityInterface
   *   The called Notification processed entity entity.
   */
  public function setName($name);

  /**
   * Gets the Notification processed entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Notification processed entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Notification processed entity creation timestamp.
   *
   * @param int $timestamp
   *   The Notification processed entity creation timestamp.
   *
   * @return \Drupal\erpw_in_app_notification\Entity\NotificationProcessedEntityInterface
   *   The called Notification processed entity entity.
   */
  public function setCreatedTime($timestamp);

}
