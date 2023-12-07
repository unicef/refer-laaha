<?php

namespace Drupal\erpw_location\EventSubscriber;

use Drupal\domain\DomainNegotiatorInterface;
use Drupal\erpw_location\LocationCookieService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class LocationCookie.
 *
 * Store the location cookie while requesting the site.
 */
class LocationCookieSubscriber implements EventSubscriberInterface {

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $request;

  /**
   * The Domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * The Location Cookie Service.
   *
   * @var \Drupal\erpw_location\LocationCookieService
   */
  protected $locationCookie;

  /**
   * LocationCookie constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack service.
   * @param \Drupal\domain\DomainNegotiatorInterface $domain_negotiator
   *   Domain negotiator service.
   * @param \Drupal\erpw_location\LocationCookieService $location_cookie
   *   Location cookie service.
   */
  public function __construct(
    RequestStack $request_stack,
    DomainNegotiatorInterface $domain_negotiator,
    LocationCookieService $location_cookie) {
    $this->request = $request_stack->getCurrentRequest();
    $this->domainNegotiator = $domain_negotiator;
    $this->locationCookie = $location_cookie;
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onResponse', 29];
    return $events;
  }

  /**
   * React to the symfony kernel response event by managing visitor cookies.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event to process.
   */
  public function onResponse(ResponseEvent $event) {
    if ($this->locationCookie->getShouldUpdate()) {
      $cookie_value = $this->locationCookie->getSavedCookie();
      $cookie_name = $this->locationCookie->getNewCookieName();
      $this->locationCookie->setShouldUpdate(FALSE);
      $this->locationCookie->setCookieValue($cookie_name, $cookie_value);
    }

    $this->locationCookie->updateLanguageCookie();
  }

}
