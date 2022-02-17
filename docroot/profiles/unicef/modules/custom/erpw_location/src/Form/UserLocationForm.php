<?php

namespace Drupal\erpw_location\Form;

use Drupal\Core\Url;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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
   * {@inheritdoc}
   */
  public function __construct(
    Connection $database,
    EntityTypeManagerInterface $entity_type_manager,
    AccountInterface $current_user,
    MessengerInterface $messenger,
    FormBuilderInterface $form_builder) {

    $this->database = $database;
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->messenger = $messenger;
    $this->formBuilder = $form_builder;
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
      $container->get('form_builder')
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
        $locaton_level = $value;
      }
      $locaton_level = implode(",", $levels['level_4']);
    }
    elseif (!empty($levels['level_3'])) {
      $locaton_level = $levels['level_3'];
    }
    elseif (!empty($levels['level_2'])) {
      $locaton_level = $levels['level_2'];
    }
    elseif (!empty($levels['level_1'])) {
      $locaton_level = $levels['level_1'];
    }
    elseif (!empty($levels['location_options'])) {
      if ($levels['location_level_page'] == "location") {
        $location_entity = $this->entityTypeManager->getStorage('location')->load($levels['location_options']);
        $locaton_level = $location_entity->get('field_location_taxonomy_term')->getValue()[0]['target_id'];
      }
    }

    if ($levels['location_level_page'] == "location") {
      $url = Url::fromUri('internal:/manage-location/' . $levels['location_options'] . "/" . $locaton_level);
      $form['location_level']['button'] = $form_state->setRedirectUrl($url);
    }
    else {
      $current_user = $this->currentUser->id();
      $user = $this->entityTypeManager->getStorage('user')->load($current_user);
      // Set value for field.
      $user->field_location_details->value = $locaton_level;
      $user->save();

      $url = Url::fromRoute('<front>');
      $form['location_level']['button'] = $form_state->setRedirectUrl($url);
    }
  }

}
