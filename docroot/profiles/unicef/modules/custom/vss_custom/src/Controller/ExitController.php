<?php

namespace Drupal\vss_custom\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\SessionManager;
use Drupal\Core\State\State;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ExitController.
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
  public function __construct(SessionManager $session_manager, State $state, KillSwitch $page_cache_kill_switch, RequestStack $request_stack) {
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
    $this->sessionManager->destroy();
    if (isset($_SERVER['HTTP_COOKIE'])) {
      $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
      foreach ($cookies as $cookie) {
        $parts = explode('=', $cookie);
        $name = trim($parts[0]);
        setcookie($name, '', time() - 1000);
        setcookie($name, '', time() - 1000, '/');
      }
    }
    $this->pageCacheKillSwitch->trigger();
    return new TrustedRedirectResponse($exit_url);
  }

}
