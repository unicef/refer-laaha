<?php

namespace Drupal\erpw_location\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\erpw_location\LocationService;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Url;
use Drupal\Core\Messenger\MessengerTrait;

/**
 * Class for add location.
 */
class AddLocationForm extends FormBase {

  use MessengerTrait;
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
   * A UrlService instance.
   *
   * @var Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * A cid variable.
   *
   * @var Drupal\erpw_location
   */
  protected $cid;

  /**
   * A tid variable.
   *
   * @var Drupal\erpw_location
   */
  protected $tid;

  /**
   * The Current user service.
   *
   * @var Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

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
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   */
  public function __construct(LoggerChannelFactory $logger,
  Connection $connection,
  EntityTypeManagerInterface $entity_type_manager,
  MessengerInterface $messenger,
  FormBuilderInterface $form_builder,
  LocationService $location_service,
  UrlGeneratorInterface $url_generator,
  AccountProxyInterface $current_user) {

    $this->logger = $logger;
    $this->connection = $connection;
    $this->entityManager = $entity_type_manager;
    $this->messenger = $messenger;
    $this->formBuilder = $form_builder;
    $this->locationService = $location_service;
    $this->urlGenerator = $url_generator;
    $this->currentUser = $current_user;
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
    $container->get('url_generator'),
    $container->get('current_user'),
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
  public function buildForm(array $form, FormStateInterface $form_state, $id = "") {
    $this->tid = $id;
    $location_options = $this->locationService->getLocationEntities();
    $level_1_name = "";
    $level_2_name = "";
    $level_3_name = "";
    $level_4_name = "";
    $readonly_level1 = FALSE;
    $readonly_level2 = FALSE;
    $readonly_level3 = FALSE;
    $readonly_level4 = FALSE;
    if (!empty($location_options) && $id == "") {
      $permission = 'add location of their own country';
      $location_options = erpw_filter_locations_permissions($location_options, $permission, 'location_entity');
      $form['location_options'] = [
        '#type' => 'select',
        '#options' => $location_options,
        '#empty_option' => $this->t('Select Country'),
        '#title' => $this->t('Country name'),
        '#ajax' => [
          'callback' => '::ajaxCallback',
          'wrapper' => 'top-wrapper',
        ],
      ];
    }
    else {
      if (!empty($id)) {
        $ancestors = $this->locationService->getAllAncestors($id);
        $top_level = $this->locationService->getTaxonomyTermById($ancestors[0]);
        if (isset($ancestors[1])) {
          $level_1_name = $this->locationService->getTaxonomyTermById($ancestors[1]) . " " . "(" . $ancestors[1] . ")";
        }
        if (isset($ancestors[2])) {
          $level_2_name = $this->locationService->getTaxonomyTermById($ancestors[2]) . " " . "(" . $ancestors[2] . ")";
        }
        if (isset($ancestors[3])) {
          $level_3_name = $this->locationService->getTaxonomyTermById($ancestors[3]) . " " . "(" . $ancestors[3] . ")";
        }
        if (isset($ancestors[4])) {
          $level_4_name = $this->locationService->getTaxonomyTermById($ancestors[4]) . " " . "(" . $ancestors[4] . ")";
        }
        $form['update_all_wrapper']['location-container-heading'] = [
          '#type' => 'markup',
          '#markup' => '<div class="location-container-heading">' . $this->t('Country name :') . " " . $top_level . '</div>',
        ];
      }
    }
    $form_state->setCached(FALSE);
    $form['top_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'top-wrapper'],
    ];
    $form['all_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'all-wrapper'],
    ];
    if ($id == "") {
      $form['top_wrapper']['all_wrapper']['intro_text'] = [
        '#type' => 'markup',
        '#markup' => '<div id="intro-text">' . $this->t('Please select the country to fill the location details') . '</div>',
      ];
    }

    if (!empty($form_state->getValue('location_options')) || !empty($id)) {
      $form['top_wrapper']['all_wrapper']['#prefix'] = '<div class="location-container">';
      $form['top_wrapper']['all_wrapper']['#suffix'] = '</div>';
    }

    if (!empty($form_state->getValue('location_options'))) {
      $form['top_wrapper']['all_wrapper']['location-container-heading'] = [
        '#type' => 'markup',
        '#markup' => '<div class="location-container-heading">' . $this->t('Add the new Location Details') . '</div>',
      ];
    }
    if (!empty($location_options) && $id !== "") {
      $form['top_wrapper']['all_wrapper']['location-container-heading'] = [
        '#type' => 'markup',
        '#markup' => '<div class="location-container-heading">' . $this->t('Update Location Details') . '</div>',
      ];
    }
    $form['top_wrapper']['all_wrapper']['level1_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'level1-wrapper', 'class' => 'location-level'],
    ];
    $form['top_wrapper']['all_wrapper']['level2_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'level2-wrapper', 'class' => 'location-level'],
    ];
    $form['top_wrapper']['all_wrapper']['level3_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'level3-wrapper', 'class' => 'location-level'],
    ];

    if ($form_state->getValue('location_options') != FALSE || !empty($id)) {
      if (empty($id)) {
        unset($form['top_wrapper']['all_wrapper']['intro_text']);
        $location = $this->entityManager->getStorage('location')->load($form_state->getValue('location_options'));
        $location_levels = $this->locationService->getLocationLevels($form_state->getValue('location_options'));
        $this->cid = $location->get('field_location_taxonomy_term')->target_id;
      }
      else {
        $location_levels = $this->locationService->getLocationEntityByTid($ancestors[0]);
        $this->cid = $ancestors[0];
      }
      if (isset($location_levels[0]) && !empty($location_levels[0])) {
        if ($id != "") {
          if ($id != $ancestors[1]) {
            $readonly_level1 = 'disabled';

          }
          else {
            $readonly_level1 = FALSE;
          }
        }
        $form['top_wrapper']['all_wrapper']['level1_wrapper']['level1'] = [
          '#type' => 'textfield',
          '#title' => $location_levels[0],
          '#autocomplete_route_name' => 'erpw_location.autocomplete',
          '#default_value' => $level_1_name,
          '#suffix' => '<div id="error-text"></div>',
          '#autocomplete_route_parameters' => ['tid' => $this->cid],
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
            'placeholder' => $this->t('Select') . " " . $location_levels[0],
            'disabled' => $readonly_level1,
          ],
        ];
      }
      if ($form_state->getValue('level1')) {
        $level1int = $this->locationService->getTaxonomyTermIdByName('country', $form_state->getValue('level1'));
      }
      else {
        $level1int = -1;
      }
      if (isset($location_levels[1]) && !empty($location_levels[1])) {
        if ($id) {
          if ($id != $ancestors[2]) {
            $readonly_level2 = 'disabled';

          }
          else {
            $readonly_level2 = FALSE;
          }
        }

        $form['top_wrapper']['all_wrapper']['level2_wrapper']['level2'] = [
          '#type' => 'textfield',
          '#title' => $location_levels[1],
          '#autocomplete_route_name' => 'erpw_location.autocomplete',
          '#default_value' => $level_2_name,
          '#suffix' => '<div id="error-text2"></div>',
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
            'placeholder' => $this->t('Select') . " " . $location_levels[1],
            'disabled' => $readonly_level2,
          ],

        ];
      }
      if ($form_state->getValue('level2')) {
        $level2int = $this->locationService->getTaxonomyTermIdByName('country', $form_state->getValue('level2'));
      }
      else {
        $level2int = -1;
      }
      if (isset($location_levels[2]) && !empty($location_levels[2])) {
        if ($id) {
          if ($id != $ancestors[3]) {
            $readonly_level3 = 'disabled';

          }
          else {
            $readonly_level3 = FALSE;
          }
        }
        $form['top_wrapper']['all_wrapper']['level3_wrapper']['level3'] = [
          '#type' => 'textfield',
          '#title' => $location_levels[2],
          '#autocomplete_route_name' => 'erpw_location.autocomplete',
          '#default_value' => $level_3_name,
          '#suffix' => '<div id="error-text3"></div>',
          '#autocomplete_route_parameters' => ['tid' => $level2int],
          '#attributes' => [
            'class' => [
              'mycategory',
            ],
            'placeholder' => $this->t('Select') . " " . $location_levels[2],
            'disabled' => $readonly_level3,
          ],
        ];
      }
      if (isset($location_levels[3]) && !empty($location_levels[3])) {
        if ($id) {
          if (isset($ancestors[4]) && $id != $ancestors[4]) {
            $readonly_level4 = 'disabled';
          }
          else {
            $readonly_level4 = FALSE;
          }
        }
        $form['top_wrapper']['all_wrapper']['level4_wrapper']['level4'] = [
          '#type' => 'textfield',
          '#title' => $location_levels[3],
          '#default_value' => $level_4_name,
          '#suffix' => '<div id="error-text4"></div>',
          '#attributes' => [
            'class' => [
              'mycategory',
            ],
            'placeholder' => $this->t('Select') . " " . $location_levels[3],
            'disabled' => $readonly_level4,
          ],
        ];
      }
      $form['top_wrapper']['submit_wrapper']['button'] = [
        '#type' => 'submit',
        '#value' => $this->t('Publish'),
        '#ajax' => [
          'callback' => '::sendMessageForm',
        ],
        '#suffix' => '</div>',
      ];
    }
    $url = $this->urlGenerator->generateFromRoute('erpw_location.manage_location');
    $form['#cache']['max-age'] = 0;
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attached']['drupalSettings']['erpw_location']['manage_location'] = $url;
    return $form;

  }

  /**
   * Ajax callback to add location.
   */
  public function ajaxCallback(array &$form, FormStateInterface $form_state) {
    unset($form['top_wrapper']['all_wrapper']['intro_text']);

    $form['top_wrapper']['all_wrapper']['level1_wrapper']['level1']['#value'] = "";
    $form['top_wrapper']['all_wrapper']['level2_wrapper']['level2']['#value'] = "";
    $form['top_wrapper']['all_wrapper']['level3_wrapper']['level3']['#value'] = "";
    $form['top_wrapper']['all_wrapper']['level4_wrapper']['level4']['#value'] = "";
    $form['top_wrapper']['#disable_inline_form_errors_summary'] = TRUE;

    return $form['top_wrapper'];
  }

  /**
   * Ajax callback to add location.
   */
  public function deptFilterSelect(array &$form, FormStateInterface $form_state) {
    return $form['top_wrapper']['all_wrapper']['level2_wrapper'];
  }

  /**
   * Ajax callback to add location.
   */
  public function ajaxCallback3(array &$form, FormStateInterface $form_state) {
    return $form['top_wrapper']['all_wrapper']['level3_wrapper'];
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
  public function submitLocationForm(array &$form, FormStateInterface $form_state) {
    // Get the selected values from the form.
    $level1 = $form_state->getValue('level1');
    $level2 = $form_state->getValue('level2');
    $level3 = $form_state->getValue('level3');
    $level4 = $form_state->getValue('level4');
    $country = $form_state->getValue('location_options');
    $counterSave = 0;
    // Country mapping.
    $countryStorage = $this->entityManager->getStorage('location');
    $countryEntity = $countryStorage->load($country)->get('name')->getValue()[0]['value'];
    $countryID = 0;
    $term_storage = $this->entityManager->getStorage('taxonomy_term');
    $query = $term_storage->getQuery()
    ->condition('vid', 'country')
    ->condition('parent', 0)
    ->sort('weight')
    ->sort('name');
    $term_ids = $query->execute();
    $terms = $term_storage->loadMultiple($term_ids);
    foreach ($terms as $term) {
      $termName = $term->get('name')->getValue()[0]['value'];
      if ($countryEntity == $termName) {
        $countryID = $term->id();
      }
    }
    
    // Load the taxonomy term storage.
    $termStorage = $this->entityManager->getStorage('taxonomy_term');
    
    // Check if the top-level term exists.
    $topLevelTerm = $termStorage->loadByProperties(['name' => $level1, 'vid' => 'country']);
    if (empty($topLevelTerm) && !empty($level1)) {
      // Create the top-level term.
      $topLevelTerm = $termStorage->create([
        'name' => $level1,
        'vid' => 'country',
        'parent' => $countryID,
      ]);
      $topLevelTerm->save();
      $counterSave++;
    }
    else {
      $topLevelTerm = reset($topLevelTerm);
    }

    // Check if the second-level term exists.
    $secondLevelTerm = $termStorage->loadByProperties(['name' => $level2, 'vid' => 'country', 'parent' => $topLevelTerm->id()]);
    if (empty($secondLevelTerm) && !empty($level2)) {
      // Create the second-level term.
      $secondLevelTerm = $termStorage->create([
        'name' => $level2,
        'vid' => 'country',
        'parent' => $topLevelTerm->id(),
      ]);
      $secondLevelTerm->save();
      $counterSave++;
    }
    else {
      $secondLevelTerm = reset($secondLevelTerm);
    }

    // Check if the third-level term exists.
    if (!empty($secondLevelTerm) && !empty($level3)) {
      $thirdLevelTerm = $termStorage->loadByProperties(['name' => $level3, 'vid' => 'country', 'parent' => $secondLevelTerm->id()]);
      if (empty($thirdLevelTerm)) {
        // Create the third-level term.
        $thirdLevelTerm = $termStorage->create([
          'name' => $level3,
          'vid' => 'country',
          'parent' => $secondLevelTerm->id(),
        ]);
        $thirdLevelTerm->save();
        $counterSave++;
      }
      else {
        $thirdLevelTerm = reset($thirdLevelTerm);
      }
    }

    // Check if the fourth-level term exists.
    if (!empty($thirdLevelTerm) && !empty($level4)) {
      $fourthLevelTerm = $termStorage->loadByProperties(['name' => $level4, 'vid' => 'country', 'parent' => $thirdLevelTerm->id()]);
      if (empty($fourthLevelTerm)) {
        // Create the fourth-level term.
        $fourthLevelTerm = $termStorage->create([
          'name' => $level4,
          'vid' => 'country',
          'parent' => $thirdLevelTerm->id(),
        ]);
        $fourthLevelTerm->save();
        $counterSave++;
      }
    }
    if ($counterSave == 0) {
      $text = t('Error! Couldnot save the location. Try with new location names.');
    }
    else {
      $text = t('Success! New location successfully created.');
    }
    // Display a success message.
    $response = new AjaxResponse();
    // Redirect the user to a different page.
    $redirect = Url::fromRoute('erpw_location.manage_location');
    $response->addCommand(new RedirectCommand($redirect->toString()));
    $this->messenger()->addMessage($text);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function sendMessageForm(array &$form, FormStateInterface $form_state, $id = "") {
    $response = new AjaxResponse();
    $counter = 0;

    $level4 = $form_state->getValue('level4');
    if (empty($form_state->getValue('location_options')) && $id == "") {
      $response->addCommand(new HtmlCommand('#intro-text',
      $this->t('Please select the country to fill the location details.')));
      $response->addCommand(new InvokeCommand('#intro-text',
      'css', ["color", "red"]));
      $counter++;
    }
    else {
      $response->addCommand(new HtmlCommand('#intro-text', ''));
    }
    if (empty($form_state->getValue('level1'))) {
      $response->addCommand(new HtmlCommand('#error-text',
      $form['top_wrapper']['all_wrapper']['level1_wrapper']['level1']['#title'] . " " . $this->t('field is required.')));
      $response->addCommand(new InvokeCommand('#error-text',
      'css', ["color", "#A85766"]));
      $counter++;
    }
    else {
      $response->addCommand(new HtmlCommand('#error-text', ''));
    }
    if (empty($form_state->getValue('level2'))) {
      if (!empty($form_state->getValue('level3'))) {
        $response->addCommand(new HtmlCommand('#error-text2',
        $form['top_wrapper']['all_wrapper']['level2_wrapper']['level2']['#title'] . " " . $this->t('field is required.')));
        $response->addCommand(new InvokeCommand('#error-text2',
        'css', ["color", "#A85766"]));
        $counter++;
      }
      else {
        $response->addCommand(new HtmlCommand('#error-text2', ''));
      }
      if (isset($level4)) {
        if (!empty($form_state->getValue('level4'))) {
          $response->addCommand(new HtmlCommand('#error-text2',
          $form['top_wrapper']['all_wrapper']['level2_wrapper']['level2']['#title'] . " " . $this->t('field is required.')));
          $response->addCommand(new InvokeCommand('#error-text2',
          'css', ["color", "#A85766"]));
          $counter++;
        }
        else {
          $response->addCommand(new HtmlCommand('#error-text2', ''));
        }
      }
    }
    if (empty($form_state->getValue('level3'))) {
      if (isset($level4)) {
        if (!empty($form_state->getValue('level4'))) {
          $response->addCommand(new HtmlCommand('#error-text3',
          $form['top_wrapper']['all_wrapper']['level3_wrapper']['level3']['#title'] . " " . $this->t('field is required.')));
          $response->addCommand(new InvokeCommand('#error-text3',
          'css', ["color", "#A85766"]));
          $counter++;
        }
        else {
          $response->addCommand(new HtmlCommand('#error-text3', ''));
        }
      }
    }
    if ($counter == 0) {
      $response = $this->submitLocationForm($form, $form_state);
    }
    return $response;
  }

}
