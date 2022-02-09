<?php

namespace Drupal\vss_custom\EventSubscriber;

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
    $response->headers->set("X-Content-Type-Options", "nosniff always");
    $response->headers->set("Strict-Transport-Security", "max-age=31536000; includeSubDomains always");
    $response->headers->set("Access-Control-Allow-Origin", $event->getRequest()->getHost());

    $csp = "default-src 'self' blob:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.google-analytics.com https://cdnjs.cloudflare.com https://ajax.googleapis.com https://code.jquery.com https://cdn.jsdelivr.net https://www.youtube.com https://www.vimeo.com; img-src 'self' data: https://www.google-analytics.com https://h5p.org https://www.gstatic.com https://www.youtube.com https://www.vimeo.com  https://i.ytimg.com; style-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com https://translate.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://www.youtube.com https://www.vimeo.com https://i.ytimg.com; font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://www.youtube.com https://www.vimeo.com https://i.ytimg.com; frame-src 'self' https://www.youtube.com https://www.vimeo.com; connect-src 'self' https://www.google-analytics.com https://metrics.articulate.com; worker-src 'self' blob:;";
    $response->headers->set("Content-Security-Policy", $csp);
  }

  /**
   * Get subscribed events.
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onRespond'];

    return $events;
  }

}
