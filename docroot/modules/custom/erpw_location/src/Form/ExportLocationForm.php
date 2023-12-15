<?php

namespace Drupal\erpw_location\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\erpw_location\LocationService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form that allows users to import location.
 */
class ExportLocationForm extends FormBase {

  /**
   * The location import service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $stateService;

  /**
   * A entityManager instance.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

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
   * ImportLocationForm constructor.
   *
   * @param \Drupal\Core\State\StateInterface $stateService
   *   The state API service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   EntityManager object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\erpw_location\LocationService $location_service
   *   The location service.
   */
  public function __construct(StateInterface $stateService, EntityTypeManagerInterface $entity_manager, LanguageManagerInterface $language_manager, LocationService $location_service) {
    $this->stateService = $stateService;
    $this->entityManager = $entity_manager;
    $this->languageManager = $language_manager;
    $this->locationService = $location_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state'),
      $container->get('entity_type.manager'),
      $container->get('language_manager'),
      $container->get('erpw_location.location_services')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'export_location_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['export_location_title'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="review-msg">',
      '#markup' => $this->t('You are about to export the list of all locations available on the app. Proceed or Cancel.'),
      '#suffix' => '</div>',
    ];
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['proceed'] = [
      '#type' => 'submit',
      '#value' => $this->t('Proceed'),
      '#submit' => ['::exportLocationCsv'],
    ];
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => Url::fromRoute('erpw_location.manage_location'),
    ];
    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div id="download-message-box"><div id="download-message" class="result_message"></div></div>',
      '#weight' => 99,
    ];

    return $form;
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
    $export_form_data = $this->stateService->get('export_location_form_data');
    $export_form_state_data = $this->stateService->get('export_location_form_state_data');
    $location_id = $export_form_state_data->getValue('location_options');
    // Get Country Name.
    if ($location_id) {
      $country_name = $export_form_data['location_options']['#options'][$location_id];
    }
    else {
      $country_name = 'Country Name';
    }
    $active_languages = $this->languageManager->getLanguages();
    $current_active_language_code = $this->languageManager->getCurrentLanguage()->getId();
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
      $location_entity_id = $location->id();
      $number_of_levels = count($this->locationService->getLocationLevels($location_entity_id));

      $csv_export = [];
      // Get Heading Row for the CSV file.
      $i = 0;
      for ($l = 1; $l <= $number_of_levels; $l++) {
        if ($location->getTranslation($current_active_language_code)->get('level_' . $l)->getValue()) {
          $column_name = $location->getTranslation($current_active_language_code)->get('level_' . $l)->getValue()[0]['value'];
        }
        else {
          continue;
        }
        $csv_export[0][$i] = $column_name;
        $i++;
      }

      // Location details for the CSV file.
      $location_taxonomy_term_id = $location->get('field_location_taxonomy_term')->getValue()[0]['target_id'];
      $taxonomy_storage = $this->entityManager->getStorage('taxonomy_term');
      $query = $taxonomy_storage->getQuery()
        ->condition('vid', $location->bundle())
        ->accessCheck(FALSE);
      $tids = $query->execute();
      $location_data = $taxonomy_storage->loadMultiple($tids);
      $location_levels = $this->locationService->getLocationLevels($location_entity_id);
      $location_details = [];

      foreach ($location_data as $tid => $tid_location) {
        $ancestors = $this->entityManager->getStorage('taxonomy_term')->loadAllParents($tid);
        $ancestors = array_reverse(array_keys($ancestors));
        if (in_array($location_taxonomy_term_id, $ancestors)) {
          $csv_data = [];
          foreach ($location_levels as $key => $level) {
            $level_data_name = "";
            $level_term = "";
            if (!empty($ancestors[$key + 1])) {
              $level_term = $this->entityManager->getStorage('taxonomy_term')->load($ancestors[$key + 1]);
            }
            if (!empty($level_term)) {
              $level_data_name = $level_term->hasTranslation($current_active_language_code) ? $level_term->getTranslation($current_active_language_code)->get('name')->value : $level_term->get('name')->value;
            }
            $csv_data[] = strlen($level_data_name) > 0 ? $level_data_name : ' - ';
            $location_details[] = $level . " : " . $level_data_name;
          }
          $csv_export[] = $csv_data;
        }
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
