<?php

/**
 * @file
 * Contains user_workflow_history_entity.page.inc.
 *
 * Page callback for User workflow history entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for User workflow history entity templates.
 *
 * Default template: user_workflow_history_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_user_workflow_history_entity(array &$variables) {
  // Fetch UserWorkflowHistoryEntity Entity Object.
  $user_workflow_history_entity = $variables['elements']['#user_workflow_history_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
