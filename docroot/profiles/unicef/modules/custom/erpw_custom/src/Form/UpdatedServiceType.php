<?php

namespace Drupal\erpw_custom\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\erpw_custom\Services\ErpwCustomService;

/**
 * ModalForm class.
 */
class UpdatedServiceType extends FormBase {

  use StringTranslationTrait;

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
    return 'updated_service_type';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
    $current_language = $this->erpwCustomService->getCurrentLanguage();
    $user_language = $this->erpwCustomService->getUserLanguage();
    if ($current_language != $user_language) {
      $url = '/' . $user_language . '/dashboard';
    }
    else {
      $url = Url::fromRoute('view.manage_service_types.page_1')->toString();
    }
    $external_link = "<a href='$url' class='button-red'>" . $this->t("BACK TO DASHBOARD") . "</a>";

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
    $form['cancel'] = [
      '#type' => 'markup',
      '#markup' => $external_link,
    ];
    $form['#attached']['library'][] = 'erpw_custom/erpw_js';
    $form['#attached']['drupalSettings']['erpw_custom']['manage_service_type_page'] = $url;

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

}
