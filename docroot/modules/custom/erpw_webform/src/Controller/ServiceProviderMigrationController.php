<?php

namespace Drupal\erpw_webform\Controller;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements a controller for the migration form route.
 */
class ServiceProviderMigrationController extends ControllerBase {

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a ServiceProviderMigrationController object.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match service.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match')
    );
  }

  /**
   * Displays the migration form.
   *
   * @return array
   *   The form array.
   */
  public function migrationForm() {
    $form = $this->formBuilder()->getForm('Drupal\erpw_webform\Form\ServiceProviderMigrationForm');
    return $form;
  }

}
