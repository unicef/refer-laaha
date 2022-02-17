<?php

namespace Drupal\vss_custom\Controller;

use Drupal\Core\State\State;
use Drupal\Core\Session\SessionManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 * Class ExitController.
 *
 * This class handles all exist button functionality.
 */
class ExitController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * {@inheritdoc}
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  protected $sessionManager;

  /**
   * {@inheritdoc}
   */
  protected $pageCacheKillSwitch;

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * ExitController constructor.
   */
  public function __construct(
    SessionManager $session_manager,
    State $state,
    KillSwitch $page_cache_kill_switch,
    RequestStack $request_stack) {

    $this->sessionManager = $session_manager;
    $this->state = $state;
    $this->pageCacheKillSwitch = $page_cache_kill_switch;
    $this->requestStack = $request_stack->getCurrentRequest();
  }

  /**
   * Clear data function.
   */
  public static function create(ContainerInterface $container) {
    return new static(
    $container->get('session_manager'),
    $container->get('state'),
    $container->get('page_cache_kill_switch'),
    $container->get('request_stack'),
    );
  }

  /**
   * Clear data function.
   */
  public function clearAllData() {
    $exit_url = $this->state->get('exit_url');
    if ($this->sessionManager->isStarted()) {
      $this->sessionManager->destroy();
    }

    $cookies = $this->requestStack->cookies->all();
    foreach ($cookies as $name => $cookie) {
      setcookie($name, '', time() - 1000);
      setcookie($name, '', time() - 1000, '/');
      if ($name == 'country-location-selector') {
        $domain_current_url = explode(".", $this->requestStack->server->get('SERVER_NAME'));
        $domain_slice = array_slice($domain_current_url, -2);
        $domain_site = '.' . $domain_slice[0] . '.' . $domain_slice[1];
        setcookie($name, '', time() - 100, "/", $domain_site);
      }
    }
    $this->pageCacheKillSwitch->trigger();
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Content-Type: application/xml; charset=utf-8");
    return new TrustedRedirectResponse($exit_url);
  }

}
