<?php

namespace Drupal\erpw_location\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\erpw_location\LocationService;

/**
 * ModalForm class.
 */
class DeleteLocationForm extends FormBase {


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
   * A entityTypeManager instance.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * A currentPathStack instance.
   *
   * @var Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPathStack;

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
    return 'delete_location_forms';
  }

  /**
   * ManageLocation constructor.
   *
   * @param \Psr\Log\LoggerChannelFactory $logger
   *   Logger object.
   * @param \Drupal\Core\Database\Connection $connection
   *   Connection Object.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   EntityManager object.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form_builder service.
   * @param Drupal\Core\Path\CurrentPathStack $currentPathStack
   *   The location service.
   * @param \Drupal\erpw_location\LocationService $location_service
   *   The location service.
   */
  public function __construct(LoggerChannelFactory $logger, Connection $connection, EntityTypeManagerInterface $entityTypeManager, MessengerInterface $messenger, FormBuilderInterface $form_builder, CurrentPathStack $currentPathStack, LocationService $location_service) {
    $this->logger = $logger;
    $this->connection = $connection;
    $this->entityTypeManager = $entityTypeManager;
    $this->messenger = $messenger;
    $this->formBuilder = $form_builder;
    $this->currentPathStack = $currentPathStack;
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
      $container->get('path.current'),
      $container->get('erpw_location.location_services'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
    $contry_name = NULL;
    $query = \Drupal::entityQuery('location');
    $query->condition('status', 1);
    $query->condition('type', 'country');
    $entity_ids = $query->execute();
    foreach ($entity_ids as $value) {
      $entity['entity_id'] = $value;
    }
    $current_path = $this->currentPathStack->getPath();
    $curr_path = explode("/", $current_path);
    $ancestors = $this->entityTypeManager->getStorage('taxonomy_term')->loadAllParents($curr_path[2]);
    $ancestors = array_reverse(array_keys($ancestors));
    $country_term_ = $this->entityTypeManager->getStorage('taxonomy_term')->load($ancestors[0]);
    $country_term_name = $country_term_->get('name')->value;
    $country_label = t('Country name');
    $contry_name .= '<div class="country-name">' . $country_label . " *: " . $country_term_name . '</div>';
    $location_levels = $this->locationService->getLocationLevels($entity['entity_id']);
    $location_details = '';
    foreach ($location_levels as $key => $level) {
      $level_term = $this->entityTypeManager->getStorage('taxonomy_term')->load($ancestors[$key + 1]);
      $level_data_name = $level_term->get('name')->value;
      $location_details .= '<div class="level">' . $level . " *: " . $level_data_name . '</div>';
    }
    $form['tid'] = [
      '#type' => 'hidden',
      '#value' => $curr_path[2],
    ];
    $form['location_values1'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="review-msg">',
      '#markup' => $contry_name,
      '#suffix' => '</div>',
    ];
    $form['location_values'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="review-msg">',
      '#markup' => $location_details,
      '#suffix' => '</div>',
    ];
    $form['actions']['delete_location'] = [
      '#type' => 'submit',
      '#value' => $this->t('DELETE LOCATION'),
      '#attributes' => [
        'class' => [
          'use-ajax',
          'ok-btn',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'deleteLocation'],
        'event' => 'click',
      ],
    ];

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attached']['library'][] = 'erpw_location/erpw_location_js';
    return $form;
  }

  /**
   * AJAX callback handler that displays any errors or a success message.
   */
  public function deleteLocation(array $form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $response = new AjaxResponse();
    $term = Term::load($values['tid']);
    if (!empty($term)) {
      $term->delete();
    }
    // Get the modal form using the form builder.
    $location_popup_form = $this->formBuilder->getForm('Drupal\erpw_location\Form\LocationPopupForm');

    // Add an AJAX command to open a modal dialog with the form as the content.
    $response->addCommand(new OpenModalDialogCommand('', $location_popup_form, ['width' => '400']));

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['config.modal_form_example_modal_form'];
  }

}
