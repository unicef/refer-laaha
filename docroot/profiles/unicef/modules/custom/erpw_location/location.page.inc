<?php

/**
 * @file
 * Contains location.page.inc.
 *
 * Page callback for Location Entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Location Entity templates.
 *
 * Default template: location.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_location(array &$variables) {
  // Fetch LocationEntity Entity Object.
  $location = $variables['elements']['#location'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
