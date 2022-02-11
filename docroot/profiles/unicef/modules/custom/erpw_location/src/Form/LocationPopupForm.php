<?php

namespace Drupal\erpw_location\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * ModalForm class.
 */
class LocationPopupForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'location_popup_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
    $form['modal_description_1'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="review-msg">',
      '#markup' => $this->t('Delete successfully'),
      '#suffix' => '</div>',
    ];
    $form['modal_description_2'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="email-notify">',
      '#markup' => $this->t('The details has been successfully deleted.'),
      '#suffix' => '</div>',
    ];
    $link_options = [
      'attributes' => [
        'class' => [
          'back-to-dashboard',
          'bg-green',
        ],
      ],
    ];
    $url = Url::fromRoute('erpw_custom.dashboard');
    $url->setOptions($link_options);
    $dashboard_url = Link::fromTextAndUrl('BACK TO DASHBOARD', $url)->toString();
    $form['back_to_dashboard'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="use-ajax modal-popup button-red">',
      '#markup' => $dashboard_url,
      '#suffix' => '</div>',
    ];
    $url_redirect = Url::fromRoute('erpw_location.manage_location_url', ['country_tid' => $options],
     [], ['absolute' => TRUE])->toString();
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attached']['library'][] = 'erpw_location/erpw_location_js';
    $form['#attached']['drupalSettings']['erpw_location']['redirect_to_manage'] = $url_redirect;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['config.modal_form_example_modal_form'];
  }

}
