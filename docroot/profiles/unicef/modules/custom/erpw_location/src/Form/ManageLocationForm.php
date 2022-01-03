<?php

namespace Drupal\erpw_location\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\erpw_location\Entity\LocationEntity;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Class ManageLocationForm.
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
   * CuidarLogin constructor.
   *
   * @param \Psr\Log\LoggerChannelFactory $logger
   *   Logger object.
   * @param \Drupal\Core\Database\Connection $connection
   *   Connection Object.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entityManager
   *   EntityManager object.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(LoggerChannelFactory $logger, Connection $connection, EntityTypeManagerInterface $entityManager, MessengerInterface $messenger) {
    $this->logger = $logger;
    $this->connection = $connection;
    $this->entityManager = $entityManager->getStorage('location');
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory'),
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('messenger')
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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['enctype'] = "multipart/form-data";

    $form['import_location_csv'] = [
      '#type' => 'managed_file',
      '#multiple' => FALSE,
      '#title' => t('Import Location CSV'),
      '#size' => 22,
      '#upload_location' => 'public://location_csv',
      "#upload_validators" => ["file_validate_extensions" => ["csv"]],
    ];
    $form['export_csv'] = [
      '#type' => 'submit',
      '#value' => $this->t('Export Location CSV'),
      '#submit' => ['::exportLocationCSV'],
      '#attributes' => [
        'class' => [
          'export-csv',
        ],
      ],
    ];
    $form['import_csv'] = [
      '#type' => 'submit',
      '#value' => t('Import Location CSV'),
    ];
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
        '#empty_option' => t('Select Country'),
        '#title' => $this->t('Country'),
      ];
    }
    $form['#cache'] = ['max-age' => 0];
    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $op = $form_state->getValue("op");
    if ($op == 'Import Location CSV') {
      if (empty($form_state->getValue('import_location_csv'))) {
        $form_state->setErrorByName('import_location_csv', t('Please upload a file to start import process.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $file_obj = $form_state->getValue('import_location_csv');
    if ($file_obj) {
      $file_obj_data = File::load(reset($file_obj));
      $file_path = 'public://location_csv/' . $file_obj_data->getFilename();
      $country_name = explode('.', $file_obj_data->getFilename())[0];
      $imported = $this->handleFileData($file_path, $country_name);
      if ($imported) {
        $this->messenger->addMessage(t('Location CSV imported successfully.'));
      }
    }
  }

  /**
   * To import data as location.
   *
   * @param string $filepath
   *   The filepath.
   */
  protected function handleFileData($filepath, $country_name) {
    $country_name = explode("_", $country_name)[0];
    if (($handle = fopen($filepath, 'r')) === FALSE) {
      return;
    }

    // Read the csv data.
    $headerData = [];
    $csvData = [];
    while (($data = fgetcsv($handle)) !== FALSE) {
      if (empty($headerData)) {
        $headerData = $data;
      }
      else {
        $csvData[] = $data;
      }
    }
    fclose($handle);
    $header_count = count($headerData);
    $langcode_count = $header_count / 4;
    $csv_langcodes = [];
    for ($i = 0; $i < $langcode_count; $i++) {
      $csv_langcodes[$i] = explode("_", $headerData[$i])[2];
    }
    // Check if the language in the csv is an active language.
    $active_languages = \Drupal::languageManager()->getLanguages();
    $active_languages_list = array_keys($active_languages);
    foreach ($csv_langcodes as $langcode) {
      if (!in_array($langcode, $active_languages_list)) {
        $this->messenger->addError(t('%langcode is not a valid langcode available in the system.', [
          '%langcode' => $langcode,
        ]));
        return FALSE;
      }
    }

    // Import hierarchy and add translation.
    $hierarchy_level = [];
    for ($k = 0; $k < count($csv_langcodes); $k++) {
      $j = $k;
      $l = $k;
      $langcode = $csv_langcodes[$k];
      for ($i = 1; $i <= $langcode_count; $i++) {
        $hierarchy_level['level_' . $i] = $csvData[0][$j];
        $j += $langcode_count;
      }
      $location_hierarchy = array_merge(
        [
          'type' => 'country',
          'name' => $country_name,
        ], $hierarchy_level);
      $query = $this->connection->select('location_field_data', 'lc');
      $query->fields('lc', ['name', 'id']);
      $query->condition('lc.name', $country_name);
      $result = $query->execute()->fetchAll();
      // If location exist update the hierarchy.
      if ($result[0]->name == $country_name) {
        $entity_id = $result[0]->id;
        $location_entity = LocationEntity::load($entity_id);
        // If the given translation exists, update the translation.
        if ($location_entity->hasTranslation($langcode)) {
          $location_entity = $location_entity->getTranslation($langcode);
          for ($i = 1; $i <= $langcode_count; $i++) {
            $location_entity->set('level_' . $i, $csvData[0][$l]);
            $l += $langcode_count;
          }
          $location_entity->save();
        }
        else {
          // Create translation for the location entity for the langcode.
          $location_entity->addTranslation($langcode, $location_hierarchy)->save();
        }
      }
      else {
        $location_hierarchy = array_merge(
          [
            'type' => 'country',
            'langcode' => $langcode,
            'name' => $country_name,
          ], $hierarchy_level);
        $location_entity = LocationEntity::create($location_hierarchy);
        $location_entity->save();
      }
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function exportLocationCsv(array &$form, FormStateInterface $form_state) {
    $location_id = $form_state->getValue('location_options');
    if (!$location_id) {
      $active_languages = \Drupal::languageManager()->getLanguages();
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
      $this->arrayCsvDownload($csv_export);
    }
    else {
      $location = $this->entityManager->load($location_id);
      $active_languages = \Drupal::languageManager()->getLanguages();
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
      $this->arrayCsvDownload($csv_export);
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function arrayCsvDownload($array, $filename = "eRPW Location.csv", $delimiter = ",") {

    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '";');

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
