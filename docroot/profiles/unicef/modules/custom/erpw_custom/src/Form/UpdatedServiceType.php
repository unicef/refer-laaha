<?php

namespace Drupal\erpw_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * ModalForm class.
 */
class UpdatedServiceType extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'updated_service_type';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
    $form['modal_description_1'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="review-msg">',
      '#markup' => $this->t('Update successful'),
      '#suffix' => '</div>',
    ];
    $form['modal_description_2'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="email-notify">',
      '#markup' => $this->t('The details has been sucessfully updated.'),
      '#suffix' => '</div>',
    ];
    $url = Url::fromRoute('view.manage_service_types.page_1')->toString();
    $external_link = t("<a href='$url' class='btn btn-primary ok-btn'>BACK TO DASHBOARD</a>");
    $form['cancel'] = [
      '#type' => 'markup',
      '#markup' => $external_link,
    ];
    $form['#attached']['library'][] = 'erpw_custom/erpw_js';
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
  }
}
