<?php

namespace Drupal\erpw_location\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\erpw_location\LocationService;

/**
 * Class for add location.
 */
class AddLocationForm extends FormBase {

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerChannelFactory
   */
  protected $logger;

  /**
   * Database Connection instance.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * A entityManager instance.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * A LocationService instance.
   *
   * @var Drupal\erpw_location\LocationService
   */
  protected $locationService;

  /**
   * ManageLocation constructor.
   *
   * @param \Psr\Log\LoggerChannelFactory $logger
   *   Logger object.
   * @param \Drupal\Core\Database\Connection $connection
   *   Connection Object.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entityManager
   *   EntityManager object.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form_builder service.
   * @param \Drupal\erpw_location\LocationService $location_service
   *   The location service.
   */
  public function __construct(LoggerChannelFactory $logger, Connection $connection, EntityTypeManagerInterface $entityManager, MessengerInterface $messenger, FormBuilderInterface $form_builder, LocationService $location_service) {
    $this->logger = $logger;
    $this->connection = $connection;
    $this->entityManager = $entityManager->getStorage('location');
    $this->messenger = $messenger;
    $this->formBuilder = $form_builder;
    $this->locationService = $location_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory'),
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('messenger'),
      $container->get('form_builder'),
      $container->get('erpw_location.location_services'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'add_new_location';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $location_entities = $this->entityManager->loadByProperties(
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
        '#ajax' => [
          'callback' => '::ajaxCallback',
          'wrapper' => 'all-wrapper',
        ],
      ];
    }
    $form_state->setCached(FALSE);
    $form['all_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'all-wrapper'],
    ];
    $form['all_wrapper']['intro_text'] = [
      '#type' => 'markup',
      '#markup' => '<div id="intro-text">' . $this->t('Select the county first, to view the respective form') . '</div>',
    ];
    $form['all_wrapper']['level1_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'level1-wrapper'],
    ];
    $form['all_wrapper']['level2_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'level2-wrapper'],
    ];
    $form['all_wrapper']['level3_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'level3-wrapper'],
    ];
    if ($form_state->getValue('location_options') != FALSE) {
      unset($form['all_wrapper']['intro_text']);
      $location = $this->entityManager->load($form_state->getValue('location_options'));
      $form['all_wrapper']['level1_wrapper']['level1'] = [
        '#type' => 'textfield',
        '#title' => $location->get('level_1')->value,
        '#autocomplete_route_name' => 'erpw_location.autocomplete',
        '#autocomplete_route_parameters' => ['tid' => $location->get('field_location_taxonomy_term')->target_id],
        '#ajax' => [
          'callback' => '::deptFilterSelect',
          'event' => 'autocompleteclose',
          'progress' => FALSE,
          'wrapper' => 'level2-wrapper',
        ],
        '#attributes' => [
          'class' => [
            'mycategory',
          ],
        ],
      ];
      if ($form_state->getValue('level1')) {
        $level1int = $this->locationService->clean($form_state->getValue('level1'));
      }
      else {
        $level1int = -1;
      }
      $form['all_wrapper']['level2_wrapper']['level2'] = [
        '#type' => 'textfield',
        '#title' => $location->get('level_2')->value,
        '#autocomplete_route_name' => 'erpw_location.autocomplete',
        '#autocomplete_route_parameters' => ['tid' => $level1int],
        '#ajax' => [
          'callback' => '::ajaxCallback3',
          'event' => 'autocompleteclose',
          'progress' => FALSE,
          'wrapper' => 'level3-wrapper',
        ],
        '#attributes' => [
          'class' => [
            'mycategory',

          ],
        ],
      ];
      if ($form_state->getValue('level2')) {
        $level2int = $this->locationService->clean($form_state->getValue('level2'));
      }
      else {
        $level2int = -1;
      }
      $form['all_wrapper']['level3_wrapper']['level3'] = [
        '#type' => 'textfield',
        '#title' => $location->get('level_3')->value,
        '#autocomplete_route_name' => 'erpw_location.autocomplete',
        '#autocomplete_route_parameters' => ['tid' => $level2int],
        '#attributes' => [
          'class' => [
            'mycategory',
          ],
        ],
      ];
      $form['all_wrapper']['level4_wrapper']['level4'] = [
        '#type' => 'textfield',
        '#title' => $location->get('level_4')->value,
        '#attributes' => [
          'class' => [
            'mycategory',
          ],
        ],
      ];

    }
    $form['ajax_wrapper']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Publish'),
    ];
    $form['#cache']['max-age'] = 0;
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    return $form;

  }

  /**
   * Ajax callback to add location.
   */
  public function ajaxCallback(array &$form, FormStateInterface $form_state) {
    unset($form['all_wrapper']['intro_text']);
    $form['all_wrapper']['level1_wrapper']['level1']['#value'] = "";
    $form['all_wrapper']['level2_wrapper']['level2']['#value'] = "";
    $form['all_wrapper']['level3_wrapper']['level3']['#value'] = "";
    return $form['all_wrapper'];
  }

  /**
   * Ajax callback to add location.
   */
  public function deptFilterSelect(array &$form, FormStateInterface $form_state) {
    return $form['all_wrapper']['level2_wrapper'];
  }

  /**
   * Ajax callback to add location.
   */
  public function ajaxCallback3(array &$form, FormStateInterface $form_state) {
    return $form['all_wrapper']['level3_wrapper'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
