<?php

namespace Drupal\erpw_webform\CacheContext;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\domain\DomainNegotiatorInterface;
use Drupal\erpw_location\LocationCookieService;
use Drupal\language\ConfigurableLanguageManagerInterface;

/**
 * Defines the ServiceListCacheContext service, for "per user role, organization, location, service type, language and domain" caching.
 *
 * Cache context ID: 'servicelist'.
 */
class ServiceListCacheContext implements CacheContextInterface {

  /**
   * The account object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The Domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * A entityManager instance.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The cookie as a service.
   *
   * @var \Drupal\erpw_location\LocationCookieService
   */
  protected $locationCookie;


  /**
   * Drupal\language\ConfigurableLanguageManagerInterface definition.
   *
   * @var \Drupal\language\ConfigurableLanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new UserCacheContextBase class.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The current user.
   * @param \Drupal\Core\Database\Connection $domain_negotiator
   *   The domain negotiator service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityManager object.
   * @param \Drupal\erpw_location\LocationCookieService $location_cookie
   *   Location Cookie Service.
   * @param \Drupal\language\ConfigurableLanguageManagerInterface $language_manager
   *   Language Manager Service.
   */
  public function __construct(AccountInterface $user, DomainNegotiatorInterface $domain_negotiator, EntityTypeManagerInterface $entity_type_manager, LocationCookieService $location_cookie, ConfigurableLanguageManagerInterface $language_manager) {
    $this->currentUser = $user;
    $this->domainNegotiator = $domain_negotiator;
    $this->entityTypeManager = $entity_type_manager;
    $this->locationCookie = $location_cookie;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Service List');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
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
        return $activeDomain . $language . $shortrolestr . $user_org_id . $cookie_tid;
      }
      elseif (in_array('gbv_focal_point', $roles) || in_array('interagency_gbv_coordinator', $roles) || in_array('country_admin', $roles)) {
        return $activeDomain . $language . $shortrolestr . $cookie_tid;
      }
      else {
        return $activeDomain . $language . $shortrolestr;
      }
    }
    else {
      return $activeDomain . '_' . $language . '_' . '_' . $shortrolestr;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

}
