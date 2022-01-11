<?php

namespace Drupal\erpw_location;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\erpw_location\Entity\LocationEntityInterface;

/**
 * Defines the storage handler class for Location Entity entities.
 *
 * This extends the base storage class, adding required special handling for
 * Location Entity entities.
 *
 * @ingroup erpw_location
 */
interface LocationEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Location Entity revision IDs for a specific Location Entity.
   *
   * @param \Drupal\erpw_location\Entity\LocationEntityInterface $entity
   *   The Location Entity entity.
   *
   * @return int[]
   *   Location Entity revision IDs (in ascending order).
   */
  public function revisionIds(LocationEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Location Entity author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Location Entity revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\erpw_location\Entity\LocationEntityInterface $entity
   *   The Location Entity entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(LocationEntityInterface $entity);

  /**
   * Unsets the language for all Location Entity with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
