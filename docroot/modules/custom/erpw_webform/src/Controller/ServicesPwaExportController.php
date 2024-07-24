<?php

namespace Drupal\erpw_webform\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\domain\DomainNegotiatorInterface;
use Drupal\erpw_location\LocationCookieService;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for custom PWA export.
 */
class ServicesPwaExportController extends ControllerBase {

  /**
   * Add a private property to store the EntityTypeManager.
   */
  protected $entityTypeManager;

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The location service.
   *
   * @var \Drupal\erpw_location\LocationCookieService
   */
  protected $locationCookie;

  /**
   * The Domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * Constructs a new ServiceWebforms object.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, AccountInterface $currentUser, LocationCookieService $location_cookie, DomainNegotiatorInterface $domain_negotiator) {
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
    $this->locationCookie = $location_cookie;
    $this->domainNegotiator = $domain_negotiator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('erpw_location.location_cookie'),
      $container->get('domain.negotiator')
    );
  }

  /**
   * Download CSV file containing entities and their fields/values.
   *
   * @param string $node
   *   The value of the contextual filter.
   *
   *   The CSV file download response.
   */
  public function getServiceProviderList($node) {

    $activeDomain = $this->domainNegotiator->getActiveDomain()->id();
    $language = $this->languageManager->getCurrentLanguage()->getId();
    $current_user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    $roles = $current_user->getRoles();
    // Short roles.
    $shortrolestr = '';
    foreach ($roles as $role) {
      $words = explode('_', $role);
      foreach ($words as $word) {
        $shortrolestr .= substr($word, 0, 1);
      }
    }
    $user_org_id = $current_user->get('field_organisation')->getValue()[0]['target_id'] ?? '';

    // Filter out rows which do not belong to the current location.
    $cookie_tid = $this->locationCookie->getCookieValue();
    // Add a default cookie value in case there is no location cookie set.
    if (!$cookie_tid) {
      $cookie_tid = $this->locationCookie->getDefaultDomainCookieValue();
    }

    if ($this->currentUser->isAuthenticated()) {
      if (in_array('service_provider_staff', $roles) || in_array('service_provider_focal_point', $roles)) {
        $cacheId = $activeDomain . $language . $node . $shortrolestr . $user_org_id . $cookie_tid;
      }
      elseif (in_array('gbv_focal_point', $roles) || in_array('interagency_gbv_coordinator', $roles) || in_array('country_admin', $roles)) {
        $cacheId = $activeDomain . $language . $node . $shortrolestr . $cookie_tid;
      }
      else {
        $cacheId = $activeDomain . $language . $node . $shortrolestr;
      }
    }
    else {
      $cacheId = $activeDomain . '_' . $language . '_' . $node . '_' . $shortrolestr;
    }

    $jsondecode = [];
    if ($cache = $this->cache()->get($cacheId)) {
      $jsondecode = $cache->data;
    }
    else {
      // Fetch the entities from your Views listing using the contextual filter value.
      $view_id = 'service_based_service_providers_listing';
      $view = Views::getView($view_id);
      $view->setArguments([$node]);
      $view->setDisplay('rest_export_1');
      $view->execute();
      // Check if the result is empty.
      if (!empty($view->result)) {
        $jsondecode = Json::decode($view->render()['#markup']);
        $this->cache()->set($cacheId, $jsondecode, Cache::PERMANENT);
      }
    }

    return new JsonResponse($jsondecode);
  }

}
