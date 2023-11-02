<?php

namespace Drupal\erpw_in_app_notification;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Notification processed entity entity.
 *
 * @see \Drupal\erpw_in_app_notification\Entity\NotificationProcessedEntity.
 */
class NotificationProcessedEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\erpw_in_app_notification\Entity\NotificationProcessedEntityInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished notification processed entity entities');
        }

        return AccessResult::allowedIfHasPermission($account, 'view published notification processed entity entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit notification processed entity entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete notification processed entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add notification processed entity entities');
  }

}
