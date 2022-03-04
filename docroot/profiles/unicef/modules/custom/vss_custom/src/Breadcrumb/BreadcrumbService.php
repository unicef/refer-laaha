<?php

namespace Drupal\vss_custom\Breadcrumb;

use Drupal\Core\Link;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;

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
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    RequestStack $request_stack,
    AdminContext $route_admin_context) {

    $this->entityTypeManager = $entity_type_manager;
    $this->requestStack = $request_stack->getCurrentRequest();
    $this->routeAdminContext = $route_admin_context;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $types = [
      'article',
      'video',
      'scorm',
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
    $breadcrumb->addLink(Link::createFromRoute(t('Home'), '<front>'));
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
      if ($node->hasField('field_sub_category')) {
        $path = parse_url($this->requestStack->server->get('HTTP_REFERER'))['path'];
        $url_object = \Drupal::service('path.validator')->getUrlIfValid($path);
        $route_name = $url_object ? $url_object->getRouteName() : '';
        if ($route_name == 'entity.taxonomy_term.canonical') {
          $term_id = $url_object->getrouteParameters()['taxonomy_term'];
          $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($term_id);
          $subcat = $term->get('field_sub_category')->value;
          if (!$subcat) {
            foreach ($node->field_sub_category->getValue() as $value) {
              if (!empty($value['target_id'])) {
                $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($value['target_id']);
                if ($term->name->value != NULL) {
                  $values[$value['target_id']] = $term->name->value;
                }
              }
            }
            asort($values);
            $term_id = array_key_first($values);
          }
        }
        else {
          foreach ($node->field_sub_category->getValue() as $value) {
            if (!empty($value['target_id'])) {
              $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($value['target_id']);
              if ($term->name->value != NULL) {
                $values[$value['target_id']] = $term->name->value;
              }
            }
          }
          asort($values);
          $term_id = array_key_first($values);
        }
        $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($term_id);
        if ($term) {
          $path = $term->toUrl()->getRouteName();
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
