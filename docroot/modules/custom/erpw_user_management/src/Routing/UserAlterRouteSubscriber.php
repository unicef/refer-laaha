<?php

namespace Drupal\erpw_user_management\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class UserAlterRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $permission = erpw_user_management_user_form_access();
    if ($permission && $route = $collection->get('entity.user.edit_form')) {
      $route->setOption('_admin_route', FALSE);
    }
    if ($permission && $route = $collection->get('entity.user.cancel_form')) {
      $route->setOption('_admin_route', FALSE);
    }

  }

}
