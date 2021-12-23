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
use Drupal\Core\Render\Markup;
use Drupal\Core\Link;
use Drupal\Core\Url;

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
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sign_up';
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $database, EntityTypeManagerInterface $entityTypeManager, AccountProxyInterface $current_user, MessengerInterface $messenger) {
    $this->database = $database;
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $current_user;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('messenger')
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
      '#empty_option' => t('Select system roles'),
      '#title' => $this->t('System role'),
      '#required' => TRUE,
      '#placeholder' => t('Select system role'),
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
      'positon' => $form_state->getValue('positon'),
      'system_role' => $form_state->getValue('system_role'),
    ])
      ->set('page', 2)
      ->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function formPageTwo(array &$form, FormStateInterface $form_state) {

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

    $form['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
      '#placeholder' => t('Enter email id'),
      '#disabled' => TRUE,
      '#default_value' => $form_state->getValue('email'),
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
      $form_state->setErrorByName('password', t('Password should contain one Number,  one letter, one special symbol (min Length 8 Character)'));
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
        '#status-message', 'addClass', ['messages', 'messages--error']));
      $response->addCommand(new HtmlCommand('#status-message', $command_content));
    }
    else {
      $form_state->clearErrors();
      unset($form_errors['password']);
      $values = $form_state->get('page_values');
      $user_info = [
        'status' => 1,
        'name' => $values['email'],
        'pass' => $form_state->getValue('password'),
        'mail' => $values['email'],
        'field_first_name' => $values['first_name'],
        'field_last_name' => $values['last_name'],
        'field_phone' => $values['phone'],
        'field_organisation' => $values['organisation'],
        'field_position' => $values['positon'],
        'roles' => $values['system_role'],
      ];
      $user = $this->entityTypeManager->getStorage('user')->create($user_info);
      $user->save();
      $link_options = [
        'attributes' => [
          'class' => [
            'button',
            'bg-green',
          ],
        ],
      ];
      $url = Url::fromRoute('<front>');
      $url->setOptions($link_options);
      $link = Link::fromTextAndUrl('OK', $url)->toString();
      $message = $this->t("<div class='review-msg'>Your registration has been <br/> sent for review.</div><div class='email-notify'> You will be notified via email, once your registration approved.</div>");
      $popup_msg = Markup::create($message . ' ' . $link);
      $response = $response->addCommand(new OpenModalDialogCommand("", $popup_msg, ['width' => 400]));
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
