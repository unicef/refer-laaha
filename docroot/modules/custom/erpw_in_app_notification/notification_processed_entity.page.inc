<?php

/**
 * @file
 * Contains notification_processed_entity.page.inc.
 *
 * Page callback for Notification processed entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Notification processed entity templates.
 *
 * Default template: notification_processed_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_notification_processed_entity(array &$variables) {
  // Fetch NotificationProcessedEntity Entity Object.
  $notification_processed_entity = $variables['elements']['#notification_processed_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
