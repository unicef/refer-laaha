<?php

namespace Drupal\erpw_custom\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;

/**
 * Class SignUpForm1.
 */
class SignUpForm1 extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sign_up_message_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div><p>Your registration has been sent for review.</p><span>You will be notified via email, once your registration is approved</span></div>',
    ];

    $form['ok'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('OK'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('erpw_custom.sign_up_form1');
  }

}
