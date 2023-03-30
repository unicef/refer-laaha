<?php

namespace Drupal\erpw_location;

use Drupal\domain\DomainNegotiatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class LocationCookie.
 *
 * Store the location cookie while requesting the site.
 */
class LocationCookie implements EventSubscriberInterface {

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $request;

  /**
   * Name of the cookie this service will manage.
   *
   * @var string
   */
  protected $cookieName = 'location_id';

  /**
   * The cookie value that will be set during the respond event.
   *
   * @var mixed
   */
  protected $newCookieValue;

  /**
   * Whether or not the cookie should be updated during the response.
   *
   * @var bool
   */
  protected $shouldUpdateCookie = FALSE;

  /**
   * Whether or not the cookie should be deleted during the response.
   *
   * @var bool
   */
  protected $shouldDeleteCookie = FALSE;

  /**
   * The Domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * LocationCookie constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack service.
   * 
   * @param \Drupal\domain\DomainNegotiatorInterface $negotiator
   *   Domain negotiator service.
   */
  public function __construct(
    RequestStack $request_stack, 
    DomainNegotiatorInterface $domain_negotiator) {
    $this->request = $request_stack->getCurrentRequest();
    $this->domainNegotiator = $domain_negotiator;
  }

  /**
   * Get the cookie's value.
   *
   * @return mixed
   *   Cookie value.
   */
  public function getCookieValue() {
    // If we're mid-request and setting a new cookie value, return that new
    // value. This allows other parts of the system access to the most recent
    // cookie value.
    if (!empty($this->newCookieValue)) {
      return $this->newCookieValue;
    }

    return $this->request->cookies->get($this->getCookieName());
  }

  /**
   * Set the cookie's new value.
   *
   * @param mixed $value
   *   The cookie Value.
   */
  public function setCookieValue($value) {
    $this->shouldUpdateCookie = TRUE;
    $this->newCookieValue = $value;
  }

  /**
   * Whether or not the cookie should be updated during the response.
   *
   * @return bool
   *   Return the response of cookie update.
   */
  public function getShouldUpdateCookie() {
    return $this->shouldUpdateCookie;
  }

  /**
   * Whether or not the cookie should be deleted during the response.
   *
   * @return bool
   *   Return the response of cookie delete.
   */
  public function getShouldDeleteCookie() {
    return $this->shouldDeleteCookie;
  }

  /**
   * Set whether or not the cookie should be deleted during the response.
   *
   * @param bool $delete_cookie
   *   Whether or not to delete the cookie during the response.
   */
  public function setShouldDeleteCookie($delete_cookie = TRUE) {
    $this->shouldDeleteCookie = (bool) $delete_cookie;
  }

  /**
   * Get this cookie's name.
   *
   * @return string
   *   Return the cookie name.
   */
  public function getCookieName() {
    return $this->cookieName;
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::RESPONSE => 'onResponse',
    ];
  }

  /**
   * React to the symfony kernel response event by managing visitor cookies.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event to process.
   */
  public function onResponse(ResponseEvent $event) {
    $response = $event->getResponse();
    $domain = $this->domainNegotiator->getActiveDomain();
    $full_url = $domain->get('hostname');
    if ($this->getShouldUpdateCookie()) {
      $my_new_cookie = new Cookie($this->getCookieName(), $this->getCookieValue(), strtotime('+7 days'), '/', $full_url, NULL, FALSE);
      $response->headers->setCookie($my_new_cookie);
    }
    else {
      // Case where sub - domain is changed from URL
      $config = \Drupal::config('domain.location.' . $domain->get('id'));
      $domain_tid = $config->get('location');

      //Check if the current location cookie tid value matches with the domain tid value.
      if (isset($_COOKIE['location_tid'])) {
        if($_COOKIE['location_tid'] != $domain_tid){
          setcookie('location_tid', $domain_tid, strtotime('+1 year'), '/', $full_url, FALSE);
          $url = Url::fromRoute('view.referral_pathway_on_homepage.page_1', [], ['query' => ['location' => $domain_tid]]);
          $url->setAbsolute();
          $response = new RedirectResponse($url->toString());
          $event->setResponse($response);
        }
      }
    }
    // The "should delete" needs to happen after "should update", or we could
    // find ourselves in a situation where we are unable to delete the cookie
    // because another part of the system is trying to update its value.
    if ($this->getShouldDeleteCookie()) {
      $response->headers->clearCookie($this->getCookieName());
    }
  }

}
