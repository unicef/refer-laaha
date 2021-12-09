<?php

namespace Drupal\vss\Plugin\Preprocess;

use Drupal\bootstrap\Utility\Variables;
use Drupal\Core\Template\Attribute;
use Drupal\vss_custom\Breadcrumb\BreadcrumbHelperTrait;
use Drupal\bootstrap\Plugin\Preprocess\Breadcrumb as BootBreadcrumb;

/**
 * Pre-processes variables for the "breadcrumb" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("breadcrumb")
 */
class Breadcrumb extends BootBreadcrumb {

  use BreadcrumbHelperTrait;

  /**
   * {@inheritdoc}
   */
  public function preprocessVariables(Variables $variables) {
    $breadcrumb = &$variables['breadcrumb'];

    $image = '';
    if (isset($this->theme) && $this->theme->getSetting('breadcrumb_title') && !empty($breadcrumb)) {
      $request = \Drupal::request();
      $route_match = \Drupal::routeMatch();
      $page_title = \Drupal::service('title_resolver')->getTitle($request, $route_match->getRouteObject());
      // Determine if the current page is a node page.
      $node = $route_match->getParameter('node');
      $tax_term = $route_match->getParameter('taxonomy_term');
      if ($node) {
        switch ($node->bundle()) {

          case 'article':
          case 'video':
          case 'scorm':
          case 'podcast':
            $image = $this->loadThumbnailImage($node, 'node', FALSE);

            break;
        }
      }
      $parent = FALSE;
      if (isset($tax_term->parent) && !$tax_term->parent->target_id) {
        $parent = TRUE;
      }
      if ($tax_term) {
        $image = $this->loadThumbnailImage($tax_term, 'taxonomy_term', $parent);
      }
      $link_text = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $image . render($page_title),
      ];

      if (!empty($page_title)) {
        $breadcrumb[] = [
          'text' => $link_text,
          'attributes' => new Attribute(['class' => ['active']]),
        ];
      }
    }

    // Add cache context based on url.
    $variables->addCacheContexts(['url']);
  }

}
