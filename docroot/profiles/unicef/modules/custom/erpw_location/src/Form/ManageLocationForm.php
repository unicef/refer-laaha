<?php

namespace Drupal\erpw_location\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\erpw_location\LocationService;

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
    LocationService $location_service) {

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
      $container->get('erpw_location.location_services')
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
    $url = Url::fromRoute('erpw_location.open_import_modal', [], [
      'attributes' => [
        'class' => [
          'use-ajax',
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
      '#submit' => ['::exportLocationCsv'],
    ];
    $location_entities = $this->entityManager->getStorage('location')->loadByProperties(
      ['type' => 'country', 'status' => 1]);

    $location_options = [];
    foreach ($location_entities as $location) {
      $location_options[$location->id()] = $location->get('name')->getValue()[0]['value'];
    }
    if (!$form_state->getUserInput()) {
      $user = $this->entityManager->getStorage('user')->load($this->currentUser->id());
      $location_value = $user->field_location_details->value;
      if (!empty($location_levels_tid)) {
        $location_value = $location_levels_tid;
      }
      $ancestors = $this->entityManager->getStorage('taxonomy_term')->loadAllParents($location_value);
      $upper_ancestors = array_reverse(array_keys($ancestors));
      $mylocation = "";
      foreach (array_reverse($upper_ancestors) as $key => $value) {
        $mylocation .= " " . $this->locationService->getTaxonomyTermById($value);
      }
      if (!empty($upper_ancestors[0])) {
        $country_tid = $this->locationService->getLocationSingleEntityIdByTid($upper_ancestors[0]);
      }
      if (!empty($country_tid)) {
        $link = Link::createFromRoute($this->t('Click to change location'), 'erpw_location.user_location_manage',
        ['id' => $country_tid, 'page' => 'location'])->toString();
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
      natcasesort($locations);
      $form['location_list']['location_count'] = [
        '#type' => 'markup',
        '#markup' => '<div class="location-count edit-delete-links margin-space">' .
        count($locations) . ' ' . $this->t('Locations') .
        '</div>',
      ];
      foreach ($locations as $tid => $location) {
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
        // @todo url routes to be updated.
        $clone_url = $this->urlGenerator->generateFromRoute('erpw_location.manage_location');
        $delete_url = $this->urlGenerator->generateFromRoute('erpw_location.delete_location',
           ['tid' => $tid]
         );
        $edit_url = $this->urlGenerator->generateFromRoute('erpw_location.edit_location',
           ['id' => $tid]
         );
        $view_url = $this->urlGenerator->generateFromRoute('erpw_location.view_location',
           ['tid' => $tid, 'mode' => 'view']
        );
        $location_operations = '<div class="edit-delete-links">
          <span class="clone-service-type"><a href="' . $clone_url . '">' . $this->t('Clone') . '</a></span>
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
  public function exportLocationCsv(array &$form, FormStateInterface $form_state) {
    $location_id = $form_state->getValue('location_options');
    // Get Country Name.
    if ($location_id) {
      $country_name = $form['location_options']['#options'][$location_id];
    }
    else {
      $country_name = 'Country Name';
    }
    $active_languages = $this->languageManager->getLanguages();
    if (!$location_id) {
      $active_languages_list = array_keys($active_languages);
      $location_lang_count = 0;
      $location_lang = [];
      foreach ($active_languages_list as $langcode) {
        $location_lang_count++;
        $location_lang[$location_lang_count] = $langcode;
      }
      $csv_export = [];
      // Get First Row.
      $i = 0;
      for ($l = 1; $l <= 4; $l++) {
        foreach ($location_lang as $lang) {
          $column_name = 'level_' . $l . '_' . $lang;
          $csv_export[0][$i] = $column_name;
          $i++;
        }
      }
      $this->arrayCsvDownload($csv_export, $country_name);
    }
    else {
      $location = $this->entityManager->getStorage('location')->load($location_id);
      $active_languages_list = array_keys($active_languages);
      $location_lang_count = 0;
      $location_lang = [];
      foreach ($active_languages_list as $langcode) {
        if ($location->hasTranslation($langcode)) {
          $location_lang_count++;
          $location_lang[$location_lang_count] = $langcode;
        }
      }
      $csv_export = [];
      // Get First Row.
      $i = 0;
      for ($l = 1; $l <= 4; $l++) {
        foreach ($location_lang as $lang) {
          $column_name = 'level_' . $l . '_' . $lang;
          $csv_export[0][$i] = $column_name;
          $i++;
        }
      }
      // Get Header.
      $i = 0;
      foreach ($csv_export[0] as $column) {
        $level_name = explode("_", $column)[0] . '_' . explode("_", $column)[1];
        $langcode = explode("_", $column)[2];
        if ($location->getTranslation($langcode)->get($level_name)->getValue()) {
          $field_value = $location->getTranslation($langcode)->get($level_name)->getValue()[0]['value'];
        }
        else {
          $field_value = '';
        }
        $csv_export[1][$i] = $field_value;
        $i++;
      }
      $this->arrayCsvDownload($csv_export, $country_name);
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function arrayCsvDownload($array, $filename, $delimiter = ",") {

    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv";');

    // Clean output buffer.
    ob_end_clean();

    $handle = fopen('php://output', 'w');
    $headings = $array[0];
    $counter = 0;
    // Use keys as column titles.
    fputcsv($handle, $headings, $delimiter);
    foreach ($array as $value) {
      if ($counter++ == 0) {
        continue;
      }
      fputcsv($handle, $value, $delimiter);
    }

    fclose($handle);

    // Use exit to get rid of unexpected output afterward.
    exit();
  }

}
