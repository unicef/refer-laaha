<?php

namespace Drupal\erpw_location;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\domain\DomainNegotiatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class is used for the Cookie services.
 */
class LocationCookieService {

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $currentRequest;

  /**
   * The Domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * Cookies this service will manage.
   *
   * @var array
   */
  protected $cookie_name = [
    'zw_location_tid',
    'bn_location_tid',
    'sl_location_tid',
    'txb_location_tid',
  ];

  /**
   * New Cookie Name.
   *
   * @var string
   */
  protected $newCookieName;

  /**
   * The cookie value that will be set during the respond event.
   *
   * @var mixed
   */
  protected $cookieValue;

  /**
   * The cookie value saved in UserLocationForm.
   *
   * @var mixed
   */
  protected $savedCookie;

  /**
   * Whether or not the cookie should be updated during the response.
   *
   * @var bool
   */
  protected $shouldUpdate = FALSE;

  /**
   * The Changed hostname found.
   *
   * @var mixed
   */
  protected $newUrl;

  /**
   * Language Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * LocationCookiesService constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\domain\DomainNegotiatorInterface $domain_negotiator
   *   Domain negotiator service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service.
   */
  public function __construct(
    RequestStack $requestStack,
    DomainNegotiatorInterface $domain_negotiator,
    LanguageManagerInterface $language_manager,
    ConfigFactoryInterface $config_factory
  ) {
    $this->currentRequest = $requestStack;
    $this->domainNegotiator = $domain_negotiator;
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * Get the location cookie value.
   *
   * @return mixed
   *   Returns the cookie value set in browser.
   */
  public function getCookieValue() {
    if (empty($this->cookieValue)) {
      $cookie_value = $this->currentRequest->getCurrentRequest()->cookies->get($this->getCookieName());
    }
    else {
      $cookie_value = $this->cookieValue;
    }
    return $cookie_value;
  }

  /**
   * Get Cookie name according to active domain.
   *
   * @param mixed $domain_id
   *   Optional parameter, in case domain id is passed.
   *
   * @return string
   *   Returns the name of the cookie based on domain.
   */
  public function getCookieName($domain_id = NULL) {
    if (func_num_args() > 0) {
      $domain_id = func_get_arg(0);
    }
    else {
      $domain = $this->domainNegotiator->getActiveDomain();
      $domain_id = $domain->id();
    }
    $parts = explode('_', $domain_id);
    $subdomain = $parts[0];
    if (!empty($subdomain)) {
      switch ($subdomain) {
        case 'bn':{
          $cookie_name = 'bn_location_tid';
          break;
        }
        case 'zw':{
          $cookie_name = 'zw_location_tid';
          break;
        }
        case 'sl':{
          $cookie_name = 'sl_location_tid';
          break;
        }
        case 'txb':{
          $cookie_name = 'txb_location_tid';
          break;
        }
        default:{
          $cookie_name = 'zw_location_tid';
          break;
        }
      }
    }
    return $cookie_name;
  }

  /**
   * Sets the location cookie.
   *
   * @param string $cookie_name
   *   Name of the cookie being set.
   * @param mixed $cookie_value
   *   Value of the cookie being set.
   */
  public function setCookieValue($cookie_name, $cookie_value) {
    $this->cookieValue = $cookie_value;
    $domain = $this->domainNegotiator->getActiveDomain();
    $url = preg_replace('/^[^.]+\./', '', $domain->getHostname());
    if ($cookie_name != $this->getCookieName()) {
      // This means that the cookie doesn't correspond to the domain.
      $this->shouldUpdate = TRUE;
      $this->savedCookie = $cookie_value;
      $this->newCookieName = $cookie_name;
      $url = $this->newUrl;
    }
    setcookie($cookie_name, $cookie_value, strtotime('+7 days'), '/', $url, TRUE, FALSE);
  }

  /**
   * Get taxonomy id of the countries based on domain.
   *
   * @return string
   *   Returns the country tid of the active domain.
   */
  public function getCountryId() {
    $domain = $this->domainNegotiator->getActiveDomain();
    $domain_id = $domain->id();
    $config = \Drupal::config('domain.location.' . $domain_id);
    $domain_tid = $config->get('location');
    return $domain_tid;
  }

  /**
   * Change cookie name according to location selected.
   *
   * @param mixed $country_tid
   *   Taxonomy ID of the country the location belongs to.
   *
   * @return string
   *   Returns updated cookie name
   */
  public function changeCookieName($country_tid) {
    $new_hostname = erpw_location_country_domain_from_tid($country_tid);
    $storage = \Drupal::entityTypeManager()->getStorage('domain')->loadByProperties(['hostname' => $new_hostname]);
    $domain_id = NULL;
    foreach ($storage as $id => $domain) {
      $domain_id = $id;
      break;
    }
    $this->newUrl = preg_replace('/^[^.]+\./', '', $new_hostname);
    return $this->getCookieName($domain_id);
  }

  /**
   * Sets Default location cookie according to active domain.
   */
  public function setDefaultCookieValue() {
    $domain = $this->domainNegotiator->getActiveDomain();
    $domain_id = $domain->id();
    $config = \Drupal::config('domain.location.' . $domain_id);
    $domain_tid = $config->get('location');

    $parts = explode('_', $domain_id);
    $subdomain = $parts[0];
    if (!empty($subdomain)) {
      switch ($subdomain) {
        case 'bn':{
          $new_cookie_value = $this->currentRequest->getCurrentRequest()->cookies->has('bn_location_tid') ?
          $this->currentRequest->getCurrentRequest()->cookies->get('bn_location_tid') : $domain_tid;
          break;
        }
        case 'zw':{
          $new_cookie_value = $this->currentRequest->getCurrentRequest()->cookies->has('zw_location_tid') ?
          $this->currentRequest->getCurrentRequest()->cookies->get('zw_location_tid') : $domain_tid;
          break;
        }
        case 'sl':{
          $new_cookie_value = $this->currentRequest->getCurrentRequest()->cookies->has('sl_location_tid') ?
          $this->currentRequest->getCurrentRequest()->cookies->get('sl_location_tid') : $domain_tid;
          break;
        }
        case 'txb':{
          $new_cookie_value = $this->currentRequest->getCurrentRequest()->cookies->has('txb_location_tid') ?
          $this->currentRequest->getCurrentRequest()->cookies->get('txb_location_tid') : $domain_tid;
          break;
        }
        default:{
          $new_cookie_value = $this->currentRequest->getCurrentRequest()->cookies->has('zw_location_tid') ?
          $this->currentRequest->getCurrentRequest()->cookies->get('zw_location_tid') : $domain_tid;
          break;
        }
      }
    }
    $cookie_name = $this->getCookieName();
    $this->setCookieValue($cookie_name, $new_cookie_value);
  }

  /**
   * Get the cookie value saved in UserLocationForm.
   *
   * @return mixed
   *   Return mixed value.
   */
  public function getSavedCookie() {
    return $this->savedCookie;
  }

  /**
   * Get whether or not the cookie should be updated during the response.
   *
   * @return bool
   *   Return boolean value.
   */
  public function getShouldUpdate() {
    return $this->shouldUpdate;
  }

  /**
   * Set whether or not the cookie should be updated during the response.
   *
   * @param bool $value
   *   Boolean value.
   */
  public function setShouldUpdate($value) {
    $this->shouldUpdate = $value;
  }

  /**
   * Get new Cookie Name.
   *
   * @return string
   *   Returns string value.
   */
  public function getNewCookieName() {
    return $this->newCookieName;
  }

  /**
   * When domain changes, if the current language is not supported by the new domain, set the language cookie to the first supported.
   */
  public function updateLanguageCookie() {
    $langs_for_this_domain = $this->configFactory->get('domain.language.' . $this->domainNegotiator->getActiveId() . '.language.negotiation')->get('languages');
    $currentLang = $this->languageManager->getCurrentLanguage()->getId();
    if (!in_array($currentLang, $langs_for_this_domain)) {
      $this->currentRequest->getCurrentRequest()->cookies->set('userLanguage', $langs_for_this_domain[0]);
      $response = new RedirectResponse(preg_replace('/\/[^\/]+\//', '/en/', $this->currentRequest->getCurrentRequest()->getRequestUri()));
      $response->send();
    }
  }

}
