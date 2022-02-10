<?php

namespace Drupal\erpw_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ModalForm class.
 */
class Dashboard extends FormBase {
  /**
   * The Current user service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public function __construct(AccountProxyInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dashboard';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
    $current_user_role = $this->currentUser->getRoles();
    $access_role = ['administrator', 'super_admin'];
    if (array_intersect($current_user_role, $access_role)) {
      $url = Url::fromRoute('erpw_location.manage_location');
      $external_link = Link::fromTextAndUrl($this->t('Manage locations'), $url)->toString();
      $form['manage_location'] = [
        '#type' => 'markup',
        '#prefix' => '<div class="dashboard-link location-link">',
        '#markup' => $external_link,
        '#suffix' => '</div>',
      ];
      $url = Url::fromRoute('view.manage_service_types.page_1');
      $link = Link::fromTextAndUrl($this->t('Manage service types'), $url)->toString();
      $form['manage_services'] = [
        '#type' => 'markup',
        '#prefix' => '<div class="dashboard-link service-link">',
        '#markup' => $link,
        '#suffix' => '</div>',
      ];
      $url = Url::fromRoute('view.organisations.page_1');
      $link = Link::fromTextAndUrl($this->t('Manage organisation'), $url)->toString();
      $form['manage_organization'] = [
        '#type' => 'markup',
        '#prefix' => '<div class="dashboard-link org-link">',
        '#markup' => $link,
        '#suffix' => '</div>',
      ];
      $url = Url::fromRoute('view.user_lists.page_1');
      $link = Link::fromTextAndUrl($this->t('Manage Application Users'), $url)->toString();
      $form['manage_users'] = [
        '#type' => 'markup',
        '#prefix' => '<div class="dashboard-link user-link">',
        '#markup' => $link,
        '#suffix' => '</div>',
      ];
    }
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
