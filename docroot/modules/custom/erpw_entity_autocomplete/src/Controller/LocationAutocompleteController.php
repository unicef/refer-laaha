<?php

namespace Drupal\erpw_entity_autocomplete\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\domain\DomainNegotiatorInterface;
use Drupal\erpw_location\LocationService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a route controller for country taxonomy autocomplete form elements.
 */
class LocationAutocompleteController extends ControllerBase {

  /**
   * The taxonomy term storage.
   *
   * @var \Drupal\taxonomy\TermStorage
   */
  protected $termStorage;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Drupal\domain\DomainNegotiatorInterface definition.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * Database Connection instance.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The location service.
   *
   * @var \Drupal\erpw_location\LocationService
   */
  protected $locationService;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
  AccountInterface $current_user,
  DomainNegotiatorInterface $domain_negotiator,
  ConfigFactory $config_factory,
  LocationService $location_service) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->domainNegotiator = $domain_negotiator;
    $this->configFactory = $config_factory;
    $this->locationService = $location_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('domain.negotiator'),
      $container->get('config.factory'),
      $container->get('erpw_location.location_services')
    );
  }

  /**
   * Handler for autocomplete request.
   */
  public function handleAutocomplete(Request $request) {
    $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    $results = [];
    $input = $request->query->get('q');
    $current_user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());

    // Get the typed string from the URL, if it exists.
    if (!$input) {
      return new JsonResponse($results);
    }

    $input = Xss::filter($input);

    $query = $termStorage->getQuery()
      ->condition('vid', 'country')
      ->condition('name', $input, 'CONTAINS')
      ->sort('name', 'ASC')
      ->range(0, 10);

    $ctids = $combined_ptids = [];
    // For Anonymous users, the location should be locked to the country of the active domain.
    if ($this->currentUser->isAnonymous()) {
      $domain = $this->domainNegotiator->getActiveDomain();
      $configuration = $this->configFactory->getEditable('domain.location.' . $domain->id());
      $location = $configuration->get('location');
      $ctids = get_child_term_tid($location);
      $query->condition('tid', $ctids, 'IN');
    }
    // For all signed users except admin, they should be locked to their own location.
    elseif (($current_user->hasField('field_location') &&
    !$current_user->get('field_location')->isEmpty()) &&
    !$current_user->hasRole('administrator')) {

      $locations = $current_user->get('field_location')->getValue();
      foreach ($locations as $location) {
        $location_ids[] = $location['target_id'];
      }
      if (is_array($location_ids)) {
        foreach ($location_ids as $location_id) {
          $ctids = get_child_term_tid($location_id);
          $combined_ptids = array_merge($combined_ptids, $ctids);
        }
        $ctids = $combined_ptids;
      }
      else {
        $ctids = get_child_term_tid($location_ids);
      }
      $query->condition('tid', $ctids, 'IN');
    }

    $tids = $query->accessCheck(FALSE)->execute();
    $terms = $tids ? $termStorage->loadMultiple($tids) : [];

    foreach ($terms as $term) {
      $hierarchial_label = taxonomy_get_hierarchy_labels($term->id());
      $label = $hierarchial_label;
      $value = $term->label() . ' {' . $term->id() . '}';

      $results[] = [
        'value' => $value,
        'label' => is_array($label) ? implode(' ', $label) : $label,
      ];
    }

    return new JsonResponse($results);
  }

}
