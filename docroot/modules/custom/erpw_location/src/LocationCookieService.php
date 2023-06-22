<?php

namespace Drupal\erpw_location;
use Drupal\domain\DomainNegotiatorInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;


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
   * Whether or not the cookie should be updated during the response.
   *
   * @var bool
   */
  protected $should_update_cookie = FALSE;

  /**
   * The cookie value that will be set during the respond event.
   *
   * @var mixed
   */
  protected $cookie_value;

	/**
   * CookiesService constructor.
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
	 * @return string
	 */
	public function getCookieValue() {
		// @todo this should return whatever location cookie is present for active domain.
		return $this->cookie_value;
	}
	
	/**
	 * Set the location cookie value.
	 * 
	 * @param string $cookie_name
	 * @param mixed $cookie_value
	 */
	public function setCookieValue($cookie_name, $cookie_value) {
		$this->cookie_value = $cookie_value;
    $domain = $this->domainNegotiator->getActiveDomain();
    $full_url = $domain->get('hostname');
		$response = new Response();
    $response->headers->setCookie(new Cookie($cookie_name, $cookie_value, strtotime('+7 days'), '/', $full_url, NULL, FALSE));
		$this->currentRequest->attributes->set('_response', $response);
	}

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
						$cookie_name = 'bn_location_tid';
						break;
					}
				case 'zw': {
						$new_cookie_value = $this->currentRequest->cookies->has('zw_location_tid') ?
							$this->currentRequest->cookies->get('zw_location_tid') : $domain_tid;
						$cookie_name = 'zw_location_tid';
						break;
					}
				case 'sl': {
						$new_cookie_value = $this->currentRequest->cookies->has('sl_location_tid') ?
							$this->currentRequest->cookies->get('sl_location_tid') : $domain_tid;
						$cookie_name = 'sl_location_tid';
						break;
					}
				case 'txb': {
						$new_cookie_value = $this->currentRequest->cookies->has('txb_location_tid') ?
							$this->currentRequest->cookies->get('txb_location_tid') : $domain_tid;
						$cookie_name = 'txb_location_tid';
						break;
					}
				default: {
						$new_cookie_value = $this->currentRequest->cookies->has('zw_location_tid') ?
							$this->currentRequest->cookies->get('zw_location_tid') : $domain_tid;
						$cookie_name = 'zw_location_tid';
						break;
					}
			}
		}

		$this->setCookieValue($cookie_name, $new_cookie_value);

	}

}
