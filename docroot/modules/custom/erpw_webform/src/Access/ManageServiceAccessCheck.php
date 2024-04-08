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
    $user_role = $account->getRoles();
    if ($account->isAuthenticated() && !in_array('txb_service_viewer', $user_role)) {
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
