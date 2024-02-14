<?php

namespace Drupal\erpw_webform\CacheContext;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;

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
   * Constructs a new UserCacheContextBase class.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The current user.
   */
  public function __construct(AccountInterface $user) {
    $this->currentUser = $user;
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
    $activeDomain = \Drupal::service('domain.negotiator')->getActiveDomain()->id();
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $current_user = User::load($this->currentUser->id());
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
    $cookie_tid = \Drupal::service('erpw_location.location_cookie')->getCookieValue();
    // Add a default cookie value in case there is no location cookie set.
    if (!$cookie_tid) {
      $cookie_tid = \Drupal::service('erpw_location.location_cookie')->getDefaultDomainCookieValue();
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
