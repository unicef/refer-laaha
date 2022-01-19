<?php

namespace Drupal\erpw_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormBuilderInterface;

/**
 * Class SignUpForm.
 */
class SignUpForm extends FormBase {

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
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sign_up';
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $database, EntityTypeManagerInterface $entityTypeManager, AccountProxyInterface $current_user, MessengerInterface $messenger, FormBuilderInterface $form_builder) {
    $this->database = $database;
    $this->entityTypeManager = $entityTypeManager;
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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $roles = $this->entityTypeManager->getStorage('user_role')->loadMultiple();
    foreach ($roles as $role) {
      if ($role->id() == 'administrator' || $role->id() == 'anonymous') {
        continue;
      }
      $system_roles[$role->id()] = $role->label();
    }

    if ($form_state->has('page') && $form_state->get('page') == 2) {
      return self::formPageTwo($form, $form_state);
    }

    if ($form_state->has('page') && $form_state->get('page') == 3) {
      return self::formPageThree($form, $form_state);
    }

    $form_state->set('page', 1);

    $form['progress_step1'] = [
      '#markup' => '<div class="steps-highlight"><div class="personal-detail-page step-circle">' . $this->t('<div class="step-number">1</div>') . '</div>',
    ];
    $form['progress_step2'] = [
      '#markup' => '<div class="location-detail-page step-circle">' . $this->t('<div class="step-number">2</div>') . '</div>',
    ];
    $form['progress_step3'] = [
      '#markup' => '<div class="password-creation-page step-circle">' . $this->t('<div class="step-number">3</div>') . '</div></div>',
    ];

    $form['message-step'] = [
      '#markup' => '<div class="step">' . $this->t('Step 1: Personal details') . '</div>',
    ];

    $form['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First name'),
      '#required' => TRUE,
      '#placeholder' => t('Enter first name'),
    ];

    $form['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last name'),
      '#required' => TRUE,
      '#placeholder' => t('Enter last name'),
    ];

    $form['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
      '#placeholder' => t('Example@gmail.com '),
    ];

    $form['phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone'),
      '#required' => TRUE,
      '#placeholder' => t('**********'),
    ];

    $organisation = [
      'srijan' => 'Srijan',
      'Infosys' => 'Infosys',
      'wipro' => 'Wipro',
    ];

    $form['organisation'] = [
      '#type' => 'select',
      '#options' => $organisation,
      '#empty_option' => t('Select organization '),
      '#title' => $this->t('Organisation'),
      '#required' => TRUE,
    ];

    $form['position'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Position'),
      '#required' => TRUE,
      '#placeholder' => t('Select position'),
    ];

    $form['system_role'] = [
      '#type' => 'select',
      '#options' => $system_roles,
      '#empty_option' => t('Select system role'),
      '#title' => $this->t('System role'),
      '#required' => TRUE,
    ];

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
    $form['#cache']['max-age'] = 0;
    return $form;
  }

  /**
   * Sets an error if supplied fields has not been filled.
   */
  public function validatePageOne(array &$form, FormStateInterface $form_state) {
    if (!is_numeric($form_state->getValue('phone'))) {
      $form_state->setErrorByName('phone', t('Phone number must be numeric'));
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

  /**
   * {@inheritdoc}
   */
  public function formPageTwo(array &$form, FormStateInterface $form_state) {
    $form['#prefix'] = '<div id="status-message"></div>';
    $form['progress_step1'] = [
      '#markup' => '<div class="steps-highlight"><div class="personal-detail-page step-circle">' . $this->t('<div class="step-number">1</div>') . '</div>',
    ];
    $form['progress_step2'] = [
      '#markup' => '<div class="location-detail-page active step-circle">' . $this->t('<div class="step-number">2</div>') . '</div>',
    ];
    $form['progress_step3'] = [
      '#markup' => '<div class="password-creation-page step-circle">' . $this->t('<div class="step-number">3</div>') . '</div></div>',
    ];

    $form['message-step'] = [
      '#markup' => '<div class="step">' . $this->t('Step 2: Location details') . '</div>',
    ];
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
        '#empty_option' => t('Select Country'),
        '#title' => $this->t('Country'),
        '#required' => TRUE,
        '#ajax' => [
          'callback' => '::getLocationDetail',
          'event' => 'change',
          'wrapper' => 'edit-location-details',
          'progress' => [
            'type' => 'throbber',
          ],
        ],
      ];
    }
    $form['location'] = [
      '#prefix' => '<div id="edit-location-details">',
      '#suffix' => '</div>',
    ];
    if (!empty($form_state->getValue('location_options'))) {
      $form['location']['message'] = [
        '#type' => 'markup',
        '#markup' => '<div id="intro-text">' . $this->t('Add the new location details') . '</div>',
      ];
    }
    $form['location']['intro_text'] = [
      '#type' => 'markup',
      '#markup' => '<div id="intro-text">' . $this->t('Select the county first, to view the respective form') . '</div>',
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
        '#prefix' => '<div id="location-levels">',
        '#suffix' => '</div>',
      ];
      $form['location']['location_level1'] = [
        '#prefix' => '<div id="location-level-1"></div>',
        '#suffix' => '</div>',
      ];
      $form['location']['location_level2'] = [
        '#prefix' => '<div id="location-level-2">',
        '#suffix' => '</div>',
      ];
      $form['location']['location_level3'] = [
        '#prefix' => '<div id="location-level-3">',
        '#suffix' => '</div>',
      ];
      $form['location']['location_level4'] = [
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
            '#weight' => 10,
            '#ajax' => [
              'callback' => '::getLevelTwo',
              'event' => 'change',
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
                '#weight' => 20,
                '#ajax' => [
                  'callback' => '::getLevelThree',
                  'event' => 'change',
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
                    '#weight' => 30,
                  ];
                  $array_keys = array_keys($location_levels);
                  $last_key = end($array_keys);
                  if ($last_key == $key) {
                    $form['location']['location_level']['level_' . ($key + 1)]['#multiple'] = TRUE;
                  }
                  else {
                    $form['location']['location_level']['level_' . ($key + 1)]['#ajax'] = [
                      'callback' => '::getLevelFour',
                      'event' => 'change',
                      'wrapper' => 'location-level-2',
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
                        '#weight' => 40,
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
    $form['actions']['next'] = [
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

    $form['#cache']['max-age'] = 0;
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attached']['library'][] = 'erpw_custom/erpw_js';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getLevelTwo(array &$form, FormStateInterface $form_state) {
    return $form['location']['location_level'];
  }

  /**
   * {@inheritdoc}
   */
  public function getLevelThree(array &$form, FormStateInterface $form_state) {
    return $form['location']['location_level']['level_3'];
  }

  /**
   * {@inheritdoc}
   */
  public function getLevelFour(array &$form, FormStateInterface $form_state) {
    return $form['location']['location_level']['level_4'];
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
    unset($form['locatin']['intro_text']);
    return $form['location'];
  }

  /**
   * Sets an error if supplied fields has not been filled.
   */
  public function validatePageTwo(array &$form, FormStateInterface $form_state) {
    if (empty($form_state->getValue('location_options'))) {
      $form_state->setErrorByName('location_options', t('Please fill the required fileds'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitPageTwo(array &$form, FormStateInterface $form_state) {
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
      '#markup' => '<div class="steps-highlight"><div class="personal-detail-page step-circle">' . $this->t('<div class="step-number">1</div>') . '</div>',
    ];
    $form['progress_step2'] = [
      '#markup' => '<div class="location-detail-page active step-circle">' . $this->t('<div class="step-number">2</div>') . '</div>',
    ];
    $form['progress_step3'] = [
      '#markup' => '<div class="password-creation-page active step-circle">' . $this->t('<div class="step-number">3</div>') . '</div></div>',
    ];

    $form['message-step'] = [
      '#markup' => '<div class="step">' . $this->t('Step 3: Password') . '</div>',
    ];
    $form['#prefix'] = '<div id="status-message"></div>';
    $values = $form_state->get('page_values');
    $form['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
      '#placeholder' => t('Enter email id'),
      '#disabled' => TRUE,
      '#default_value' => $values['email'],
    ];
    $form['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#required' => TRUE,
      '#placeholder' => t('**********'),
      '#description' => '<span class="help-text">' . $this->t('i') . '</span>',
    ];
    $form['confirm_password'] = [
      '#type' => 'password',
      '#title' => $this->t('Confirm password'),
      '#required' => TRUE,
      '#placeholder' => t('**********'),
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
    if (isset($password) && isset($confirm_password) && (strlen($password) > 0 || strlen($confirm_password) > 0)) {
      if (strcmp($password, $confirm_password)) {
        $form_state->setErrorByName('password', t('The specified passwords do not match.'));
      }
    }
    if (!preg_match("/^(?=.*\d)(?=.*[!@#$%^&*])(?=.*[a-z]).{8,64}$/", $password)) {
      $form_state->setErrorByName('password', t('Password should contain at least one Number, one Symbol and one alphabet'));
    }
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
      // Add an AJAX command to open a modal dialog with the form as the content.
      $response->addCommand(new OpenModalDialogCommand('', $modal_form, ['width' => '400']));
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::messenger()->deleteAll();
  }

}
