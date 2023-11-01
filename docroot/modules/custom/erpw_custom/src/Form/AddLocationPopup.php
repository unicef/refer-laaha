<?php

namespace Drupal\erpw_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ModalForm class.
 */
class AddLocationPopup extends FormBase {

  /**
   * The temp store factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Constructs a new Add Location Popup.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The temp store factory.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory) {
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
    return 'add_location_modal_popup';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $header_message = NULL, string $message = NULL, string $mode = NULL) {
    $url = Url::fromRoute('erpw_location.manage_location');
    $external_link = Link::fromTextAndUrl('BACK TO LOCATION', $url)->toString();
    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div class="review-msg">' . $header_message . '</div><div class="email-notify">' . $message . '</div>',
    ];
    $form['back_to_dashboard'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="use-ajax modal-popup button-red">',
      '#markup' => $external_link,
      '#suffix' => '</div>',
    ];

    $store = $this->tempStoreFactory->get('erpw_location_collection');
    $redirect_url_value = $store->get('location_redirect_url');
    $url = Url::fromUri('internal:' . $redirect_url_value)->toString();

    $form['#attached']['library'][] = 'erpw_location/erpw_location_js';
    $form['#attached']['drupalSettings']['erpw_location']['redirect_to_manage_location'] = $url;
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
