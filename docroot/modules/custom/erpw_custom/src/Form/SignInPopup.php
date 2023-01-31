<?php

namespace Drupal\erpw_custom\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\erpw_custom\Services\ErpwCustomService;

/**
 * Class to handle SignInPopup functionality.
 */
class SignInPopup extends FormBase {

  /**
   * The custom service.
   *
   * @var \Drupal\erpw_custom\Services\ErpwCustomService
   */
  protected $erpwCustomService;

  /**
   * {@inheritdoc}
   */
  public function __construct(ErpwCustomService $erpw_custom_service) {
    $this->erpwCustomService = $erpw_custom_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('erpw_custom.custom_service')
    );
  }

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
      '#prefix' => '<div class="welcome-message"><span class="close-popup"></span><div class="review-msg">',
      '#markup' => $this->t('Welcome!'),
      '#suffix' => '</div>',
    ];
    $form['welcome_message'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="popup-content">',
      '#suffix' => '</div>',
      '#markup' => $this->t(
        'This app allows GBV service providers to update the RPW with correct information on the services they provide.
        If you are a GBV SP, Click Sign Up to Register and then Sign In. If you are not a GBV service provider, select Skip.'
      ),
    ];
    $form['actions']['run_reco'] = [
      '#type' => 'submit',
      '#value' => $this->t('SKIP'),
      '#attributes' => [
        'class' => [
          'button-border',
          'skip',
        ],
      ],
    ];

    $form['actions']['export_csv'] = [
      '#type' => 'submit',
      '#value' => $this->t('SIGN IN'),
      '#attributes' => [
        'class' => [
          'sign-in',
        ],
      ],
      '#suffix' => '</div>',
    ];

    if (!empty($this->erpwCustomService->getUserLanguage())) {
      return $form;
    }
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
