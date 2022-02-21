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
        '#ajax' => [
          'callback' => [$this, 'getLocationDetail'],
          'event' => 'change',
          'method' => 'replace',
          'wrapper' => 'edit-location-details',
          'progress' => [
            'type' => 'throbber',
          ],
        ],
      ];
    }
    $form['location'] = [
      '#weight' => -107,
      '#prefix' => '<div id="edit-location-details" >',
      '#suffix' => '</div>',
    ];
    $form['location']['intro_text'] = [
      '#type' => 'markup',
      '#markup' => '<div id="intro-text">' . $this->t('Select the country first, to view the respective form') . '</div>',
    ];

    if (!empty($form_state->getValue('location_options'))) {
      unset($form['location']['intro_text']);
      $location_country_id = $form_state->getValue('location_options');
      $location_entity = $this->entityTypeManager->getStorage('location')->load($location_country_id);
      $location_tid = $location_entity->get('field_location_taxonomy_term')->getValue()[0]['target_id'];
      $location_levels = \Drupal::service('erpw_location.location_services')->getLocationLevels($location_country_id);
      $childs = \Drupal::service('erpw_location.location_services')->getChildrenByTid($location_tid);
      $i = 1;
      $form['location']['location_level'] = [
        '#weight' => -105,
        '#prefix' => '<div id="location-levels">',
        '#suffix' => '</div>',
      ];
      $form['location']['location_level1'] = [
        '#weight' => -103,
        '#prefix' => '<div id="location-level-1"></div>',
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
      foreach ($location_levels as $key => $level) {
        if ($key == 0) {
          $this->level_key = $key;
          $form['location']['location_level']['level_1'] = [
            '#type' => 'select',
            '#empty_option' => $this->t("Select Level 1 Label"),
            '#empty_value' => '',
            '#options' => $childs,
            '#title' => $level,
            '#weight' => -108,
            '#ajax' => [
              'callback' => [$this, 'getLevelTwo'],
              'event' => 'change',
              'method' => 'replace',
              'wrapper' => 'location-levels',
              'progress' => [
                'type' => 'throbber',
              ],
            ],
          ];
        }
        else {
          if (!empty($form_state->getValue('level_' . $key))) {
            if ($key == 1) {
              $parent_tid = $form_state->getValue('level_' . $key);
              $level_1_options = \Drupal::service('erpw_location.location_services')->getChildrenByTid($parent_tid);
              $form['location']['location_level']['level_2'] = [
                '#type' => 'select',
                '#empty_option' => $this->t("Select Level 2 Label"),
                '#options' => $level_1_options,
                '#empty_value' => '',
                '#title' => $level,
                '#weight' => -106,
                '#ajax' => [
                  'callback' => [$this, 'getLevelThree'],
                  'event' => 'change',
                  'method' => 'replace',
                  'wrapper' => 'location-level-1',
                  'progress' => [
                    'type' => 'throbber',
                  ],
                ],
              ];
              $array_keys = array_keys($location_levels);
              $last_key = end($array_keys);
              if ($last_key == $key) {
                $form['location']['location_level']['level_' . ($key + 1)]['#multiple'] = TRUE;
              }
            }
            else {
              if (!empty($form_state->getValue('level_' . $key))) {
                if ($key == 2) {
                  $parent_level2_tid = $form_state->getValue('level_' . $key);
                  $level_2_options = \Drupal::service('erpw_location.location_services')->getChildrenByTid($parent_level2_tid);
                  $form['location']['location_level']['level_3'] = [
                    '#type' => 'select',
                    '#empty_option' => $this->t("Select Level 3 Label"),
                    '#empty_value' => '',
                    '#options' => $level_2_options,
                    '#title' => $level,
                    '#weight' => -104,
                  ];
                  $array_keys = array_keys($location_levels);
                  $last_key = end($array_keys);
                  if ($last_key == $key) {
                    $form['location']['location_level']['level_' . ($key + 1)]['#ajax'] = [
                      'callback' => [$this, 'getLevelFour'],
                      'event' => 'change',
                      'method' => 'replace',
                      'wrapper' => 'location-level-4',
                      'progress' => [
                        'type' => 'throbber',
                      ],
                    ];

                  }
                  else {
                    $form['location']['location_level']['level_' . ($key + 1)]['#ajax'] = [
                      'callback' => [$this, 'getLevelFour'],
                      'event' => 'change',
                      'method' => 'replace',
                      'wrapper' => 'location-level-4',
                      'progress' => [
                        'type' => 'throbber',
                      ],
                    ];
                  }
                }
                else {
                  if (!empty($form_state->getValue('level_' . $key))) {
                    if ($key == 3) {
                      $parent_level3_tid = $form_state->getValue('level_' . $key);
                      $level_3_options = \Drupal::service('erpw_location.location_services')->getChildrenByTid($parent_level3_tid);
                      $form['location']['location_level']['level_4'] = [
                        '#type' => 'select',
                        '#multiple' => TRUE,
                        '#empty_option' => $this->t("Select Level 4 Label"),
                        '#empty_value' => '',
                        '#options' => $level_3_options,
                        '#title' => $level,
                        '#weight' => -102,
                      ];
                    }
                  }
                }
              }
            }
          }
        }
        $i++;
      }
    }

    $form['#cache']['max-age'] = 0;
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attached']['library'][] = 'erpw_custom/erpw_js';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getLevelTwo(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $location_country_id = $form_state->getValue('location_options');
    $location_levels = \Drupal::service('erpw_location.location_services')->getLocationLevels($location_country_id);
    $parent_level2_tid = $form_state->getValue('level_1');
    $level_2_options = \Drupal::service('erpw_location.location_services')->getChildrenByTid($parent_level2_tid);
    if (!empty($level_2_options)) {
      $level_2_options_final[''] = $this->t('Select Level 2 Label');
      foreach ($level_2_options as $key => $value) {
        $level_2_options_final[$key] = $value;
      }
    }
    else {
      $level_2_options_final = $level_2_options;
    }
    $form['location']['location_level']['level_2']['#empty_option'] = $this->t("Select Level 2 Label");
    $form['location']['location_level']['level_3']['#options'] = [];
    $form['location']['location_level']['level_4']['#options'] = [];
    $form['location']['location_level']['level_2']['#options'] = $level_2_options_final;
    $form['location']['location_level']['level_2']['#title'] = $location_levels[1];
    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#location-level-2', $form['location']['location_level']['level_2']));
    $response->addCommand(new HtmlCommand('#location-level-3', ''));
    $response->addCommand(new HtmlCommand('#location-level-4', ''));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function getLevelThree(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $parent_level2_tid = $form_state->getValue('level_2');
    $location_country_id = $form_state->getValue('location_options');
    $location_levels = \Drupal::service('erpw_location.location_services')->getLocationLevels($location_country_id);
    $level_2_options = \Drupal::service('erpw_location.location_services')->getChildrenByTid($parent_level2_tid);
    if (!empty($level_2_options)) {
      $level_2_options_final[''] = $this->t('Select Level 3 Label');
      foreach ($level_2_options as $key => $value) {
        $level_2_options_final[$key] = $value;
      }
    }
    else {
      $level_2_options_final = $childs;
    }

    $form['location']['location_level']['level_3']['#empty_option'] = $this->t("Select Level 3 Label");
    $form['location']['location_level']['level_4']['#options'] = [];
    $form['location']['location_level']['level_3']['#options'] = $level_2_options_final;
    $form['location']['location_level']['level_3']['#empty_value'] = '';
    $form['location']['location_level']['level_3']['#title'] = $location_levels[2];
    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#location-level-3', $form['location']['location_level']['level_3']));
    $response->addCommand(new HtmlCommand('#location-level-4', ''));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function getLevelFour(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $parent_level2_tid = $form_state->getValue('level_3');
    $level_2_options = \Drupal::service('erpw_location.location_services')->getChildrenByTid($parent_level2_tid);
    $form['location']['location_level']['level_4']['#empty_option'] = $this->t("Select Level 4 Label");
    $form['location']['location_level']['level_4']['#options'] = $level_2_options;
    $form['location']['location_level']['level_4']['#empty_value'] = '';
    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#location-level-4', $form['location']['location_level']['level_4']));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function getLevelFourData(array &$form, FormStateInterface $form_state) {
    return $form['location']['location_level']['location_level3'];
  }

  /**
   * {@inheritdoc}
   */
  public function getLocationDetail(array &$form, FormStateInterface $form_state) {
    $location_country_id = $form_state->getValue('location_options');
    $location_entity = $this->entityTypeManager->getStorage('location')->load($location_country_id);
    $location_tid = $location_entity->get('field_location_taxonomy_term')->getValue()[0]['target_id'];
    $location_levels = \Drupal::service('erpw_location.location_services')->getLocationLevels($location_country_id);
    $childs = \Drupal::service('erpw_location.location_services')->getChildrenByTid($location_tid);

    if (!empty($childs)) {
      $level_2_options_final[''] = $this->t('Select Level 1 Label');
      foreach ($childs as $key => $value) {
        $level_2_options_final[$key] = $value;
      }
    }
    else {
      $level_2_options_final = $childs;
    }
    $response = new AjaxResponse();

    $form['location']['location_level']['level_1']['#options'] = $level_2_options_final;
    $form['location']['location_level']['level_1']['#empty_option'] = $this->t("Select Level 1 Label");
    $form['location']['location_level']['level_1']['#empty_value'] = '';
    $form['location']['location_level']['level_1']['#title'] = $location_levels[0];
    unset($form['location']['location_level']['level_2']);
    unset($form['location']['location_level']['level_3']);
    unset($form['location']['location_level']['level_4']);
    $response->addCommand(new HtmlCommand('#edit-location-details', $form['location']));
    return $response;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger->deleteAll();
  }

}
