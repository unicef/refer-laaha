<?php

namespace Drupal\erpw_user_workflow_history;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the User workflow history entity entity.
 *
 * @see \Drupal\erpw_user_workflow_history\Entity\UserWorkflowHistoryEntity.
 */
class UserWorkflowHistoryEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\erpw_user_workflow_history\Entity\UserWorkflowHistoryEntityInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished user workflow history entity entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published user workflow history entity entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit user workflow history entity entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete user workflow history entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add user workflow history entity entities');
  }


}
