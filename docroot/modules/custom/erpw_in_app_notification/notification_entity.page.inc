<?php

/**
 * @file
 * Contains notification_entity.page.inc.
 *
 * Page callback for Notification entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Notification entity templates.
 *
 * Default template: notification_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_notification_entity(array &$variables) {
  // Fetch NotificationEntity Entity Object.
  $notification_entity = $variables['elements']['#notification_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
