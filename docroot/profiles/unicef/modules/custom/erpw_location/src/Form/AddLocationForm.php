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
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;

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
   * A cid variable.
   *
   * @var Drupal\erpw_location
   */
  protected $cid;

  /**
   * ManageLocation constructor.
   *
   * @param \Psr\Log\LoggerChannelFactory $logger
   *   Logger object.
   * @param \Drupal\Core\Database\Connection $connection
   *   Connection Object.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityManager object.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form_builder service.
   * @param \Drupal\erpw_location\LocationService $location_service
   *   The location service.
   */
  public function __construct(LoggerChannelFactory $logger,
    Connection $connection,
    EntityTypeManagerInterface $entity_type_manager,
    MessengerInterface $messenger,
    FormBuilderInterface $form_builder,
    LocationService $location_service) {
    $this->logger = $logger;
    $this->connection = $connection;
    $this->entityManager = $entity_type_manager->getStorage('location');
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
    return 'add_location';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $location_options = $this->locationService->getLocationEntities();
    if (!empty($location_options)) {
      $form['location_options'] = [
        '#type' => 'select',
        '#options' => $location_options,
        '#empty_option' => $this->t('Select Country'),
        // '#required' => TRUE,
        '#title' => $this->t('Country name'),
        '#ajax' => [
          'callback' => '::ajaxCallback',
          'wrapper' => 'all-wrapper',
        ],
      ];
    }
    $form_state->setCached(FALSE);

    $form['all_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'all-wrapper', 'class' => 'location-container'],
    ];

    $form['all_wrapper']['intro_text'] = [
      '#type' => 'markup',
      '#markup' => '<div id="intro-text">' . $this->t('Please select the country to fill the location details') . '</div>',
    ];
    if (!empty($form_state->getValue('location_options'))) {
      $form['all_wrapper']['location-container-heading'] = [
        '#type' => 'markup',
        '#markup' => '<div class="location-container-heading">' . $this->t('Add the new location details') . '</div>',
      ];
    }
    $form['all_wrapper']['level1_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'level1-wrapper', 'class' => 'location-level'],
    ];
    $form['all_wrapper']['level2_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'level2-wrapper', 'class' => 'location-level'],
    ];
    $form['all_wrapper']['level3_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'level3-wrapper', 'class' => 'location-level'],
    ];
    $form['all_wrapper']['level2_wrapper']['error_text'] = [
      '#type' => 'markup',
      '#markup' => '<div id="error-text"></div>',
    ];
    $form['all_wrapper']['level3_wrapper']['error_text'] = [
      '#type' => 'markup',
      '#markup' => '<div id="error-text2"></div>',
    ];
    $form['all_wrapper']['level4_wrapper']['error_text'] = [
      '#type' => 'markup',
      '#markup' => '<div id="error-text3"></div>',
    ];
    if ($form_state->getValue('location_options') != FALSE) {
      unset($form['all_wrapper']['intro_text']);
      $location = $this->entityManager->load($form_state->getValue('location_options'));
      $location_levels = $this->locationService->getLocationLevels($form_state->getValue('location_options'));
      $this->cid = $location->get('field_location_taxonomy_term')->target_id;
      if (isset($location_levels[0]) && !empty($location_levels[0])) {
        $form['all_wrapper']['level1_wrapper']['level1'] = [
          '#type' => 'textfield',
          '#title' => $location_levels[0],
          '#autocomplete_route_name' => 'erpw_location.autocomplete',
        // '#required' => TRUE,
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
            'placeholder' => $this->t('Plese enter') . " " . $location_levels[0],
          ],
        ];
      }
      if ($form_state->getValue('level1')) {
        $level1int = $this->locationService->clean($form_state->getValue('level1'));
      }
      else {
        $level1int = -1;
      }
      if (isset($location_levels[1]) && !empty($location_levels[1])) {
        $readonly_level2 = !empty($form_state->getValue('level1')) ? FALSE : 'readonly';
        $form['all_wrapper']['level2_wrapper']['level2'] = [
          '#type' => 'textfield',
          '#title' => $location_levels[1],
          '#autocomplete_route_name' => 'erpw_location.autocomplete',
          '#autocomplete_route_parameters' => ['tid' => $level1int],
        // '#required' => TRUE,
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
            'placeholder' => $this->t('Plese enter') . " " . $location_levels[1],
            // 'readonly' => $readonly_level2,
          ],

        ];
      }
      if ($form_state->getValue('level2')) {
        $level2int = $this->locationService->clean($form_state->getValue('level2'));
      }
      else {
        $level2int = -1;
      }
      if (isset($location_levels[2]) && !empty($location_levels[2])) {
        $readonly_level3 = !empty($form_state->getValue('level2')) ? FALSE : 'readonly';
        $form['all_wrapper']['level3_wrapper']['level3'] = [
          '#type' => 'textfield',
          '#title' => $location_levels[2],
          '#autocomplete_route_name' => 'erpw_location.autocomplete',
        // '#required' => TRUE,
          '#autocomplete_route_parameters' => ['tid' => $level2int],
          '#attributes' => [
            'class' => [
              'mycategory',
            ],
            'placeholder' => $this->t('Plese enter') . " " . $location_levels[2],
            // 'readonly' => $readonly_level3,
          ],
        ];
      }
      if (isset($location_levels[3]) && !empty($location_levels[3])) {
        $readonly_level4 = !empty($form_state->getValue('level3')) ? FALSE : 'readonly';
        $form['all_wrapper']['level4_wrapper']['level4'] = [
          '#type' => 'textfield',
          '#title' => $location_levels[3],
        // '#required' => TRUE,
          '#attributes' => [
            'class' => [
              'mycategory',
            ],
            'placeholder' => $this->t('Plese enter') . " " . $location_levels[3],
            // 'readonly' => $readonly_level4,
          ],
        ];
      }

    }
    $form['ajax_wrapper']['save_draft'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save as Draft'),
      '#attributes' => [
        'class' => [
          'button-border',
        ],
      ],
      '#ajax' => [
        'callback' => '::sendMessageForm',
      ],
    ];
    $form['ajax_wrapper']['button'] = [
      '#type' => 'submit',
      '#value' => $this->t('Publish'),
      '#ajax' => [
        'callback' => '::sendMessageForm',
      ],
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
    $form['#disable_inline_form_errors_summary'] = TRUE;

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
    // parent::validateForm($form, $form_state);.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function sendMessageForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if (empty($form_state->getValue('location_options'))) {
      $response->addCommand(new HtmlCommand('#intro-text',
      $this->t('Please select the country to fill the location details.')));
      $response->addCommand(new InvokeCommand('#intro-text',
      'css', ["color", "red"]));
      return $response;
    }
    if (empty($form_state->getValue('level1'))) {
      $response->addCommand(new HtmlCommand('#error-text',
      $form['all_wrapper']['level1_wrapper']['level1']['#title'] . "  " . ' field is required.'));
      $response->addCommand(new InvokeCommand('#error-text',
      'css', ["color", "red"]));
      return $response;
    }
    if (empty($form_state->getValue('level2'))) {
      $response->addCommand(new HtmlCommand('#error-text2',
      $form['all_wrapper']['level2_wrapper']['level2']['#title'] . " " . ' field is required.'));
      $response->addCommand(new InvokeCommand('#error-text2',
      'css', ["color", "red"]));
      return $response;
    }
    if (empty($form_state->getValue('level3'))) {
      $response->addCommand(new HtmlCommand('#error-text3',
      $form['all_wrapper']['level3_wrapper']['level3']['#title'] . " " . ' field is required.'));
      $response->addCommand(new InvokeCommand('#error-text3',
      'css', ["color", "red"]));
      return $response;
    }

    // State.
    if ($form_state->getValue('level1')) {
      $level1_tid = $this->locationService->processTaxonomyData($form_state->getValue('level1'), $this->cid);
    }
    // City.
    if ($form_state->getValue('level2')) {
      $level2_tid = $this->locationService->processTaxonomyData($form_state->getValue('level2'), $level1_tid);
    }
    // District.
    if ($form_state->getValue('level3')) {
      $level3_tid = $this->locationService->processTaxonomyData($form_state->getValue('level3'), $level2_tid);
    }
    if ($form_state->getValue('level4')) {
      $level4_tid = $this->locationService->processTaxonomyData($form_state->getValue('level4'), $level3_tid, 4);
    }
    $modal_form = $this->formBuilder->getForm('Drupal\erpw_custom\Form\AddLocationPopup');
    $response->addCommand(new InvokeCommand('.messages',
    'css', ["color", "green"]));
    $response->addCommand(new OpenModalDialogCommand('', $modal_form, ['width' => '400']));
    return $response;
  }

}
