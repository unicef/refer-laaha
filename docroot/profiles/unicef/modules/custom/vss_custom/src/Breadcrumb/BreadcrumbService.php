<?php

namespace Drupal\vss_custom\Breadcrumb;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\AdminContext;

/**
 * Class BreadcrumbService to modify breadcrumbs.
 */
class BreadcrumbService implements BreadcrumbBuilderInterface {

  use BreadcrumbHelperTrait;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Drupal\Core\Routing\AdminContext definition.
   *
   * @var \Drupal\Core\Routing\AdminContext
   */
  protected $routeAdminContext;

  /**
   * Constructs a new BreadcrumbService object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RequestStack $request_stack, AdminContext $routeAdminContext) {
    $this->entityTypeManager = $entity_type_manager;
    $this->requestStack = $request_stack;
    $this->routeAdminContext = $routeAdminContext;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $types = [
      'article',
      'video',
      'podcast',
    ];
    $route = $route_match->getRouteObject();
    if ($this->routeAdminContext->isAdminRoute($route)) {
      return FALSE;
    }
    // Determine if the current page is a node page.
    $node = $route_match->getParameter('node');
    $tax_term = $route_match->getParameter('taxonomy_term');
    if ($tax_term) {
      return TRUE;
    }
    if ($node) {
      if (in_array($node->bundle(), $types)) {
        // You can do additional checks here for the node type, etc...
        return TRUE;
      }
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    // Define a new object of type Breadcrumb.
    $breadcrumb = new Breadcrumb();

    $node = $route_match->getParameter('node');
    $tax_term = $route_match->getParameter('taxonomy_term');
    // // Add a link to the homepage as our first crumb.
    $breadcrumb->addLink(Link::createFromRoute('Home', '<front>'));
    if ($tax_term) {
      $path = $tax_term->toUrl()->getRouteName();
      $tax_parent = $this->entityTypeManager->getStorage('taxonomy_term')->load($tax_term->parent->target_id);
      if ($tax_parent) {
        $parent_path = $tax_parent->toUrl()->getRouteName();
        $parent_link = $this->generateBreadcrumbLink($tax_parent, 'taxonomy_term', $parent_path, TRUE);
        $breadcrumb->addLink($parent_link);

      }
    }
    // Special handling based on node type aka bundle.
    // NOTE use of the Link class.
    if ($node) {
      switch ($node->bundle()) {

        case 'article':
        case 'video':
        case 'podcast':
          if ($node->hasField('field_sub_category') && !empty($node->get('field_sub_category')->first())) {
            $term_id = $node->field_sub_category->target_id;
            $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($term_id);
            $path = $term->toUrl()->getRouteName();
            if ($term) {
              $parent = $this->entityTypeManager->getStorage('taxonomy_term')->load($term->parent->target_id);
              if ($parent) {
                $parent_path = $parent->toUrl()->getRouteName();
                $parent_link = $this->generateBreadcrumbLink($parent, 'taxonomy_term', $parent_path, TRUE);
                $breadcrumb->addLink($parent_link);
              }
              $link = $this->generateBreadcrumbLink($term, 'taxonomy_term', $path, FALSE);
              $breadcrumb->addLink($link);
            }
          }

          break;
      }
    }
    // $breadcrumb->addLink(Link::createFromRoute($title, ''));
    // Don't forget to add cache control by a route.
    // Otherwise all pages will have the same breadcrumb.
    $breadcrumb->addCacheContexts(['route']);
    $breadcrumb->addCacheContexts(['url']);
    // Return object of type breadcrumb.
    return $breadcrumb;
  }

}
