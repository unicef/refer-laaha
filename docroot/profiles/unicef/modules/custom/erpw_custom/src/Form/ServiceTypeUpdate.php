<?php

namespace Drupal\erpw_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * ModalForm class.
 */
class ServiceTypeUpdate extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'service_type_update';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['modal_description_1'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="review-msg">',
      '#markup' => $this->t('Are you sure you want to update the details?'),
      '#suffix' => '</div>',
    ];
    $form['modal_description_2'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="email-notify">',
      '#markup' => $this->t('Click on proceed to update or cancel to go back.'),
      '#suffix' => '</div>',
    ];
    $url = Url::fromRoute('erpw_custom.updated_service_type')->toString();
    $external_link = t("<a href='$url' class='use-ajax button bg-green' data-dialog-type='modal' data-dialog-options='{&quot;width&quot;:400}'>PROCEED</a>");
    $form['proceed'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="email-notify">',
      '#markup' => $external_link,
      '#suffix' => '</div>',
    ];
    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('CANCEL'),
      '#attributes' => [
        'class' => [
          'button',
          'bg-green',
        ],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $url = Url::fromRoute(\Drupal::service('path.current')->getPath());
    $response = new RedirectResponse($url->toString());
    $response->send();
    return $response;
  }

}
