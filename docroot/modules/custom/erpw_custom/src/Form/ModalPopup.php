<?php

namespace Drupal\erpw_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * ModalForm class.
 */
class ModalPopup extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'modal_popup';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $url = Url::fromRoute('view.manage_service_types.page_1');
    $external_link = Link::fromTextAndUrl($this->t('Back to Service Type'), $url)->toString();
    $form['back_to_dashboard'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="use-ajax modal-popup button-red">',
      '#markup' => $external_link,
      '#suffix' => '</div>',
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
  }

}
