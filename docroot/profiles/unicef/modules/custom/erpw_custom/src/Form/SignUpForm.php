<?php

namespace Drupal\erpw_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\erpw_location\LocationService;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class user signup form.
 */
class SignUpForm extends FormBase {

  /**
   * The Current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
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
   * The location service.
   *
   * @var \Drupal\erpw_location\LocationService
   */
  protected $locationService;

  /**
   * A userId variable.
   *
   * @var Drupal\erpw_custom
   */
  protected $userId;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sign_up';
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    Connection $database,
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
    $this->userId = $id;
    $roles = $this->entityTypeManager->getStorage('user_role')->loadMultiple();
    foreach ($roles as $role) {
      if ($role->id() == 'administrator' || $role->id() == 'anonymous' || $role->id() == 'authenticated') {
        continue;
      }
      $system_roles[$role->id()] = $role->label();
    }

    if ($this->userId != "") {
      $user_details = $this->entityTypeManager->getStorage('user')->load($this->userId);
      $first_name = $user_details->field_first_name->value;
      $last_name = $user_details->field_last_name->value;
      $field_phone = $user_details->field_phone->value;
      $organisation = $user_details->field_organisation->target_id[0];
      $organisation = $this->entityTypeManager->getStorage('node')->load($organisation);
      $field_position = $user_details->field_position->value;
      $email = $user_details->getEmail();
      $system_role = $user_details->getRoles();
    }

    if ($form_state->has('page') && $form_state->get('page') == 2 && $this->userId == "") {
      return self::formPageTwo($form, $form_state);
    }

    if ($form_state->has('page') && $form_state->get('page') == 3) {
      return self::formPageThree($form, $form_state);
    }

    $form_state->set('page', 1);
    if ($this->userId == "") {
      $form['progress_step1'] = [
        '#markup' => '<div class="steps-highlight">
          <div class="personal-detail-page step-circle">' . $this->t('<div class="step-number">1</div>') .
        '</div>',
      ];
      $form['progress_step2'] = [
        '#markup' => '<div class="location-detail-page step-circle">' . $this->t('<div class="step-number">2</div>') . '</div>',
      ];
      $form['progress_step3'] = [
        '#markup' => '<div class="password-creation-page step-circle">' .
        $this->t('<div class="step-number">3</div>') . '</div>
        </div>',
      ];
    }

    $form['message-step'] = [
      '#markup' => '<div class="step">' . $this->t('Step 1: Personal details') . '</div>',
    ];

    $form['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First name'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Enter first name'),
      '#default_value' => ($this->userId) ? $form_state->getValue('first_name', $first_name) : $form_state->getValue('first_name', ''),
    ];

    $form['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last name'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Enter last name'),
      '#default_value' => ($this->userId) ? $form_state->getValue('last_name', $last_name) : $form_state->getValue('last_name', ''),
    ];

    $form['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Example@gmail.com'),
      '#default_value' => ($this->userId) ? $form_state->getValue('email', $email) : $form_state->getValue('email', ''),
    ];

    $form['phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone'),
      '#required' => TRUE,
      '#placeholder' => $this->t('**********'),
      '#default_value' => ($this->userId) ? $form_state->getValue('phone', $field_phone) : $form_state->getValue('phone', ''),
    ];

    $form['organisation'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => $this->t('Organisation'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Select Organisation'),
      '#default_value' => ($this->userId) ? $form_state->getValue('organisation', $organisation) : $form_state->getValue('organisation', ''),
      '#selection_settings' => [
        'target_bundles' => ['organisation'],
        'sort' => [
          'field' => 'title',
          'direction' => 'ASC',
        ],
        'match_operator' => 'STARTS_WITH',
        'match_limit' => 10,
      ],
    ];

    $form['position'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Position'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Select position'),
      '#default_value' => ($this->userId) ? $form_state->getValue('position', $field_position) : $form_state->getValue('position', ''),
    ];

    $form['system_role'] = [
      '#type' => 'select',
      '#options' => $system_roles,
      '#empty_option' => $this->t('Select system role'),
      '#title' => $this->t('System role'),
      '#required' => TRUE,
      '#default_value' => ($this->userId) ? $form_state->getValue('system_role', $system_role) : $form_state->getValue('system_role', ''),
    ];
    if ($this->userId != "") {
      $form = self::formPageTwo($form, $form_state);
    }
    else {

      $form['actions'] = [
        '#type' => 'actions',
      ];

      $form['actions']['next'] = [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => $this->t('Next'),
        '#attributes' => [
          'class' => [
            'signup-next',
          ],
        ],
        '#submit' => ['::submitPageOne'],
        '#validate' => ['::validatePageOne'],
      ];
    }
    $form['#cache']['max-age'] = 0;
    return $form;
  }

  /**
   * Sets an error if supplied fields has not been filled.
   */
  public function validatePageOne(array &$form, FormStateInterface $form_state) {
    if (!is_numeric($form_state->getValue('phone'))) {
      $form_state->setErrorByName('phone', $this->t('Phone number must be numeric'));
    }
    if (!$form_state->getValue('email') || !filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL)) {
      $form_state->setErrorByName('email', $this->t('Please provide valid email address.'));
    }
    $email = $form_state->getValue('email');
    if (isset($email) && user_load_by_mail($email)) {
      $form_state->setErrorByName('email', $this->t('Email already in use.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitPageOne(array &$form, FormStateInterface $form_state) {
    if ($this->userId == "") {
      $form_state->set('page_values', [
        'first_name' => $form_state->getValue('first_name'),
        'last_name' => $form_state->getValue('last_name'),
        'email' => $form_state->getValue('email'),
        'phone' => $form_state->getValue('phone'),
        'organisation' => $form_state->getValue('organisation'),
        'position' => $form_state->getValue('position'),
        'system_role' => $form_state->getValue('system_role'),
      ])
        ->set('page', 2)
        ->setRebuild(TRUE);
    }
    else {
      $location_tid = '';
      if (!empty($form_state->getValue('level_4'))) {
        $location_tid = $form_state->getValue('level_4');
      }
      elseif (!empty($form_state->getValue('level_3'))) {
        $location_tid = $form_state->getValue('level_3');
      }
      elseif (!empty($form_state->getValue('level_2'))) {
        $location_tid = $form_state->getValue('level_2');
      }
      elseif (!empty($form_state->getValue('level_1'))) {
        $location_tid = $form_state->getValue('level_1');
      }
      $user = $this->entityTypeManager()->getStorage('user')->load($this->userId);
      $user->setEmail($form_state->getValue('email'));
      $user->set('field_first_name', $form_state->getValue('first_name'));
      $user->set('field_last_name', $form_state->getValue('last_name'));
      $user->set('field_phone', $form_state->getValue('phone'));
      $user->set('field_organisation', $form_state->getValue('organisation'));
      $user->set('field_position', $form_state->getValue('position'));
      $user->set('roles', $form_state->getValue('system_role'));
      // $user->set('field_location', [$location_tid]);
      $user->save();
    }

  }

  /**
   * {@inheritdoc}
   */
  public function formPageTwo(array &$form, FormStateInterface $form_state, $id = NULL) {

    if ($this->userId == "") {
      $form['#prefix'] = '<div id="status-message"></div>';
      $form['progress_step1'] = [
        '#markup' => '<div class="steps-highlight">
          <div class="personal-detail-page step-circle">' . $this->t('<div class="step-number">1</div>') .
        '</div>',
      ];
      $form['progress_step2'] = [
        '#markup' => '<div class="location-detail-page active step-circle">' . $this->t('<div class="step-number">2</div>') . '</div>',
      ];
      $form['progress_step3'] = [
        '#markup' => '<div class="password-creation-page step-circle">' . $this->t('<div class="step-number">3</div>') . '</div></div>',
      ];

      $form['message-step'] = [
        '#markup' => '<div class="step">' . $this->t('Step 2: Geographical coverage of your role') . '</div>',
      ];

    }
    else {
      $user_details = $this->entityTypeManager->getStorage('user')->load($this->userId);
      $user_location_array = $this->locationService->getAllAncestors($user_details->field_location_details->value);

      $user_location_entity_id = $this->locationService->getLocationSingleEntityIdByTid($user_location_array[0]);

    }
    $level_one_default = !empty($user_location_array[1]) ? $user_location_array[1] : "";
    $level_two_default = !empty($user_location_array[2]) ? $user_location_array[2] : "";
    $level_three_default = !empty($user_location_array[3]) ? $user_location_array[3] : "";
    $level_four_default = !empty($user_location_array[4]) ? $user_location_array[4] : "";
    $user_location_entity_id = !empty($user_location_entity_id) ? $user_location_entity_id : "";
    $location_entities = $this->entityTypeManager->getStorage('location')->loadByProperties(
      ['type' => 'country', 'status' => 1]);
    $location_options = [];
    foreach ($location_entities as $location) {
      $location_options[$location->id()] = $location->get('name')->getValue()[0]['value'];
    }

    if (!empty($location_entities)) {
      $form['location_options'] = [
        '#type' => 'select',
        '#options' => $location_options,
        '#empty_option' => $this->t('Select Country'),
        '#title' => $this->t('Country'),
        '#default_value' => !empty($form_state->getValue('location_options', '')) ? $form_state->getValue('location_options', '') : $user_location_entity_id,
        '#required' => TRUE,
        '#ajax' => [
          'callback' => '::getLocationDetail',
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
      '#prefix' => '<div id="edit-location-details" class="card-shadow">',
      '#suffix' => '</div>',
    ];
    $form['all_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'all-wrapper'],
    ];
    $form['location']['all_wrapper']['intro_text'] = [
      '#type' => 'markup',
      '#markup' => '<div id="intro-text">' . $this->t('Select country to view its Hierarchy.') . '</div>',
    ];
    if (!empty($form_state->getValue('location_options'))) {
      $form['top_wrapper']['all_wrapper']['#prefix'] = '<div class="location-container">';
      $form['top_wrapper']['all_wrapper']['#suffix'] = '</div>';
    }

    if (!empty($form_state->getValue('location_options')) || $this->userId) {
      unset($form['location']['all_wrapper']['intro_text']);
      $location_country_id = !empty($form_state->getValue('location_options')) ? $form_state->getValue('location_options') : $user_location_entity_id;
      $location_entity = $this->entityTypeManager->getStorage('location')->load($location_country_id);
      $location_tid = $location_entity->get('field_location_taxonomy_term')->getValue()[0]['target_id'];
      $location_levels = $this->locationService->getLocationLevels($location_country_id);
      $childs = $this->locationService->getChildrenByTid($location_tid);
      $i = 1;
      $form['location']['all_wrapper']['location_level'] = [
        '#prefix' => '<div id="location-levels">',
        '#suffix' => '</div>',
      ];
      $form['location']['all_wrapper']['location_level1'] = [
        '#prefix' => '<div id="location-level-1"></div>',
        '#suffix' => '</div>',
      ];
      $form['location']['all_wrapper']['location_level2'] = [
        '#prefix' => '<div id="location-level-2">',
        '#suffix' => '</div>',
      ];
      $form['location']['all_wrapper']['location_level3'] = [
        '#prefix' => '<div id="location-level-3">',
        '#suffix' => '</div>',
      ];
      $form['location']['all_wrapper']['location_level4'] = [
        '#prefix' => '<div id="location-level-4">',
        '#suffix' => '</div>',
      ];
      foreach ($location_levels as $key => $level) {
        if ($key == 0) {
          $this->level_key = $key;
          $form['location']['all_wrapper']['location_level']['level_1'] = [
            '#type' => 'select',
            '#empty_option' => $this->t("Select Level 1 Label"),
            '#empty_value' => '',
            '#options' => $childs,
            '#title' => $level,
            '#weight' => 10,
            '#default_value' => !empty($form_state->getValue('level_1', '')) ? $form_state->getValue('level_1', '') : $level_one_default,
            '#ajax' => [
              'callback' => '::getLevelTwo',
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
          if (!empty($form_state->getValue('level_' . $key)) || $this->userId) {
            if ($key == 1) {
              $parent_tid = !empty($form_state->getValue('level_' . $key)) ? $form_state->getValue('level_' . $key) : $level_one_default;
              $level_1_options = $this->locationService->getChildrenByTid($parent_tid);
              $form['location']['all_wrapper']['location_level']['level_2'] = [
                '#type' => 'select',
                '#empty_option' => $this->t("Select Level 2 Label"),
                '#options' => $level_1_options,
                '#empty_value' => '',
                '#title' => $level,
                '#weight' => 20,
                '#default_value' => !empty($form_state->getValue('level_2', '')) ? $form_state->getValue('level_2', '') : $level_two_default,
                '#ajax' => [
                  'callback' => '::getLevelThree',
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
                $form['location']['all_wrapper']['location_level']['level_' . ($key + 1)]['#multiple'] = TRUE;
              }
            }
            else {
              if (!empty($form_state->getValue('level_' . $key)) || $this->userId) {
                if ($key == 2) {
                  $parent_level2_tid = !empty($form_state->getValue('level_' . $key)) ? $form_state->getValue('level_' . $key) : $level_two_default;
                  $level_2_options = $this->locationService->getChildrenByTid($parent_level2_tid);
                  $form['location']['all_wrapper']['location_level']['level_3'] = [
                    '#type' => 'select',
                    '#empty_option' => $this->t("Select Level 3 Label"),
                    '#empty_value' => '',
                    '#options' => $level_2_options,
                    '#title' => $level,
                    '#weight' => 30,
                    '#default_value' => !empty($form_state->getValue('level_3', '')) ? $form_state->getValue('level_3', '') : $level_three_default,
                  ];
                  $array_keys = array_keys($location_levels);
                  $last_key = end($array_keys);
                  if ($last_key == $key) {
                    $form['location']['all_wrapper']['location_level']['level_' . ($key + 1)]['#multiple'] = TRUE;
                  }
                  else {
                    $form['location']['all_wrapper']['location_level']['level_' . ($key + 1)]['#ajax'] = [
                      'callback' => '::getLevelFour',
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
                  if (!empty($form_state->getValue('level_' . $key)) || $this->userId) {
                    if ($key == 3) {
                      $parent_level3_tid = $form_state->getValues();
                      if (isset($parent_level3_tid['level_3']) && is_array($parent_level3_tid['level_3'])) {
                        $parent_level3_tid_array = array_keys($parent_level3_tid['level_3']);
                        $parent_level_3 = !empty($parent_level3_tid['level_3']) ? $parent_level3_tid_array[0] : $level_three_default;
                        $level_4_array = "";
                      }
                      else {
                        $parent_level_3 = !empty($parent_level3_tid['level_3']) ? $parent_level3_tid['level_3'] : $level_three_default;
                        $level_4_array = [$level_four_default];
                      }
                      $level_3_options = $this->locationService->getChildrenByTid($parent_level_3);
                      $form['location']['all_wrapper']['location_level']['level_4'] = [
                        '#type' => 'select',
                        '#multiple' => TRUE,
                        '#empty_option' => $this->t("Select Level 4 Label"),
                        '#empty_value' => '',
                        '#options' => $level_3_options,
                        '#title' => $level,
                        '#weight' => 40,
                        '#default_value' => !empty($form_state->getValue('level_4', '')) ? $form_state->getValue('level_4', '') : $level_4_array,
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

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['action-wrapper'] = [
      '#prefix' => '<div id="form-actions" class="sign-buttons">',
      '#suffix' => '</div>',
    ];
    if (!empty($form_state->getValue('location_options'))) {
      if ($this->userId == "") {
        $form['action-wrapper']['actions']['back'] = [
          '#type' => 'submit',
          '#value' => $this->t('Back'),
          '#attributes' => [
            'class' => [
              'button-border',
            ],
          ],
          '#submit' => ['::pageOneBack'],
          '#limit_validation_errors' => [],
        ];
      }
      $form['action-wrapper']['actions']['next'] = [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => $this->t('Next'),
        '#attributes' => [
          'class' => [
            'signup-next',
          ],
        ],
        '#submit' => ['::submitPageTwo'],
        '#validate' => ['::validatePageTwo'],
      ];
    }
    if ($this->userId != "") {

      $form['action-wrapper']['actions']['next'] = [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => $this->t('Next'),
        '#attributes' => [
          'class' => [
            'signup-next',
          ],
        ],
        '#submit' => ['::submitPageOne'],
        '#validate' => ['::validatePageOne'],
      ];
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
    $location_levels = $this->locationService->getLocationLevels($location_country_id);
    $parent_level2_tid = $form_state->getValue('level_1');
    $level_2_options = $this->locationService->getChildrenByTid($parent_level2_tid);
    if (!empty($level_2_options)) {
      $i = 1;
      foreach ($level_2_options as $key => $value) {
        if ($i == 1) {
          $level_2_options_final[''] = $this->t('Select Level 2 Label');
          $level_2_options_final[$key] = $value;
        }
        else {
          $level_2_options_final[$key] = $value;
        }
        $i++;
      }
    }
    else {
      $level_2_options_final = $level_2_options;
    }
    $form['location']['all_wrapper']['location_level']['level_2']['#empty_option'] = $this->t("Select Level 2 Label");
    $form['location']['all_wrapper']['location_level']['level_3']['#options'] = [];
    $form['location']['all_wrapper']['location_level']['level_4']['#options'] = [];
    $form['location']['all_wrapper']['location_level']['level_2']['#options'] = $level_2_options_final;
    $form['location']['all_wrapper']['location_level']['level_2']['#title'] = $location_levels[1];
    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#location-level-2', $form['location']['all_wrapper']['location_level']['level_2']));
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
    $location_levels = $this->locationService->getLocationLevels($location_country_id);
    $level_2_options = $this->locationService->getChildrenByTid($parent_level2_tid);
    if (!empty($level_2_options)) {
      $i = 1;
      foreach ($level_2_options as $key => $value) {
        if ($i == 1) {
          $level_2_options_final[''] = $this->t('Select Level 3 Label');
          $level_2_options_final[$key] = $value;
        }
        else {
          $level_2_options_final[$key] = $value;
        }
        $i++;
      }
    }
    else {
      $level_2_options_final = $level_2_options;
    }
    $form['location']['all_wrapper']['location_level']['level_3']['#empty_option'] = $this->t("Select Level 3 Label");
    $form['location']['all_wrapper']['location_level']['level_4']['#options'] = [];
    $form['location']['all_wrapper']['location_level']['level_3']['#options'] = $level_2_options_final;
    $form['location']['all_wrapper']['location_level']['level_3']['#empty_value'] = '';
    $form['location']['all_wrapper']['location_level']['level_3']['#title'] = $location_levels[2];
    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#location-level-3', $form['location']['all_wrapper']['location_level']['level_3']));
    $response->addCommand(new HtmlCommand('#location-level-4', ''));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function getLevelFour(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $parent_level2_tid = $form_state->getValue('level_3');
    $level_2_options = $this->locationService->getChildrenByTid($parent_level2_tid);
    $form['location']['all_wrapper']['location_level']['level_4']['#empty_option'] = $this->t("Select Level 4 Label");
    $form['location']['all_wrapper']['location_level']['level_4']['#options'] = $level_2_options;
    $form['location']['all_wrapper']['location_level']['level_4']['#empty_value'] = '';
    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#location-level-4', $form['location']['all_wrapper']['location_level']['level_4']));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function getLevelFourData(array &$form, FormStateInterface $form_state) {
    return $form['location']['all_wrapper']['location_level']['location_level3'];
  }

  /**
   * {@inheritdoc}
   */
  public function getLocationDetail(array &$form, FormStateInterface $form_state) {
    $location_country_id = $form_state->getValue('location_options');
    $location_entity = $this->entityTypeManager->getStorage('location')->load($location_country_id);
    $location_tid = $location_entity->get('field_location_taxonomy_term')->getValue()[0]['target_id'];
    $location_levels = $this->locationService->getLocationLevels($location_country_id);
    $childs = $this->locationService->getChildrenByTid($location_tid);
    if (!empty($childs)) {
      $i = 1;
      foreach ($childs as $key => $value) {
        if ($i == 1) {
          $level_2_options_final[''] = $this->t('Select Level 1 Label');
          $level_2_options_final[$key] = $value;
        }
        else {
          $level_2_options_final[$key] = $value;
        }
        $i++;
      }
    }
    else {
      $level_2_options_final = $childs;
    }
    $response = new AjaxResponse();

    $form['location']['all_wrapper']['location_level']['level_1']['#options'] = $level_2_options_final;
    $form['location']['all_wrapper']['location_level']['level_1']['#empty_option'] = $this->t("Select Level 1 Label");
    $form['location']['all_wrapper']['location_level']['level_1']['#empty_value'] = '';
    $form['location']['all_wrapper']['location_level']['level_1']['#title'] = $location_levels[0];
    unset($form['location']['all_wrapper']['location_level']['level_2']);
    unset($form['location']['all_wrapper']['location_level']['level_3']);
    unset($form['location']['all_wrapper']['location_level']['level_4']);
    $response->addCommand(new HtmlCommand('#edit-location-details', $form['location']['all_wrapper']));
    $response->addCommand(new HtmlCommand('#form-actions', $form['action-wrapper']['actions']));
    return $response;

  }

  /**
   * Sets an error if supplied fields has not been filled.
   */
  public function validatePageTwo(array &$form, FormStateInterface $form_state) {
    if (empty($form_state->getValue('location_options'))) {
      $form_state->setErrorByName('location_options', $this->t('Please fill the required fields'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function pageOneBack(array &$form, FormStateInterface $form_state) {
    $form_state
      ->setValues($form_state->get('page_values'))
      ->set('page', 1)
      ->setRebuild(TRUE);

  }

  /**
   * {@inheritdoc}
   */
  public function submitPageTwo(array &$form, FormStateInterface $form_state) {
    $location_tid = '';
    if (!empty($form_state->getValue('level_4'))) {
      $location_tid = $form_state->getValue('level_4');
    }
    elseif (!empty($form_state->getValue('level_3'))) {
      $location_tid = $form_state->getValue('level_3');
    }
    elseif (!empty($form_state->getValue('level_2'))) {
      $location_tid = $form_state->getValue('level_2');
    }
    $form_state->set('page_two_values', [
      'personal_details' => $form_state->get('page_values'),
      'location_tid' => $location_tid,
    ])
      ->set('page', 3)
      ->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function formPageThree(array &$form, FormStateInterface $form_state) {
    $form['progress_step1'] = [
      '#markup' => '<div class="steps-highlight">
        <div class="personal-detail-page step-circle">' . $this->t('<div class="step-number">1</div>') .
      '</div>',
    ];
    $form['progress_step2'] = [
      '#markup' => '<div class="location-detail-page active step-circle">' .
      $this->t('<div class="step-number">2</div>') .
      '</div>',
    ];
    $form['progress_step3'] = [
      '#markup' => '<div class="password-creation-page active step-circle">' .
      $this->t('<div class="step-number">3</div>') .
      '</div></div>',
    ];

    $form['message-step'] = [
      '#markup' => '<div class="step">' . $this->t('Step 3: Password') . '</div>',
    ];

    if ($this->currentUser->id()) {
      $form['message-info'] = [
        '#prefix' => '<div class="password-creation">',
        '#markup' => '<div class="notify-messsage">' .
        $this->t('How would you like to send the password creation link to the new user?') .
        '</div>',
      ];
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('VIA EMAIL'),
        '#attributes' => [
          'class' => [
            'use-ajax',
          ],
        ],
        '#ajax' => [
          'callback' => [$this, 'requestActivationMail'],
          "wrapper" => "requestActivationMail",
          'event' => 'click',
        ],
        '#suffix' => '</div>',
      ];
    }
    else {
      $form['#prefix'] = '<div id="status-message"></div>';
      $values = $form_state->get('page_values');
      $form['email'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Email'),
        '#required' => TRUE,
        '#placeholder' => $this->t('Enter email id'),
        '#disabled' => TRUE,
        '#default_value' => $values['email'],
      ];
      $form['password'] = [
        '#type' => 'password',
        '#title' => $this->t('Password'),
        '#required' => TRUE,
        '#placeholder' => $this->t('**********'),
        '#description' => '<span class="help-text">' . $this->t('i') . '</span>',
      ];
      $form['confirm_password'] = [
        '#type' => 'password',
        '#title' => $this->t('Confirm password'),
        '#required' => TRUE,
        '#placeholder' => $this->t('**********'),
      ];
      $form['back'] = [
        '#type' => 'submit',
        '#value' => $this->t('Back'),
        '#submit' => ['::pageTwoBack'],
        '#limit_validation_errors' => [],
      ];
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('REQUEST REGISTRATION'),
        '#attributes' => [
          'class' => [
            'use-ajax',
            'arrow-btn',
          ],
        ],
        '#ajax' => [
          'callback' => [$this, 'requestRegistration'],
          "wrapper" => "requestregistration",
          'event' => 'click',
        ],
      ];
    }
    $form['#cache']['max-age'] = 0;
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attached']['library'][] = 'erpw_custom/erpw_js';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $password = $form_state->getValue('password');
    $confirm_password = $form_state->getValue('confirm_password');
    if ($password && strcmp($password, $confirm_password)) {
      $form_state->setErrorByName('password', $this->t('The specified passwords do not match.'));
    }
    if ($password && !preg_match("/^(?=.*\d)(?=.*[!@#$%^&*])(?=.*[a-z]).{8,64}$/", $password)) {
      $form_state->setErrorByName('password', $this->t('Password should contain at least one Number, one Symbol and one alphabet'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function pageTwoBack(array &$form, FormStateInterface $form_state) {
    $form_state
      ->setValues($form_state->get('page_two_values'))
      ->set('page', 2)
      ->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function requestRegistration(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if ($form_state->hasAnyErrors()) {
      $errors = $form_state->getErrors();
      $command_content = $errors['password'];
      $response->addCommand(new InvokeCommand(
        '#status-message', 'addClass', ['messages messages--error']));
      $response->addCommand(new HtmlCommand('#status-message', $command_content));
    }
    else {
      $form_state->clearErrors();
      $form_state->setRebuild(TRUE);
      $values = $form_state->get('page_values');
      $location_values = $form_state->get('page_two_values');
      $user_info = [
        'status' => 1,
        'name' => $values['email'],
        'pass' => $form_state->getValue('password'),
        'mail' => $values['email'],
        'field_first_name' => $values['first_name'],
        'field_last_name' => $values['last_name'],
        'field_phone' => $values['phone'],
        'field_organisation' => $values['organisation'],
        'field_position' => $values['position'],
        'field_location_details' => $location_values['location_tid'],
        'roles' => $values['system_role'],
      ];
      $user = $this->entityTypeManager->getStorage('user')->create($user_info);
      $user->save();
      $response = new AjaxResponse();
      $modal_form = $this->formBuilder->getForm('Drupal\erpw_custom\Form\ModalForm');
      // Add an AJAX command to open a modal dialog with the form as content.
      $response->addCommand(new OpenModalDialogCommand('', $modal_form, ['width' => '400']));
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function requestActivationMail(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if ($form_state->hasAnyErrors()) {
      $errors = $form_state->getErrors();
      $command_content = $errors['password'];
      $response->addCommand(new InvokeCommand(
        '#status-message', 'addClass', ['messages messages--error']));
      $response->addCommand(new HtmlCommand('#status-message', $command_content));
    }
    else {
      $form_state->clearErrors();
      $form_state->setRebuild(TRUE);
      $values = $form_state->get('page_values');
      $location_values = $form_state->get('page_two_values');
      $user_info = [
        'status' => 0,
        'name' => $values['email'],
        'pass' => 'password',
        'mail' => $values['email'],
        'field_first_name' => $values['first_name'],
        'field_last_name' => $values['last_name'],
        'field_phone' => $values['phone'],
        'field_organisation' => $values['organisation'],
        'field_position' => $values['position'],
        'field_location_details' => $location_values['location_tid'],
        'roles' => $values['system_role'],
      ];
      $user = $this->entityTypeManager->getStorage('user')->create($user_info);
      $user->save();
      _user_mail_notify('register_pending_approval', $user);
      $response = new AjaxResponse();
      $modal_form = $this->formBuilder->getForm('Drupal\erpw_custom\Form\ModalForm');
      // Add an AJAX command to open a modal dialog with the form as content.
      $response->addCommand(new OpenModalDialogCommand('', $modal_form, ['width' => '400']));
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger->deleteAll();
  }

}
