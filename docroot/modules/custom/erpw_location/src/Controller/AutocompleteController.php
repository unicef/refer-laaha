<?php

namespace Drupal\erpw_location\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\erpw_location\LocationService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines a route controller for entity autocomplete form elements.
 */
class AutocompleteController extends ControllerBase {

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Drupal\erpw_location\LocationService definition.
   *
   * @var \Drupal\erpw_location\LocationService
   */
  protected $locationService;

  /**
   * AutocompleteController constructor.
   */
  public function __construct(RequestStack $request_stack, LocationService $location_service) {
    $this->requestStack = $request_stack->getCurrentRequest();
    $this->locationService = $location_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
    $container->get('request_stack'),
    $container->get('erpw_location.location_services'),
    );
  }

  /**
   * Handler for autocomplete request.
   */
  public function handleAutocomplete(Request $request, $tid) {
    $results = [];
    $q = $this->requestStack->query->get('q');
    $locations = $this->locationService->getChildrenByTid($tid, $q);
    foreach ($locations as $key => $value) {
      $results[] = [
        'value' => $value,
        'label' => $value,
        'tid' => $key,
      ];
    }
    return new JsonResponse($results);
  }

}
