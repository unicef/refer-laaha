<?php

namespace Drupal\erpw_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Class SignUpForm.
 */
class SignUpForm extends FormBase {

  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sign_up';
  }

  public function __construct(Connection $database,EntityTypeManagerInterface $entityTypeManager,AccountProxyInterface $current_user) {
    $this->database = $database;
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $current_user;
  }
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $user_entity = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    $roles = $user_entity->getRoles(); 
    
      $form['message-step'] = [
        '#markup' => '<div class="step">' . $this->t('Step 1: Personal details') . '</div>',
      ];

      $form['first_name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('First name'),
        '#required' => TRUE,
        '#attributes' => ['placeholder' => t('Enter first name'),]
      ];

      $form['last_name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Last name'),
        '#required' => TRUE,
        '#attributes' => ['placeholder' => t('Enter last name'),]
      ];

      $form['email'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Email'),
        '#required' => TRUE,
        '#attributes' => ['placeholder' => t('Example@gmail.com'),]
      ];
      
      $form['phone'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Phone'),
        '#required' => TRUE,
        '#attributes' => ['placeholder' => t('**********'),]
      ];

      $form['organisation'] = [
        '#type' => 'textfield',
        '#options' => $roles,
        '#title' => $this->t('Organisation'),
        '#required' => TRUE,
        '#attributes' => ['placeholder' => t('Select organisation'),]
      ];

      $form['positon'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Positon'),
        '#required' => TRUE,
        '#attributes' => ['placeholder' => t('Select position'),]
      ];

      $form['system_role'] = [
        '#type' => 'select',
        '#options' => $roles,
        '#empty_option' => t('Select system roles'),
        '#title' => $this->t('System role'),
        '#required' => TRUE,
        '#attributes' => ['placeholder' => t('Select system role'),],
      ];
      $form['actions'] = [
        '#type' => 'actions',
      ];
    
      $form['actions']['next'] = [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => $this->t('Next'),
        '#submit' => ['::submitPageOne'],
      ];
    
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    return parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}