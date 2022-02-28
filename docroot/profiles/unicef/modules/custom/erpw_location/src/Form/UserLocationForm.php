<?php

namespace Drupal\erpw_location\Form;

use Drupal\Core\Url;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\erpw_location\LocationCookie;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LocationListForm.
 */
class UserLocationForm extends LocationListForm {

  /**
   * Drupal\Core\Session\AccountInterface definition.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The cookie as a service.
   *
   * @var \Drupal\erpw_location\LocationCookie
   */
  protected $locationCookie;

  /**
   * The tempstore factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    Connection $database,
    EntityTypeManagerInterface $entity_type_manager,
    AccountInterface $current_user,
    MessengerInterface $messenger,
    FormBuilderInterface $form_builder,
    LocationCookie $location_cookie,
    PrivateTempStoreFactory $temp_store_factory) {
    $this->database = $database;
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->messenger = $messenger;
    $this->formBuilder = $form_builder;
    $this->locationCookie = $location_cookie;
    $this->tempStoreFactory = $temp_store_factory->get('erpw_location_collection');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('messenger'),
      $container->get('form_builder'),
      $container->get('erpw_location.location_cookie'),
      $container->get('tempstore.private'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_location_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL, $page = "") {
    if (!empty($id)) {
      $location_entity = $this->entityTypeManager->getStorage('location')->loadByProperties(
        ['field_location_taxonomy_term' => $id]
      );
      foreach ($location_entity as $location) {
        $id = $location->Id();
      }
    }
    $form = parent::buildForm($form, $form_state, $id);

    $form['location_level']['button'] = [
      '#type' => 'submit',
      '#title' => "Change",
      '#value' => 'DONE',
      '#attributes' => [
        'class' => [
          'use-ajax',
          'arrow-btn',
        ],
      ],
    ];
    $form['location_level_page'] = [
      '#type' => 'hidden',
      '#value' => $page,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $levels = $form_state->getValues();

    if (!empty($levels['level_4'])) {
      foreach ($levels['level_4'] as $value) {
        $location_value = $value;
      }
      $location_value = implode(",", $levels['level_4']);
    }
    elseif (!empty($levels['level_3'])) {
      $location_value = $levels['level_3'];
    }
    elseif (!empty($levels['level_2'])) {
      $location_value = $levels['level_2'];
    }
    elseif (!empty($levels['level_1'])) {
      $location_value = $levels['level_1'];
    }
    elseif (!empty($levels['location_options'])) {
      $location_entity = $this->entityTypeManager->getStorage('location')->load($levels['location_options']);
      if (!empty($location_entity->get('field_location_taxonomy_term')->getValue())) {
        $location_value = $location_entity->get('field_location_taxonomy_term')->getValue()[0]['target_id'];
      }
    }

    if ($levels['location_level_page'] == 'location') {
      $url = Url::fromUri('internal:/manage-location/' . $levels['location_options'] . '/' . $location_value);
      $form['location_level']['button'] = $form_state->setRedirectUrl($url);
    }
    else {
      if (empty($this->locationCookie->getCookieValue())) {
        $this->locationCookie->setCookieValue(base64_encode('country_tid_' . time()));
        $this->tempStoreFactory->set(base64_decode($this->locationCookie->getCookieValue()), $location_value);
      }
      else {
        $this->tempStoreFactory->set(base64_decode($this->locationCookie->getCookieValue()), $location_value);
      }
      $url = Url::fromRoute('<front>');
      $form['location_level']['button'] = $form_state->setRedirectUrl($url);
    }
  }

}
