<?php

namespace Drupal\erpw_location;


/**
 * Class is used for the Cookie services.
 */
class LocationCookieService {

  /**
   * Name of the cookie this service will manage.
   *
   * @var string
   */
  protected $cookie_name = 'location_tid';

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
	 * Get location cookie name.
	 * 
	 * @return string
	 */
	public function getCookieName() {
		return $this->cookie_name;
	}

	/**
	 * Get the location cookie value.
	 * 
	 * @return string
	 */
	public function getCookieValue() {
		return $this->cookie_value;
	}
	
	/**
	 * Get the location cookie value.
	 * 
	 * @param string $cookie_value
	 * @return self
	 */
	public function setCookieValue($cookie_value): self {
		$this->should_update_cookie = TRUE;
		$this->cookie_value = $cookie_value;
		return $this;
	}

	/**
	 * Get whether or not the cookie should be updated during the response.
	 * 
	 * @return bool
	 */
	public function getShouldUpdateCookie() {
		return $this->should_update_cookie;
	}

	/* @todo if $should_update_cookie = TRUE, 
	then delete the old value from response headers.
	And update the new value to them.
	*/

}
