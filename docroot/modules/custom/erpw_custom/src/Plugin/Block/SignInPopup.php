<?php

namespace Drupal\erpw_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'SignInPopup' block.
 *
 * @Block(
 *  id = "sign_in_popup",
 *  admin_label = @Translation("Sign in popup"),
 * )
 */
class SignInPopup extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = [];

    $requestStack = \Drupal::service('request_stack')->getCurrentRequest();
    if ($requestStack->cookies->has('sign_in_popup')) {
      return $form;
    }

    $form = \Drupal::formBuilder()->getForm('\Drupal\erpw_custom\Form\SignInPopup');
    return $form;
  }

}
