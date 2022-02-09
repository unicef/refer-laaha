<?php

namespace Drupal\erpw_location\Form;

use Drupal\Core\Form\FormBase;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Database\Connection;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Form\FormStateInterface;
use Drupal\erpw_location\LocationService;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * A UrlService instance.
   *
   * @var Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

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
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityManager object.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form_builder service.
   * @param Drupal\Core\Path\CurrentPathStack $current_path_stack
   *   The location service.
   * @param \Drupal\erpw_location\LocationService $location_service
   *   The location service.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator.
   */
  public function __construct(LoggerChannelFactory $logger,
    Connection $connection,
    EntityTypeManagerInterface $entity_type_manager,
    MessengerInterface $messenger,
    FormBuilderInterface $form_builder,
    CurrentPathStack $current_path_stack,
    LocationService $location_service,
    UrlGeneratorInterface $url_generator) {

    $this->logger = $logger;
    $this->connection = $connection;
    $this->entityTypeManager = $entity_type_manager;
    $this->messenger = $messenger;
    $this->formBuilder = $form_builder;
    $this->currentPathStack = $current_path_stack;
    $this->locationService = $location_service;
    $this->urlGenerator = $url_generator;
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
      $container->get('url_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL, $mode = '') {
    $country_name = NULL;
    $current_path = $this->currentPathStack->getPath();
    $curr_path = explode("/", $current_path);
    $ancestors = $this->entityTypeManager->getStorage('taxonomy_term')->loadAllParents($curr_path[2]);
    $ancestors = array_reverse(array_keys($ancestors));
    $country_term_name = $this->entityTypeManager->getStorage('taxonomy_term')->load($ancestors[0])->get('name')->value;
    $country_label = $this->t('Country name');
    $country_name .= '<div class="detail-row">
      <div class="label-text">' . $country_label . " *: " . '</div>' . '<span>' . $country_term_name . '</span>
    </div>';

    // Get location entity id.
    $query = $this->entityTypeManager->getStorage('location')->getQuery();
    $query->condition('status', 1);
    $query->condition('type', 'country');
    $query->condition('field_location_taxonomy_term', $ancestors[0]);
    $location_entities = $query->execute();
    $location_id = NULL;
    foreach ($location_entities as $location_entity_id) {
      $location_id = $location_entity_id;
    }
    $location_levels = $this->locationService->getLocationLevels($location_id);
    $location_details = '';
    foreach ($location_levels as $key => $level) {
      $level_term = $this->entityTypeManager->getStorage('taxonomy_term')->load($ancestors[$key + 1]);
      $level_data_name = $level_term->get('name')->value;
      $location_details .= '<div class="detail-row">
        <div class="label-text">' . $level . " *: " . '</div>' . '<span>' . $level_data_name . '</span>
      </div>';
    }
    $form['tid'] = [
      '#type' => 'hidden',
      '#value' => $curr_path[2],
    ];
    $form['top_parent'] = [
      '#type' => 'hidden',
      '#value' => $location_id,
    ];
    if ($mode == 'view') {
      // @todo url routes to be updated.
      $clone_url = $this->urlGenerator->generateFromRoute('erpw_location.manage_location');
      $delete_url = $this->urlGenerator->generateFromRoute('erpw_location.delete_location',
          ['tid' => $curr_path[2]]
      );
      $edit_url = $this->urlGenerator->generateFromRoute('erpw_location.edit_location',
        ['id' => $curr_path[2], 'mode' => 'view']
      );

      $location_operations = '<div class="edit-delete-links margin-space">
        <span class="clone-service-type"><a href="' . $clone_url . '">' . $this->t('Clone') . '</a></span>
        <span class="delete-link"><a href="' . $delete_url . '">' . $this->t('Delete') . '</a></span>
        <span class="edit-link"><a href="' . $edit_url . '">' . $this->t('Edit') . '</a></span>
      </div>';
      $form['location_list']['location_' . $curr_path[2]] = [
        '#type' => 'markup',
        '#markup' => '<div class="location-card"><div class="title-with-icons">
          <div class="location-operations">' . $location_operations .
        '</div></div></div> ',
      ];
    }
    $form['location_values1'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="delete-screen">',
      '#markup' => $country_name,
      '#suffix' => '</div>',
    ];
    $form['location_values'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="delete-screen">',
      '#markup' => $location_details,
      '#suffix' => '</div>',
    ];
    if ($mode != 'view') {
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
      $msg_note = $this->t('This action cannot be reversed !
        Please note that deleting a location will remove any mapping it
        has with existing referral pathways and Service providers of application');
      $form['msg_note'] = [
        '#type' => 'markup',
        '#markup' => '<div class="msg-note">' . $msg_note . '</div>',
      ];
    }
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attached']['library'][] = 'erpw_location/erpw_location_js';
    return $form;
  }

  /**
   * AJAX callback handler that displays any errors or a success message.
   */
  public function deleteLocation(array $form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if (!empty($values)) {
      $response = new AjaxResponse();
      $term = Term::load($values['tid']);
      if (!empty($term)) {
        $term->delete();
      }
    }
    // Get the modal form using the form builder.
    $location_popup_form = $this->formBuilder->getForm('Drupal\erpw_location\Form\LocationPopupForm', $values['top_parent']);

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
