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
    return $route_match->getRouteName() == 'entity.taxonomy_term.canonical';
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
