<?php

namespace Drupal\erpw_location;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\erpw_location\Entity\LocationEntityInterface;

/**
 * Defines the storage handler class for Location Entity entities.
 *
 * This extends the base storage class, adding required special handling for
 * Location Entity entities.
 *
 * @ingroup erpw_location
 */
class LocationEntityStorage extends SqlContentEntityStorage implements LocationEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(LocationEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {location_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {location_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(LocationEntityInterface $entity) {
    return $this->database->query(
      'SELECT COUNT(*) FROM {location_field_revision}
      WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()]
      )->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('location_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
