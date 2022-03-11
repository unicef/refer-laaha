<?php

namespace Drupal\visitors\EventSubscriber;

use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Store visitors data when a request terminates.
 */
class KernelTerminateSubscriber implements EventSubscriberInterface {
  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Store visitors data when a request terminates.
   *
   * @param Symfony\Component\HttpKernel\Event\PostResponseEvent $event
   *   The Event to process.
   */
  public function onTerminate(PostResponseEvent $event) {
    $this->request = $event->getRequest();

    $user = \Drupal::currentUser();
    $visitors_uid = isset($user) ? $user->id() : '';
    $lang = \Drupal::languageManager()->getCurrentLanguage()->getId();
    if ($user->isAnonymous() && (
      Url::fromRoute('<current>')->toString() == '/' ||
      Url::fromRoute('<current>')->toString() == '/node' ||
      Url::fromRoute('<current>')->toString() == "/$lang/node"
    )) {
      $title = 'Home';
    }

    if ($user->isAnonymous() && ($this->getTitle() || $title) && (
      strpos(Url::fromRoute('<current>')->toString(), 'user') == '' &&
      strpos(Url::fromRoute('<current>')->toString(), 'modal') == '' &&
      strpos(Url::fromRoute('<current>')->toString(), 'autocomplete') == ''  &&
      strpos(Url::fromRoute('<current>')->toString(), 'nodeviewcount') == '' &&
      strpos(Url::fromRoute('<current>')->toString(), 'profiles') == ''
    )) {
      $ip_str = $this->getIpStr();
      if (Url::fromRoute('<current>')->toString() == '/') {
        $title = 'Home';
      }
      if (Url::fromRoute('<current>')->toString() == '/search-form') {
        $title = 'Search result page';
      }

      $fields = [
        'visitors_uid'        => $visitors_uid,
        'visitors_ip'         => $ip_str,
        'visitors_date_time'  => time(),
        'visitors_url'        => $this->getUrl(),
        'visitors_referer'    => $this->getReferer(),
        'visitors_path'       => Url::fromRoute('<current>')->toString(),
        'visitors_title'      => $title ? $title : $this->getTitle(),
        'visitors_user_agent' => $this->getUserAgent(),
      ];

      try {
        \Drupal::database()->insert('visitors')
          ->fields($fields)
          ->execute();
      }
      catch (\Exception $e) {
        // Intentionally do nothing.
      }
    }
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents() {
    $events["kernel.terminate"] = ['onTerminate'];

    return $events;
  }

  /**
   * Get the title of the current page.
   */
  protected function getTitle() {
    $title = '';
    $route = \Drupal::routeMatch();
    $route_object = $route->getRouteObject();
    if (!is_null($route_object)) {
      $title = $route->getRouteObject()->getDefault("_title");
      if ($route->getRouteName() == 'entity.taxonomy_term.canonical') {
        $term_id = $route->getRawParameter('taxonomy_term');
        $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($term_id);
        $title = $term->label();
      }
      if ($route->getRouteName() == 'entity.node.canonical') {
        $nid = $route->getRawParameter('node');
        $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
        $title = $node->label();
      }
    }
    return htmlspecialchars_decode($title, ENT_QUOTES);
  }

  /**
   * Get full path request uri.
   */
  protected function getUrl() {
    return urldecode(sprintf('http://%s%s', $_SERVER['HTTP_HOST'], $this->request->getRequestUri()));
  }

  /**
   * Get the address of the page which referred the user to the current page.
   */
  protected function getReferer() {
    return isset($_SERVER['HTTP_REFERER']) ? urldecode($_SERVER['HTTP_REFERER']) : '';
  }

  /**
   * Converts a string containing an visitors (IPv4) Internet Protocol dotted.
   */
  protected function getIpStr() {
    return sprintf("%u", ip2long($this->request->getClientIp()));
  }

  /**
   * Get visitor user agent.
   *
   * @return string
   *   string user agent, or empty string if user agent does not exist
   */
  protected function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
  }

}
