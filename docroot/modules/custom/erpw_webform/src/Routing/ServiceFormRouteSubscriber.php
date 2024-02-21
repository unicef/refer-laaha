<?php

namespace Drupal\erpw_webform\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class ServiceFormRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('entity.webform.duplicate_form')) {
      $route->setRequirement('_custom_access', '\Drupal\erpw_webform\Access\ServiceFormAccessCheck::access');
    }
  }

}
