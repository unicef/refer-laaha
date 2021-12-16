<?php

namespace Drupal\erpw_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class SignInPopup.
 */
class SignInPopup extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sign_in_popup';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['wel_come'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="welcome-message">',
      '#suffix' => '</div></div>',
      '#markup' => $this->t('Welcome!'),
    ];
    $form['welcome_message'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="welcome-content">',
      '#suffix' => '</div></div>',
      '#markup' => $this->t('If you havent registered or signed in, you can either click on sign in below or go to menu to sign in or register later'),
    ];
    $form['actions']['run_reco'] = [
      '#type' => 'submit',
      '#value' => $this->t('SKIP'),
    ];

    $form['actions']['export_csv'] = [
      '#type' => 'submit',
      '#value' => $this->t('SIGN IN'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $op = $form_state->getValue("op");
    if ($op == 'SKIP') {
      $url = Url::fromRoute('<front>')->toString();
      return new RedirectResponse($url);
    }
    else {
      $response = new RedirectResponse('/user/login', 301);
      $response->send();
    }
  }

}
