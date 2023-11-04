<?php

namespace Drupal\erpw_in_app_notification;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the Notification entity entity.
 *
 * @see \Drupal\erpw_in_app_notification\Entity\NotificationEntity.
 */
class NotificationEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\erpw_in_app_notification\Entity\NotificationEntityInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished notification entity entities');
        }

        return AccessResult::allowedIfHasPermission($account, 'view published notification entity entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit notification entity entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete notification entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add notification entity entities');
  }

}
