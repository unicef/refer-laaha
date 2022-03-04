<?php

namespace Drupal\erpw_entity_autocomplete\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * {@inheritdoc}
 */
class RpwAutocompleteRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('system.entity_autocomplete')) {
      $route->setDefault('_controller', '\Drupal\erpw_entity_autocomplete\Controller\RpwEntityAutocompleteController::handleAutocomplete');
    }
  }

}
