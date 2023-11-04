<?php

namespace Drupal\erpw_location\Form;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\erpw_location\LocationService;
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
   * The cache bin to cache terms.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $defaultCache;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

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
  LocationService $location_service,
  CacheBackendInterface $cacheBackend) {
    $this->database = $database;
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->messenger = $messenger;
    $this->formBuilder = $form_builder;
    $this->locationService = $location_service;
    $this->defaultCache = $cacheBackend;
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
      $container->get('erpw_location.location_services'),
      $container->get('cache.default')
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
    // Terms array.
    $level_zero_tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree(
      'country',
      0,
      1,
      TRUE
    );
    if ($terms_array = $this->defaultCache->get('erpw_locations_list')) {
      $terms_array = $terms_array->data;
    }
    else {
      $terms_array = [];
      foreach ($level_zero_tree as $level_zero) {
        $termid = $level_zero->get('tid')->value;
        foreach ($location_options as $key => $value) {
          if ($value == $level_zero->get('name')->value) {
            $terms_array[$key] = ['name' => $level_zero->get('name')->value];
            $tid = $key;
            $terms_array[$key]['level_label'] =
              ucfirst(
                strtolower(
                  $location_entities[$key]->get('level_1')->getValue()[0]['value']
                )
              );
          }
        }
        $level_one_tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree(
          'country',
          $termid,
          1,
          TRUE
        );
        foreach ($level_one_tree as $level_one) {
          $terms_array[$tid]['children'][$level_one->get('tid')->value] = [
            'name' => $level_one->get('name')->value,
            'parent_id' => $tid,
          ];
          $terms_array[$tid]['children'][$level_one->get('tid')->value]['level_label'] =
            ucfirst(
              strtolower(
                $location_entities[$tid]->get('level_2')->getValue()[0]['value']
              )
            );
          $level_two_tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree(
            'country',
            $level_one->get('tid')->value,
            1,
            TRUE
          );
          foreach ($level_two_tree as $level_two) {
            $terms_array[$tid]['children'][$level_one->get('tid')->value]['children'][$level_two->get('tid')->value] =
              [
                'name' => $level_two->get('name')->value,
                'parent_id' => $level_one->get('tid')->value,
              ];
            $terms_array[$tid]['children'][$level_one->get('tid')->value]['children'][$level_two->get('tid')->value]['level_label'] =
              ucfirst(
                strtolower(
                  $location_entities[$tid]->get('level_3')->getValue()[0]['value']
                )
              );
            $level_three_tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree(
              'country',
              $level_two->get('tid')->value,
              1,
              TRUE
            );
            foreach ($level_three_tree as $level_three) {
              $terms_array[$tid]['children'][$level_one->get('tid')->value]['children'][$level_two->get('tid')->value]['children'][$level_three->get('tid')->value] =
                [
                  'name' => $level_three->get('name')->value,
                  'parent_id' => $level_two->get('tid')->value,
                ];
              $terms_array[$tid]['children'][$level_one->get('tid')->value]['children'][$level_two->get('tid')->value]['children'][$level_three->get('tid')->value]['level_label'] =
                $location_entities[$tid]->get('level_4')->getValue() != [] ?
                  ucfirst(strtolower($location_entities[$tid]->get('level_4')->getValue()[0]['value'])) : '';
              $level_four_tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree(
                'country',
                $level_three->get('tid')->value,
                1,
                TRUE
              );
              foreach ($level_four_tree as $level_four) {
                $terms_array[$tid]['children'][$level_one->get('tid')->value]['children'][$level_two->get('tid')->value]['children'][$level_three->get('tid')->value]['children'][$level_four->get('tid')->value] =
                  [
                    'name' => $level_four->get('name')->value,
                    'parent_id' => $level_three->get('tid')->value,
                  ];
              }
            }
          }
        }
      }
      $this->defaultCache->set('erpw_locations_list', $terms_array, CacheBackendInterface::CACHE_PERMANENT, ['erpw_locations_list']);
    }
    if (!empty($location_entities)) {
      $form['location_options'] = [
        '#type' => 'select',
        '#options' => $location_options,
        '#empty_option' => $this->t('Select Country'),
        '#title' => $this->t('Country'),
        '#required' => TRUE,
        '#weight' => -109,
        '#attributes' => [
          'class' => ['location_options'],
        ],
      ];
    }
    $form['level_1'] = [
      '#type' => 'select',
      '#empty_option' => $this->t("Select province/district"),
      '#empty_value' => '',
      '#title' => t("Select province/district"),
      '#weight' => -108,
      '#validated' => TRUE,
      '#prefix' => '<div id="edit-location-details" ><div id="location-level-1">',
      '#suffix' => '</div>',
      '#attributes' => [
        'class' => ['level_1'],
      ],
    ];
    $form['level_2'] = [
      '#type' => 'select',
      '#empty_option' => $this->t("Select district/upazila"),
      '#empty_value' => '',
      '#title' => t("Select district/upazila"),
      '#weight' => -106,
      '#validated' => TRUE,
      '#prefix' => '<div id="location-level-2">',
      '#suffix' => '</div>',
      '#attributes' => [
        'class' => ['level_2'],
      ],
    ];
    $form['level_3'] = [
      '#type' => 'select',
      '#empty_option' => $this->t("Select Level 3 Label"),
      '#empty_value' => '',
      '#title' => t("Select Level 3 Label"),
      '#weight' => -104,
      '#validated' => TRUE,
      '#prefix' => '<div id="location-level-3">',
      '#suffix' => '</div>',
      '#attributes' => [
        'class' => ['level_3'],
      ],
    ];
    $form['level_4'] = [
      '#type' => 'select',
      '#empty_option' => $this->t("Select Level 4 Label"),
      '#empty_value' => '',
      '#title' => t("Select Level 4 Label"),
      '#weight' => -102,
      '#validated' => TRUE,
      '#prefix' => '<div id="location-level-4">',
      '#suffix' => '</div></div>',
      '#attributes' => [
        'class' => ['level_4'],
      ],
    ];
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
