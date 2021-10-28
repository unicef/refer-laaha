<?php

namespace Drupal\vss_custom\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Routing\TrustedRedirectResponse;

/**
 * Class SellerNpsController.
 */
class ExitController extends ControllerBase {
  public function clear_all_data() {
    $exit_url = \Drupal::state()->get('exit_url');
    \Drupal::request()->getSession()->clear();
    unset($_COOKIE);
    \Drupal::service('page_cache_kill_switch')->trigger();
    return new TrustedRedirectResponse($exit_url);
  }
}