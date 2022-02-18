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
    $response->headers->set("X-Frame-Options", "ALLOW-FROM " . $event->getRequest()->getHost());
    $response->headers->set("X-Frame-Options", "ALLOW-FROM *.virtualsafespace.net");
    $response->headers->set("X-Content-Type-Options", "nosniff always");
    $response->headers->set("Strict-Transport-Security", "max-age=31536000; includeSubDomains always");
    $response->headers->set("Access-Control-Allow-Origin", $event->getRequest()->getHost());

    $csp = "upgrade-insecure-requests; default-src 'self' blob:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://bam.nr-data.net https://cdn.ampproject.org https://www.google-analytics.com https://cdnjs.cloudflare.com https://ajax.googleapis.com https://code.jquery.com https://cdn.jsdelivr.net https://www.youtube.com https://www.googletagmanager.com https://js-agent.newrelic.com https://www.vimeo.com;";

    $csp .= " img-src 'self' data: https://www.googletagmanager.com https://www.google-analytics.com https://h5p.org https://www.gstatic.com https://www.youtube.com https://www.vimeo.com  https://i.ytimg.com;";

    $csp .= " style-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com *.virtualsafespace.net https://translate.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://www.youtube.com https://www.vimeo.com https://i.ytimg.com;";

    $csp .= " font-src 'self' data: https://themes.googleusercontent.com https://fonts.gstatic.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com *.virtualsafespace.net https://www.youtube.com https://www.vimeo.com https://i.ytimg.com;";

    $csp .= " frame-src 'self' http://stage.virtualsafespace.net http://stage.iq.virtualsafespace.net http://stage.ec.virtualsafespace.net http://dev.virtualsafespace.net http://dev.iq.virtualsafespace.net http://dev.ec.virtualsafespace.net https://www.youtube.com https://www.vimeo.com https://player.vimeo.com https://iq.virtualsafespace.net https://ec.virtualsafespace.net https://virtualsafespace.net *.virtualsafespace.net;";

    $csp .= " connect-src 'self' http://stage.virtualsafespace.net http://stage.iq.virtualsafespace.net http://stage.ec.virtualsafespace.net http://dev.virtualsafespace.net http://dev.iq.virtualsafespace.net http://dev.ec.virtualsafespace.net  https://iq.virtualsafespace.net https://ec.virtualsafespace.net https://virtualsafespace.net https://bam.nr-data.net https://www.google-analytics.com https://metrics.articulate.com *.virtualsafespace.net https://api.opencagedata.com; worker-src 'self' blob:;";
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
