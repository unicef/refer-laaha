<?php

namespace Drupal\erpw_location\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ModalForm class.
 */
class LocationPopupForm extends FormBase {

  /**
   * The temp store factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Location Popup Form constructor.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The temp store factory.
   */
  public function __construct(
    PrivateTempStoreFactory $temp_store_factory) {

    $this->tempStoreFactory = $temp_store_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
    );
  }

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

    $store = $this->tempStoreFactory->get('erpw_location_collection');
    $redirect_url_value = $store->get('location_redirect_url');
    $url_redirect = Url::fromUri('internal:' . $redirect_url_value)->toString();

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
