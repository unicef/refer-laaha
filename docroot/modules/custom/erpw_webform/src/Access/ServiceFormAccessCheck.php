<?php 

namespace Drupal\erpw_webform\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\Access\AccessInterface;

/**
 * Checks access for custom route.
 */
class ServiceFormAccessCheck implements AccessInterface {

  /**
   * {@inheritdoc}
   */
  public function access(RouteMatchInterface $route_match, AccountInterface $account) {
    $required_role = ['interagency_gbv_coordinator','country_admin','super_admin','administrator'];
    $roles = $account->getRoles();
    $flag = FALSE;
    foreach ($roles as $role) {
      if (in_array($role,$required_role)) {
        $flag = TRUE;
        break;
      }
    }
    if ($flag) {
      return AccessResult::allowed();
    }
    else {
      return AccessResult::forbidden();
    }
  }

}
