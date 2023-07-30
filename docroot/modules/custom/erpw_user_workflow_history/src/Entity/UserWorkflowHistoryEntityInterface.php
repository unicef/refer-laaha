<?php

namespace Drupal\erpw_user_workflow_history\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining User workflow history entity entities.
 *
 * @ingroup erpw_user_workflow_history
 */
interface UserWorkflowHistoryEntityInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the User workflow history entity name.
   *
   * @return string
   *   Name of the User workflow history entity.
   */
  public function getName();

  /**
   * Sets the User workflow history entity name.
   *
   * @param string $name
   *   The User workflow history entity name.
   *
   * @return \Drupal\erpw_user_workflow_history\Entity\UserWorkflowHistoryEntityInterface
   *   The called User workflow history entity entity.
   */
  public function setName($name);

  /**
   * Gets the User workflow history entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the User workflow history entity.
   */
  public function getCreatedTime();

  /**
   * Sets the User workflow history entity creation timestamp.
   *
   * @param int $timestamp
   *   The User workflow history entity creation timestamp.
   *
   * @return \Drupal\erpw_user_workflow_history\Entity\UserWorkflowHistoryEntityInterface
   *   The called User workflow history entity entity.
   */
  public function setCreatedTime($timestamp);

}
