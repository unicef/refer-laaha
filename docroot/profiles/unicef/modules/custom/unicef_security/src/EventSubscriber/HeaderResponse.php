<?php

namespace Drupal\unicef_security\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Add custom headers.
 */
class HeaderResponse implements EventSubscriberInterface {

  /**
   * Modify headers.
   */
  public function onRespond(ResponseEvent $event) {
    $response = $event->getResponse();
    $response->headers->remove('x-generator');
    $response->headers->remove('Generator');

    $response->headers->set("X-XSS-Protection", "1; mode=block");
    $response->headers->set("cache-control", "no-cache, no-store, must-revalidate");
    $response->headers->set("X-Content-Type-Options", "nosniff always");
    $response->headers->set("Strict-Transport-Security", "max-age=31536000; includeSubDomains always");
    $response->headers->set("Access-Control-Allow-Origin", $event->getRequest()->getHost());
  }

  /**
   * Get subscribed events.
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onRespond'];

    return $events;
  }

}
