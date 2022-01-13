<?php

namespace Drupal\erpw_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * ModalForm class.
 */
class AddLocationPopup extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'add_location_modal_popup';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $url = Url::fromRoute('erpw_custom.dashboard');
    $external_link = Link::fromTextAndUrl('BACK TO DASHBOARD', $url)->toString();
    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<h2>Location addded</h2><div>'.$this->t('The location has been added successfully. You can now access it in the application').'</div>',
    ];
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
