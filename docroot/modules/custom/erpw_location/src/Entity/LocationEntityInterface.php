<?php

namespace Drupal\erpw_location\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Location Entity entities.
 *
 * @ingroup erpw_location
 */
interface LocationEntityInterface extends
    ContentEntityInterface,
    RevisionLogInterface,
    EntityChangedInterface,
    EntityPublishedInterface,
    EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Location Entity name.
   *
   * @return string
   *   Name of the Location Entity.
   */
  public function getName();

  /**
   * Sets the Location Entity name.
   *
   * @param string $name
   *   The Location Entity name.
   *
   * @return \Drupal\erpw_location\Entity\LocationEntityInterface
   *   The called Location Entity entity.
   */
  public function setName($name);

  /**
   * Gets the Location Entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Location Entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Location Entity creation timestamp.
   *
   * @param int $timestamp
   *   The Location Entity creation timestamp.
   *
   * @return \Drupal\erpw_location\Entity\LocationEntityInterface
   *   The called Location Entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Location Entity revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Location Entity revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\erpw_location\Entity\LocationEntityInterface
   *   The called Location Entity entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Location Entity revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Location Entity revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\erpw_location\Entity\LocationEntityInterface
   *   The called Location Entity entity.
   */
  public function setRevisionUserId($uid);

}
