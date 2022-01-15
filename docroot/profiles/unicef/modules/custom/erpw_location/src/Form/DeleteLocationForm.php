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
use \Drupal\taxonomy\Entity\Term;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Ajax\OpenModalDialogCommand;

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
   * A entityManager instance.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;
  protected $currentPathStack;

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
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entityManager
   *   EntityManager object.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form_builder service.
   */
  public function __construct(LoggerChannelFactory $logger, Connection $connection, EntityTypeManagerInterface $entityManager, MessengerInterface $messenger, FormBuilderInterface $form_builder, CurrentPathStack $currentPathStack) {
    $this->logger = $logger;
    $this->connection = $connection;
    $this->entityManager = $entityManager;
    $this->messenger = $messenger;
    $this->formBuilder = $form_builder;
    $this->currentPathStack = $currentPathStack;
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
      $container->get('path.current')
    );
  }

  

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
    $contry_name = '';
    $current_path = $this->currentPathStack->getPath();
    $curr_path = explode("/",$current_path);
    $ancestors = $this->entityManager->getStorage('taxonomy_term')->loadAllParents($curr_path[2]);
    $ancestors = array_reverse(array_keys($ancestors));
    $country_term_name = Term::load($ancestors[0])->get('name')->value;
    $contry_name .= '<div class="country-name">Country name*: '.$country_term_name.'</div>';
    $location_levels = \Drupal::service('erpw_location.location_services')->getLocationLevels($ancestors[0]);
    $location_details = '';
    foreach ($location_levels as $key => $level) {
        $level_term = $this->entityManager->getStorage('taxonomy_term')->load($ancestors[$key + 1]);
        $level_data_name = $level_term->get('name')->value;
        $location_details .= '<div class="level">' . $level . " *: " . $level_data_name . '</div>';
    }
    $form['tid'] =[
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
    $Term = Term::load($values['tid']);
    if (!empty($Term)) {
      $Term->delete();
    }
    // Get the modal form using the form builder.
    $modal_form = $this->formBuilder->getForm('Drupal\erpw_location\Form\LocationPopupForm');

    // Add an AJAX command to open a modal dialog with the form as the content.
    $response->addCommand(new OpenModalDialogCommand('', $modal_form, ['width' => '400']));

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

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
