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
   */
  public function __construct(LoggerChannelFactory $logger, Connection $connection, EntityTypeManagerInterface $entityManager) {
    $this->logger = $logger;
    $this->connection = $connection;
    $this->entityManager = $entityManager->getStorage('location');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory'),
      $container->get('database'),
      $container->get('entity_type.manager')
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

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];

    $form['#cache'] = ['max-age' => 0];
    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (empty($form_state->getValue('import_location_csv'))) {
      $form_state->setErrorByName('import_location_csv', t('Please upload a file to start import process.'));
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
      $this->handleFileData($file_path, $country_name);
      \Drupal::messenger()->addMessage(t('Location CSV imported successfully.'));
    }
  }

  /**
   * To import data as location.
   *
   * @param string $filepath
   *   The filepath.
   */
  protected function handleFileData($filepath, $country_name) {
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
    $i = 1;
    $level_count = count($headerData);
    $hierarchy_level = [];
    for ($k = 1; $k <= $level_count; $k++) {
      $hierarchy_level['level_' . $k] = $headerData[$k - 1];
    }
    $location_hierarchy = array_merge(['type' => 'country', 'name' => $country_name], $hierarchy_level);
    // Check if the location entity already exists.
    $query = $this->connection->select('location_field_data', 'lc');
    $query->fields('lc', ['name', 'id']);
    $query->condition('lc.name', $country_name);
    $result = $query->execute()->fetchAll();

    if ($result[0]->name == $country_name) {
      $entity_id = $result[0]->id;
      $location_entity = LocationEntity::load($entity_id);
      for ($k = 1; $k <= $level_count; $k++) {
        $location_entity->set('level_' . $k, $headerData[$k - 1]);
      }
      $location_entity->save();
    }
    else {
      $location_entity = LocationEntity::create($location_hierarchy);
      $location_entity->save();
    }
  }

}
