<?php

namespace Drupal\erpw_location\Form;

use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\State\StateInterface;

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
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * ImportLocationForm constructor.
   *
   * @param \Drupal\Core\State\StateInterface $stateService
   *   The state API service.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   EntityManager object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   */
  public function __construct(StateInterface $stateService, EntityTypeManagerInterface $entity_manager, LanguageManagerInterface $language_manager) {
    $this->stateService = $stateService;
    $this->entityManager = $entity_manager;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state'),
      $container->get('entity_type.manager'),
      $container->get('language_manager')
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
    // dump($export_form_data);
    $location_id = $export_form_state_data->getValue('location_options');
    // Get Country Name.
    if ($location_id) {
      $country_name = $export_form_data['location_options']['#options'][$location_id];
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
