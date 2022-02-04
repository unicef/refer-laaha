<?php

namespace Drupal\vss_custom\Theme;

use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Defines a service that is use for taxonomy theme switch.
 */
class ExampleNegotiator implements ThemeNegotiatorInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    // Use this theme on a certain route.
    $routes = [
      'entity.taxonomy_term.canonical', 'vss_custom.country_selector', 'view.need_help_view.page_1',
    ];
    if (in_array($route_match->getRouteName(), $routes)) {
      return $route_match->getRouteName();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    if ($_SERVER['QUERY_STRING'] == 'amp') {
      $route_match->getRouteObject()->setOption('_no_big_pipe', TRUE);
      return 'vss_amp';
    }
  }

}
