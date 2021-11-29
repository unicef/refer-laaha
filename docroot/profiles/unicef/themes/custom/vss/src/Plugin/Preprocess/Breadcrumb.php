<?php

namespace Drupal\vss\Plugin\Preprocess;

use Drupal\bootstrap\Utility\Variables;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Drupal\bootstrap\Plugin\Preprocess\PreprocessBase;
use Drupal\bootstrap\Plugin\Preprocess\PreprocessInterface;
use Drupal\vss_custom\Breadcrumb\BreadcrumbHelperTrait;

/**
 * Pre-processes variables for the "breadcrumb" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("breadcrumb")
 */
class Breadcrumb extends PreprocessBase implements PreprocessInterface {

  use BreadcrumbHelperTrait;

  /**
   * {@inheritdoc}
   */
  public function preprocessVariables(Variables $variables) {
    $breadcrumb = &$variables['breadcrumb'];

    // Determine if breadcrumbs should be displayed.
    $breadcrumb_visibility = $this->theme->getSetting('breadcrumb');
    if (($breadcrumb_visibility == 0 || ($breadcrumb_visibility == 2 && \Drupal::service('router.admin_context')->isAdminRoute())) || empty($breadcrumb)) {
      $breadcrumb = [];
      return;
    }

    // Remove first occurrence of the "Home" <front> link, provided by core.
    if (!$this->theme->getSetting('breadcrumb_home')) {
      $front = Url::fromRoute('<front>')->toString();
      foreach ($breadcrumb as $key => $link) {
        if (isset($link['url']) && $link['url'] === $front) {
          unset($breadcrumb[$key]);
          break;
        }
      }
    }
    $image = '';
    if ($this->theme->getSetting('breadcrumb_title') && !empty($breadcrumb)) {
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
          case 'podcast':
            $image = $this->loadThumbnailImage($node, 'node', FALSE);

            break;
        }
      }
      if ($tax_term) {
        $image = $this->loadThumbnailImage($tax_term, 'taxonomy_term');
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
