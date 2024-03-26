<?php

namespace Drupal\erpw_webform\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Checks access for custom route.
 */
class ManageServiceAccessCheck implements AccessInterface {

  /**
   * {@inheritdoc}
   */
  public function access(RouteMatchInterface $route_match, AccountInterface $account) {
    $flag = FALSE;
    if ($account->isAuthenticated()) {
      $flag = TRUE;
    }
    if ($flag) {
      return AccessResult::allowed();
    }
    else {
      return AccessResult::forbidden();
    }
  }

}
