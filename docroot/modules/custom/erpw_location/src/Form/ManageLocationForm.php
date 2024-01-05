<?php

namespace Drupal\erpw_location\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\domain\DomainNegotiatorInterface;
use Drupal\erpw_location\LocationService;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class Manage Location Form.
 */
class ManageLocationForm extends FormBase {

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
   * A UrlService instance.
   *
   * @var Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * The Current user service.
   *
   * @var Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The location service.
   *
   * @var \Drupal\erpw_location\LocationService
   */
  protected $locationService;

  /**
   * The temp store factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The Domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $stateService;

  /**
   * ManageLocation constructor.
   *
   * @param \Psr\Log\LoggerChannelFactory $logger
   *   Logger object.
   * @param \Drupal\Core\Database\Connection $connection
   *   Connection Object.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   EntityManager object.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form_builder service.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The temp store factory.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\erpw_location\LocationService $location_service
   *   The location service.
   * @param \Drupal\domain\DomainNegotiatorInterface $domain_negotiator
   *   The domain negotiator service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\State\StateInterface $stateService
   *   The state storage service.
   */
  public function __construct(
    LoggerChannelFactory $logger,
    Connection $connection,
    EntityTypeManagerInterface $entity_manager,
    MessengerInterface $messenger,
    FormBuilderInterface $form_builder,
    UrlGeneratorInterface $url_generator,
    LanguageManagerInterface $language_manager,
    PrivateTempStoreFactory $temp_store_factory,
    AccountProxyInterface $current_user,
    LocationService $location_service,
    DomainNegotiatorInterface $domain_negotiator,
    ConfigFactoryInterface $configFactory,
    StateInterface $stateService
  ) {

    $this->logger = $logger;
    $this->connection = $connection;
    $this->entityManager = $entity_manager;
    $this->messenger = $messenger;
    $this->formBuilder = $form_builder;
    $this->urlGenerator = $url_generator;
    $this->languageManager = $language_manager;
    $this->tempStoreFactory = $temp_store_factory;
    $this->currentUser = $current_user;
    $this->locationService = $location_service;
    $this->domainNegotiator = $domain_negotiator;
    $this->configFactory = $configFactory;
    $this->stateService = $stateService;
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
      $container->get('url_generator'),
      $container->get('language_manager'),
      $container->get('tempstore.private'),
      $container->get('current_user'),
      $container->get('erpw_location.location_services'),
      $container->get('domain.negotiator'),
      $container->get('config.factory'),
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'manage_location_form_csv';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $country_tid = "", $location_levels_tid = "") {
    $current_uri = $this->getRequest()->getRequestUri();
    $store = $this->tempStoreFactory->get('erpw_location_collection');
    $store->set('location_redirect_url', $current_uri);
    $form['#attributes']['enctype'] = "multipart/form-data";
    $url = Url::fromRoute('erpw_location.import_location_file', [], [
      'attributes' => [
        'class' => [
          'button',
        ],
      ],
    ]);
    $link = Link::fromTextAndUrl($this->t('Import'), $url)->toString();
    $form['open_modal'] = [
      '#type' => 'markup',
      '#markup' => $link,
    ];
    $form['export_csv'] = [
      '#type' => 'submit',
      '#value' => $this->t('Export'),
      '#ajax' => [
        'callback' => '::erpw_location_open_export_modal_callback',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => NULL,
        ],
      ],
    ];
    $location_entities = $this->entityManager->getStorage('location')->loadByProperties(
      ['type' => 'country', 'status' => 1]);

    $location_options = [];
    foreach ($location_entities as $location) {
      $location_options[$location->id()] = $location->get('name')->getValue()[0]['value'];
    }
    if (!$form_state->getUserInput()) {
      $user = $this->entityManager->getStorage('user')->load($this->currentUser->id());
      // Get active domain's tid.
      $domain = $this->domainNegotiator->getActiveDomain();
      $config = $this->configFactory->get('domain.location.' . $domain->get('id'));
      $location_value = $config->get('location');
      $ancestors = $this->entityManager->getStorage('taxonomy_term')->loadAllParents($location_value);
      $upper_ancestors = array_reverse(array_keys($ancestors));
      $mylocation = "";
      foreach (array_reverse($upper_ancestors) as $key => $value) {
        $mylocation .= " " . $this->locationService->getTaxonomyTermById($value) . ", ";
      }
      $mylocation = substr(trim($mylocation), 0, -1);

      if (!empty($upper_ancestors[0])) {
        $country_tid = $this->locationService->getLocationSingleEntityIdByTid($upper_ancestors[0]);
      }
      if (!empty($country_tid)) {
        $link = Link::createFromRoute(
          $this->t('Click to Change Country'),
          'erpw_location.user_location_manage',
          ['id' => $country_tid, 'page' => 'location']
        )->toString();
      }
      else {
        $link = "";
      }

      $form['change_country_link'] = [
        '#markup' => $link,
      ];
    }

    if (!empty($location_entities) || $country_tid = "") {
      $form['location_options'] = [
        '#type' => 'select',
        '#options' => $location_options,
        '#default_value' => $country_tid,
        '#empty_option' => $this->t('Select Country'),
        '#title' => $this->t('Country'),
        '#ajax' => [
          'callback' => '::getLocationDetail',
          'event' => 'change',
          'wrapper' => 'edit-location-details',
          'progress' => [
            'type' => 'throbber',
          ],
        ],
      ];
    }
    $form['location_value'] = [
      '#markup' => $mylocation,
    ];

    $form['location_list'] = [
      '#prefix' => '<div id="edit-location-details">',
      '#suffix' => '</div>',
    ];

    if (!empty($form_state->getValue('location_options')) || $country_tid != "") {
      $location_entity_id = !empty($form_state->getValue('location_options')) ? $form_state->getValue('location_options') : $country_tid;
      $location_levels = $this->locationService->getLocationLevels($location_entity_id);
      $location_levels_count = count($location_levels);
      $location_entity = $this->entityManager->getStorage('location')->load($location_entity_id);
      if (!empty($location_entity->get('field_location_taxonomy_term')->getValue())) {
        $location_tid = $location_entity->get('field_location_taxonomy_term')->getValue()[0]['target_id'];
      }
      $manager = $this->entityManager->getStorage('taxonomy_term');
      if (!$form_state->getUserInput()) {
        $location_tid = $upper_ancestors[0];
        $location_tid = !empty($location_levels_tid) ? $location_levels_tid : $location_tid;
      }
      $childs_check = $this->entityManager->getStorage('taxonomy_term')->loadChildren($location_tid);
      if (!empty($childs_check)) {
        $tree = $manager->loadTree('country', $location_tid, $location_levels_count, TRUE);
        $locations = [];
        $langcode = $this->languageManager->getCurrentLanguage()->getId();
        foreach ($tree as $term) {
          $childs = $this->entityManager->getStorage('taxonomy_term')->loadChildren($term->id());
          if (empty($childs)) {
            $tid = $term->id();
            $term_name = $term->hasTranslation($langcode) ?
              $term->getTranslation($langcode)->get('name')->value : $term->get('name')->value;
            $locations[$tid] = $term_name;
          }
        }
      }
      else {
        $locations[$location_tid] = $this->locationService->getTaxonomyTermById($location_tid);
      }
      // Get active domain's tid.
      $domain = $this->domainNegotiator->getActiveDomain();
      $config = $this->configFactory->get('domain.location.' . $domain->get('id'));
      $location_value = $config->get('location');
      $countryHierarchy = getCountryHierarchy($location_value);

      // Output the key-value pairs as an array.
      $finalArray = [];
      foreach ($countryHierarchy as $tid => $termName) {
        $finalArray[$tid] = $termName;
      }
      natcasesort($locations);
      $form['location_list']['location_count'] = [
        '#type' => 'markup',
        '#markup' => '<div class="location-count edit-delete-links margin-space">' .
        count($finalArray) . ' ' . $this->t('Locations') .
        '</div>',
      ];

      foreach ($finalArray as $tid => $location) {
        $ancestors = $this->entityManager->getStorage('taxonomy_term')->loadAllParents($tid);
        $ancestors = array_reverse(array_keys($ancestors));
        $location_details = '';
        foreach ($location_levels as $key => $level) {
          $level_data_name = "";
          $level_term = "";
          if (!empty($ancestors[$key + 1])) {
            $level_term = $this->entityManager->getStorage('taxonomy_term')->load($ancestors[$key + 1]);
          }
          if (!empty($level_term)) {
            $level_data_name = $level_term->get('name')->value;
          }
          if ($key !== array_key_last($location_levels)) {
            $location_details .= '<div class="level">' . $level . " : " . $level_data_name . '</div>';
          }
        }

        $delete_url = $this->urlGenerator->generateFromRoute(
          'erpw_location.delete_location',
          ['tid' => $tid]
        );
        $edit_url = $this->urlGenerator->generateFromRoute(
          'erpw_location.edit_location',
          ['id' => $tid]
        );
        $view_url = $this->urlGenerator->generateFromRoute(
          'erpw_location.view_location',
          ['tid' => $tid, 'mode' => 'view']
        );
        $location_operations = '<div class="edit-delete-links">
          <span class="delete-link"><a href="' . $delete_url . '">' . $this->t('Delete') . '</a></span>
          <span class="edit-link"><a href="' . $edit_url . '">' . $this->t('Edit') . '</a></span>
        </div>';

        $form['location_list']['location_' . $tid] = [
          '#type' => 'markup',
          '#markup' => '
            <div class="location-card">
              <div class="title-with-icons">
                <a href="' . $view_url . '">
                  <div id="location-title" class="location-title">' . $location . '</div>
                </a>
                <div class="location-operations">' . $location_operations . '</div>
                </div><a href="' . $view_url . '"><div class="location-details>' . $location_details .
          '</div></div></a> ',
        ];
      }
    }
    $form['#cache']['max-age'] = 0;
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#theme'] = 'manage_location_form';
    $form['#flag'] = 1;
    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function getLocationDetail(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild();
    return $form['location_list'];
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

  /**
   * {@inheritdoc}
   */
  public function erpw_location_open_export_modal_callback(array &$form, FormStateInterface $form_state) {
    $this->stateService->set('export_location_form_data', $form);
    $this->stateService->set('export_location_form_state_data', $form_state);
    $response = new AjaxResponse();
    $url = Url::fromRoute('erpw_location.export_location_file');
    $redirect_response = new RedirectResponse($url->toString());
    $response->addCommand(new RedirectCommand($redirect_response->getTargetUrl()));
    return $response;
  }

}

/**
 *
 */
function getTermHierarchy($vid, $parent = 0, &$result = [], $depth = 0) {
  $query = \Drupal::entityQuery('taxonomy_term');
  $query->condition('vid', $vid);
  $query->condition('parent', $parent);
  $tids = $query->accessCheck(TRUE)->execute();

  foreach ($tids as $tid) {
    $term = Term::load($tid);
    $result[$term->id()] = $term->getName();
    getTermHierarchy($vid, $term->id(), $result, $depth + 1);
  }
}

/**
 *
 */
function getCountryHierarchy($cid) {
  $query = \Drupal::entityQuery('taxonomy_term');
  $query->condition('vid', 'country');
  $query->condition('tid', $cid);
  $countryTids = $query->accessCheck(TRUE)->execute();

  $result = [];
  foreach ($countryTids as $countryTid) {
    getTermHierarchy('country', $countryTid, $result);
  }

  return $result;
}
