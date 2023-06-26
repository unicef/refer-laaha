<?php

namespace Drupal\erpw_location;
use Drupal\domain\DomainNegotiatorInterface;
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
  protected $cookie_name = ['zw_location_tid','bn_location_tid','sl_location_tid','txb_location_tid'];

	/**
	 * New Cookie Name
	 * 
	 * @var string;
	*/
	protected $new_cookie_name;

  /**
   * The cookie value that will be set during the respond event.
   *
   * @var mixed
   */
  protected $cookie_value;

	/**
	 * The cookie value saved in UserLocationForm.
	 * 
	 * @var mixed
	*/
	protected $saved_cookie;

	/**
	 * Whether or not the cookie should be updated during the response.
	 * 
	 * @var bool
	*/
	protected $should_update = FALSE;

	/**
	 * The Changed hostname found.
	 * 
	 * @var mixed
	*/
	protected $new_url;

	/**
   * LocationCookiesService constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\domain\DomainNegotiatorInterface $negotiator
   *   Domain negotiator service.
   */
  public function __construct(RequestStack $requestStack,
	DomainNegotiatorInterface $domain_negotiator) {
    $this->currentRequest = $requestStack->getCurrentRequest();
		$this->domainNegotiator = $domain_negotiator;
  }

	/**
	 * Get the location cookie value.
	 * 
	 * @return mixed
	 *   Returns the cookie value set in browser.
	 */
	public function getCookieValue() {
		if (empty($this->cookie_value)) {
			$cookie_value = $this->currentRequest->cookies->get($this->getCookieName());
		} 
		else {
			$cookie_value = $this->cookie_value;
		}
		return $cookie_value;
	}

	/**
	 * Get Cookie name according to active domain.
	 * 
	 * @param mixed
	 *   Optional parameter, in case domain id is passed.
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
				case 'bn': {
						$cookie_name = 'bn_location_tid';
						break;
					}
				case 'zw': {
						$cookie_name = 'zw_location_tid';
						break;
					}
				case 'sl': {
						$cookie_name = 'sl_location_tid';
						break;
					}
				case 'txb': {
						$cookie_name = 'txb_location_tid';
						break;
					}
				default: {
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
		$this->cookie_value = $cookie_value;
    $domain = $this->domainNegotiator->getActiveDomain();
    $full_url = $domain->get('hostname');
		if ($cookie_name != $this->getCookieName()) {
			// This means that the cookie doesn't correspond to the domain.
			$this->should_update = TRUE;
			$this->saved_cookie = $cookie_value;
			$this->new_cookie_name = $cookie_name;
			$full_url = $this->new_url;
		}
		setcookie($cookie_name, $cookie_value, strtotime('+7 days'), '/', $full_url, NULL, FALSE);
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
	 * @param mixed
	 *   Taxonomy ID of the country the location belongs to.
	 * @return string
	 *   Returns updated cookie name
	*/
	public function changeCookieName($country_tid) {
		$new_hostname = erpw_location_country_domain_from_tid($country_tid);
		$storage = \Drupal::entityTypeManager()->getStorage('domain')->loadByProperties(['hostname' => $new_hostname]);
    $domain_id = null;
    foreach ($storage as $id => $domain) {
      $domain_id = $id;
      break;
    }
		$this->new_url = $new_hostname;
		return $this->getCookieName($domain_id);
	}

	/**
	 * Sets Default location cookie according to active domain.
	*/
	public function setDefaultCookieValue(){
		$domain = $this->domainNegotiator->getActiveDomain();
		$domain_id = $domain->id();
		$config = \Drupal::config('domain.location.' . $domain_id);
		$domain_tid = $config->get('location');

		$parts = explode('_', $domain_id);
		$subdomain = $parts[0];
		if (!empty($subdomain)) {
			switch ($subdomain) {
				case 'bn': {
						$new_cookie_value = $this->currentRequest->cookies->has('bn_location_tid') ?
							$this->currentRequest->cookies->get('bn_location_tid') : $domain_tid;
						break;
					}
				case 'zw': {
						$new_cookie_value = $this->currentRequest->cookies->has('zw_location_tid') ?
							$this->currentRequest->cookies->get('zw_location_tid') : $domain_tid;
						break;
					}
				case 'sl': {
						$new_cookie_value = $this->currentRequest->cookies->has('sl_location_tid') ?
							$this->currentRequest->cookies->get('sl_location_tid') : $domain_tid;
						break;
					}
				case 'txb': {
						$new_cookie_value = $this->currentRequest->cookies->has('txb_location_tid') ?
							$this->currentRequest->cookies->get('txb_location_tid') : $domain_tid;
						break;
					}
				default: {
						$new_cookie_value = $this->currentRequest->cookies->has('zw_location_tid') ?
							$this->currentRequest->cookies->get('zw_location_tid') : $domain_tid;
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
	 */
	public function getSavedCookie() {
		return $this->saved_cookie;
	}

	/**
	 * Get whether or not the cookie should be updated during the response.
	 * 
	 * @return bool
	 */
	public function getShouldUpdate() {
		return $this->should_update;
	}

	/**
	 * Set whether or not the cookie should be updated during the response.
	 * 
	 * @param bool
	 */
	public function setShouldUpdate($value) {
		$this->should_update = $value;
	}

	/**
	 * Get new Cookie Name
	 * 
	 * @return string
	 */
	public function getNewCookieName() {
		return $this->new_cookie_name;
	}
}
