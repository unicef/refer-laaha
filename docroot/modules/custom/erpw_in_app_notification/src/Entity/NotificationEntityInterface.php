<?php

namespace Drupal\erpw_in_app_notification\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Notification entity entities.
 *
 * @ingroup erpw_in_app_notification
 */
interface NotificationEntityInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Notification entity name.
   *
   * @return string
   *   Name of the Notification entity.
   */
  public function getName();

  /**
   * Sets the Notification entity name.
   *
   * @param string $name
   *   The Notification entity name.
   *
   * @return \Drupal\erpw_in_app_notification\Entity\NotificationEntityInterface
   *   The called Notification entity entity.
   */
  public function setName($name);

  /**
   * Gets the Notification entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Notification entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Notification entity creation timestamp.
   *
   * @param int $timestamp
   *   The Notification entity creation timestamp.
   *
   * @return \Drupal\erpw_in_app_notification\Entity\NotificationEntityInterface
   *   The called Notification entity entity.
   */
  public function setCreatedTime($timestamp);

}
