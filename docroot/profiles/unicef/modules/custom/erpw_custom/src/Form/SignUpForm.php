<?php

namespace Drupal\erpw_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Url;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\erpw_location\LocationService;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\erpw_pathway\Services\ErpwPathwayService;

/**
 * Class user signup form.
 */
class SignUpForm extends FormBase {

  const MAX_LEVEL = 4;

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
   * A erpwpathway variable.
   *
   * @var Drupal\erpw_pathway\Services\ErpwPathwayService
   */
  protected $erpwpathway;

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
    LocationService $location_service,
    ErpwPathwayService $erpwp_athway) {

    $this->database = $database;
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->messenger = $messenger;
    $this->formBuilder = $form_builder;
    $this->locationService = $location_service;
    $this->erpwpathway = $erpwp_athway;
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
      $container->get('erpw_pathway.erpw_location_form'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $this->userId = $id;
    $organisation = "";
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
    if ($form_state->has('page') && $this->userId == "") {
      if ($form_state->get('page') == 3) {
        return self::formPageThree($form, $form_state);
      }
      elseif ($form_state->get('page') == 2) {
        return self::formPageTwo($form, $form_state);
      }
    }
    else {
      $form_state->set('page', 1);
    }

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
    if ($this->userId == "") {
      $form['message-step'] = [
        '#markup' => '<div class="step">' . $this->t('Step 1: Personal details') . '</div>',
      ];
    }
    $form['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Enter first name'),
      '#default_value' => !empty($first_name) ? $first_name : $form_state->getValue('first_name', ''),
    ];

    $form['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Enter last name'),
      '#default_value' => !empty($last_name) ? $last_name : $form_state->getValue('last_name', ''),
    ];

    $form['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Example@gmail.com'),
      '#default_value' => !empty($email) ? $email : $form_state->getValue('email', ''),
    ];

    $form['phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone'),
      '#required' => TRUE,
      '#placeholder' => $this->t('**********'),
      '#default_value' => !empty($field_phone) ? $field_phone : $form_state->getValue('phone', ''),
    ];
    $org = '';
    if (!empty($form_state->getValue('organisation'))) {
      $org = $this->entityTypeManager->getStorage('node')->load($form_state->getValue('organisation'));
    }
    $disabled = '';
    $permission = 'add users of their own location and organisation';
    $current_user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    if ($this->currentUser->id() != 1 && !$current_user->hasRole('administrator') && $current_user->hasPermission($permission)) {
      if ($current_user->hasField('field_organisation') && !$current_user->get('field_organisation')->isEmpty()) {
        $org_id = $current_user->get('field_organisation')->getValue()[0]['target_id'];
        $org = $this->entityTypeManager->getStorage('node')->load($org_id);
        $disabled = 'disabled';
      }
    }
    $form['organisation'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => $this->t('Organisation'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Select Organisation'),
      '#default_value' => $org,
      '#attributes' => [
        $disabled => $disabled,
      ],
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
      '#default_value' => !empty($field_position) ? $field_position : $form_state->getValue('position', ''),
    ];

    $form['system_role'] = [
      '#type' => 'select',
      '#options' => $system_roles,
      '#empty_option' => $this->t('Select System Role'),
      '#title' => $this->t('System Role'),
      '#required' => TRUE,
      '#default_value' => !empty($system_role) ? $system_role : $form_state->getValue('system_role', ''),
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
        if (is_array($form_state->getValue('level_4'))) {
          $location_tid = array_keys($form_state->getValue('level_4'));
        }
        else {
          $location_tid = $form_state->getValue('level_3');
        }

      }
      elseif (!empty($form_state->getValue('level_3'))) {
        if (is_array($form_state->getValue('level_3'))) {
          $location_tid = array_keys($form_state->getValue('level_3'));
        }
        else {
          $location_tid = $form_state->getValue('level_3');
        }

      }
      elseif (!empty($form_state->getValue('level_2'))) {
        if (is_array($form_state->getValue('level_2'))) {
          $location_tid = array_keys($form_state->getValue('level_2'));
        }
        else {
          $location_tid = $form_state->getValue('level_2');
        }
      }
      elseif (!empty($form_state->getValue('level_1'))) {
        if (is_array($form_state->getValue('level_1'))) {
          $location_tid = array_keys($form_state->getValue('level_1'));
        }
        else {
          $location_tid = $form_state->getValue('level_1');
        }

      }
      elseif (!empty($form_state->getValue('location_options'))) {

        $location_country_id = $form_state->getValue('location_options');
        $location_entity = $this->entityTypeManager->getStorage('location')->load($location_country_id);
        $location_tid = $location_entity->get('field_location_taxonomy_term')->getValue()[0]['target_id'];
      }
      $user = $this->entityTypeManager->getStorage('user')->load($this->userId);
      $user->setEmail($form_state->getValue('email'));
      $user->set('field_first_name', $form_state->getValue('first_name'));
      $user->set('field_last_name', $form_state->getValue('last_name'));
      $user->set('field_phone', $form_state->getValue('phone'));
      $user->set('field_organisation', $form_state->getValue('organisation'));
      $user->set('field_position', $form_state->getValue('position'));
      $user->set('roles', $form_state->getValue('system_role'));
      $user->set('field_location', $location_tid);
      $user->save();
    }

  }

  /**
   * {@inheritdoc}
   */
  public function formPageTwo(array &$form, FormStateInterface $form_state, $id = NULL) {
    if (!empty($form_state->getValue('level_0'))) {
      $form['top_wrapper']['all_wrapper']['#prefix'] = '<div class="location-container">';
      $form['top_wrapper']['all_wrapper']['#suffix'] = '</div>';
    }
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
    $current_user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    $location_id = (!$current_user->get('field_location')->isEmpty()) ?
      $current_user->get('field_location')->getValue()[0]['target_id'] : '';
    $ptids = $parent_list = [];
    if (!isset($form_state->getTriggeringElement()['#level'])
      && $current_user->get('uid')->value != 1 && !$current_user->hasRole('administrator')) {
      $parent_list = $this->locationService->getAllAncestors($location_id);
      $permission1 = 'add users of their own location and organisation';
      $permission2 = 'add users of their own location';
      if ($current_user->hasPermission($permission1) || $current_user->hasPermission($permission2)) {
        $ptids = $parent_list;
      }
      elseif ($current_user->hasPermission('add location of their own country')) {
        $ptids = [reset($parent_list)];
      }
    }
    $form = $this->erpwpathway->getLocationForm($form, $form_state, $parent_list, $ptids);
    $form['location']['all_wrapper']['intro_text'] = [
      '#type' => 'markup',
      '#markup' => '<div id="intro-text">' . $this->t('Select country to view its Hierarchy.') . '</div>',
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['action-wrapper'] = [
      '#prefix' => '<div id="form-actions" class="sign-buttons">',
      '#suffix' => '</div>',
    ];
    $form['action-wrapper']['actions']['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      '#attributes' => [
        'class' => [
          'button-border hidden',
        ],
      ],
      '#submit' => ['::pageOneBack'],
      '#limit_validation_errors' => [],
    ];
    $form['action-wrapper']['actions']['next'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Next'),
      '#attributes' => [
        'class' => [
          'signup-next hidden',
        ],
      ],
      '#submit' => ['::submitPageTwo'],
    ];
    $form['#cache']['max-age'] = 0;
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attached']['library'][] = 'erpw_custom/erpw_js';
    return $form;
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
    for ($i = self::MAX_LEVEL; $i >= 0; $i--) {
      $location_tid = $form_state->getValue('level_' . $i);
      if (!empty($location_tid)) {
        break;
      }
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
        '#prefix' => '<div id="status-message" class="password-creation">',
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
    if ($password && !preg_match("/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,64}$/", $password)) {
      $form_state->setErrorByName('password', $this->t('Password should contain at least one number, one symbol, one lowercase and uppercase letter.'));
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
        'status' => 0,
        'name' => $values['email'],
        'pass' => $form_state->getValue('password'),
        'mail' => $values['email'],
        'field_first_name' => $values['first_name'],
        'field_last_name' => $values['last_name'],
        'field_phone' => $values['phone'],
        'field_organisation' => $values['organisation'],
        'field_position' => $values['position'],
        'field_location' => $location_values['location_tid'],
        'roles' => $values['system_role'],
        'field_system_role' => $values['system_role'],
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
        'status' => 1,
        'name' => $values['email'],
        'pass' => 'password',
        'mail' => $values['email'],
        'field_first_name' => $values['first_name'],
        'field_last_name' => $values['last_name'],
        'field_phone' => $values['phone'],
        'field_organisation' => $values['organisation'],
        'field_position' => $values['position'],
        'field_location' => $location_values['location_tid'],
        'roles' => $values['system_role'],
        'field_system_role' => $values['system_role'],
      ];
      $user = $this->entityTypeManager->getStorage('user')->create($user_info);
      $user->save();
      _user_mail_notify('register_pending_approval', $user);
      $response = new AjaxResponse();
      $url = Url::fromRoute('view.user_lists.page_1')->toString();
      $command = new RedirectCommand($url);
      $response->addCommand($command);
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger->deleteAll();
    if ($this->currentUser->id()) {
      $this->messenger->addMessage($this->t('The user is saved successfully.'), 'status');
    }
  }

}
