<?php

namespace Drupal\erpw_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Messenger\MessengerInterface;

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
    $user_entity = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    $roles = $user_entity->getRoles();

    if ($form_state->has('page') && $form_state->get('page') == 2) {
      return self::formPageTwo($form, $form_state);
    }

    $form_state->set('page', 1);

    $form['description'] = [
      '#type' => 'item',
      '#title' => $this->t('Page @page', ['@page' => $form_state->get('page')]),
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
      '#placeholder' => t('Enter email name'),
    ];

    $form['phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone'),
      '#required' => TRUE,
      '#placeholder' => t('**********'),
    ];

    $form['organisation'] = [
      '#type' => 'textfield',
      'options' => $roles,
      '#title' => $this->t('Organisation'),
      '#required' => TRUE,
      '#placeholder' => t('Select organisation'),
    ];

    $form['positon'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Positon'),
      '#required' => TRUE,
      '#placeholder' => t('Select position'),
    ];

    $form['system_role'] = [
      '#type' => 'select',
      '#options' => $roles,
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

    $form['description'] = [
      '#type' => 'item',
      '#title' => $this->t('Page @page', ['@page' => $form_state->get('page')]),
    ];
    $form['message-step'] = [
      '#markup' => '<div class="step">' . $this->t('Step 3: Password') . '</div>',
    ];

    $form['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
      '#placeholder' => t('Enter email id'),
      '#disabled' => TRUE,
      '#default_value' => $form_state->getValue('email'),
    ];
    $form['password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Password'),
      '#required' => TRUE,
      '#placeholder' => t('**********'),
    ];
    $form['confirm_password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Confirm password'),
      '#required' => TRUE,
      '#placeholder' => t('**********'),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Submit'),
      '#attributes' => [
        'class' => [
          'use-ajax',
        ],
      ],
    ];
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
    $pass = $form_state->getValue('password');
    if (empty($pass)) {
      $form_state->setErrorByName('password', t('Password and Confirm password is required'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->get('page_values');
    $firstname = $values['first_name'];
    $lastname = $values['last_name'];
    $email = $values['email'];
    $phone = $values['phone'];
    $organisation = $values['organisation'];
    $position = $values['positon'];
    $systemrole = $values['system_role'];

    $user_info = [
      'status' => 0,
      'name' => $values['first_name'],
      'pass' => $form_state->getValue('password'),
      'mail' => $values['email'],
      'field_first_name' => $values['first_name'],
      'field_last_name' => $values['last_name'],
      'field_phone' => $values['phone'],
      'field_organisation' => $values['organisation'],
      'field_position' => $values['positon'],
      'field_system_roles' => $values['system_role'],
      'roles' => ['authenticated'],
    ];
    $user = $this->entityTypeManager->getStorage('user')->create($user_info);
    $user->save();
    $form_state->setRedirect('erpw_custom.message');
  }

}
