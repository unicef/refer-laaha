<?php

namespace Drupal\erpw_location\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\erpw_location\LocationService;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Location List Form.
 */
class LocationListForm extends FormBase {

  /**
   * The Current user service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
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
   * A LocationService instance.
   *
   * @var Drupal\erpw_location\LocationService
   */
  protected $locationService;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'location_list_form';
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $database,
  EntityTypeManagerInterface $entity_type_manager,
  AccountProxyInterface $current_user,
  MessengerInterface $messenger,
  FormBuilderInterface $form_builder,
  LocationService $location_service) {
    $this->database = $database;
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->messenger = $messenger;
    $this->formBuilder = $form_builder;
    $this->locationService = $location_service;
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
      $container->get('erpw_location.location_services')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $location_country_id = $form_state->getValue('location_options');
    if (!$id && !$location_country_id) {
      $id = erpw_location_country_id_from_domain();
      $form_state->setValue('location_options', $id);
    }
    $location_entities = $this->entityTypeManager->getStorage('location')->loadByProperties(
      ['type' => 'country', 'status' => 1]);
    $location_options = [];
    foreach ($location_entities as $location) {
      $location_options[$location->id()] = $location->get('name')->getValue()[0]['value'];
    }
    asort($location_options);
    if (!empty($location_entities)) {
      $form['location_options'] = [
        '#type' => 'select',
        '#options' => $location_options,
        '#empty_option' => $this->t('Select Country'),
        '#title' => $this->t('Country'),
        '#default_value' => $id,
        '#required' => TRUE,
        '#weight' => -109,
      ];
    }
    $form['location'] = [
      '#weight' => -107,
      '#prefix' => '<div id="edit-location-details" >',
      '#suffix' => '</div>',
    ];
    $form['location']['location_level1'] = [
      '#weight' => -103,
      '#prefix' => '<div id="location-level-1">',
      '#suffix' => '</div>',
    ];
    $form['location']['location_level2'] = [
      '#weight' => -101,
      '#prefix' => '<div id="location-level-2">',
      '#suffix' => '</div>',
    ];
    $form['location']['location_level3'] = [
      '#weight' => -99,
      '#prefix' => '<div id="location-level-3">',
      '#suffix' => '</div>',
    ];
    $form['location']['location_level4'] = [
      '#weight' => -97,
      '#prefix' => '<div id="location-level-4">',
      '#suffix' => '</div>',
    ];
    $form['location']['location_level1']['level_1'] = [
      '#type' => 'select',
      '#empty_option' => $this->t("Select province/district"),
      '#empty_value' => '',
      '#title' =>t("Select province/district"),
      '#weight' => -108,

    ];
    $form['location']['location_level2']['level_2'] = [
      '#type' => 'select',
      '#empty_option' => $this->t("Select district/upazila"),
      '#empty_value' => '',
      '#title' => t("Select district/upazila"),
      '#weight' => -106,

    ];
    $form['location']['location_level3']['level_3'] = [
      '#type' => 'select',
      '#empty_option' => $this->t("Select Level 3 Label"),
      '#empty_value' => '',
      '#title' => t("Select Level 3 Label"),
      '#weight' => -104,
    ];
    $form['location']['location_level4']['level_4'] = [
      '#type' => 'select',
      '#empty_option' => $this->t("Select Level 4 Label"),
      '#empty_value' => '',
      '#title' => t("Select Level 4 Label"),
      '#weight' => -102,
    ];

    // Terms array.
    $level_zero_tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree(
    'country',
    0,
    1,
    TRUE
    );
    $terms_array = [];
    foreach ($level_zero_tree as $level_zero) {
      $termid = $level_zero->get('tid')->value;
      foreach ($location_options as $key => $value) {
        if ($value == $level_zero->get('name')->value) {
          $terms_array[$key] = ['name' => $level_zero->get('name')->value];
          $tid = $key;
        }
      }
      $level_one_tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree(
      'country',
      $termid,
      1,
      TRUE
      );
      foreach ($level_one_tree as $level_one) {
        $terms_array[$tid]['children'][$level_one->get('tid')->value] = ['name' => $level_one->get('name')->value, 'parent_id' => $tid];
        $level_two_tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree(
        'country',
        $level_one->get('tid')->value,
        1,
        TRUE
        );
        foreach ($level_two_tree as $level_two) {
          $terms_array[$tid]['children'][$level_one->get('tid')->value]['children'][$level_two->get('tid')->value] = ['name' => $level_two->get('name')->value, 'parent_id' => $level_one->get('tid')->value];
          $level_three_tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree(
          'country',
          $level_two->get('tid')->value,
          1,
          TRUE
          );
          foreach ($level_three_tree as $level_three) {
            $terms_array[$tid]['children'][$level_one->get('tid')->value]['children'][$level_two->get('tid')->value]['children'][$level_three->get('tid')->value] = ['name' => $level_three->get('name')->value, 'parent_id' => $level_two->get('tid')->value];
            $level_four_tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree(
            'country',
            $level_three->get('tid')->value,
            1,
            TRUE
            );
            foreach ($level_four_tree as $level_four) {
              $terms_array[$tid]['children'][$level_one->get('tid')->value]['children'][$level_two->get('tid')->value]['children'][$level_three->get('tid')->value]['children'][$level_four->get('tid')->value] = ['name' => $level_four->get('name')->value, 'parent_id' => $level_three->get('tid')->value];
            }
          }
        }
      }
    }
    $form['#cache']['max-age'] = 0;
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attached']['library'][] = 'erpw_custom/erpw_js';
    $form['#attached']['drupalSettings']['erpw_location']['locations_array'] = $terms_array;
    $form['#attached']['library'][] = 'erpw_location/erpw_location_js';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger->deleteAll();
  }

}
