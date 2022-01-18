<?php

namespace Drupal\erpw_location\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Url;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\erpw_location\Entity\LocationEntity;
use Drupal\file\Entity\File;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Ajax\CloseModalDialogCommand;

/**
 * ImportLocationModalForm class.
 */
class ImportLocationModalForm extends FormBase {

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
   */
  public function __construct(LoggerChannelFactory $logger, Connection $connection, EntityTypeManagerInterface $entityManager, MessengerInterface $messenger) {
    $this->logger = $logger;
    $this->connection = $connection;
    $this->entityManager = $entityManager;
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
    return 'import_location_modal_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
    $form['modal_title'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="review-msg">',
      '#markup' => $this->t('Upload Location CSV'),
      '#suffix' => '</div>',
    ];
    $form['message'] = [
      '#prefix' => '<div id="status-message"></div>',
    ];
    $form['import_location_csv_file'] = [
      '#type' => 'managed_file',
      '#multiple' => FALSE,
      '#size' => 15,
      '#upload_location' => 'public://location_csv',
      "#upload_validators" => ["file_validate_extensions" => ["csv"]],
    ];
    $form['import_csv_location'] = [
      '#type' => 'button',
      '#value' => $this->t('Import'),
      '#ajax' => [
        'callback' => '::openImportCsv',
        'progress' => [
          'type' => 'throbber',
        ],
      ],
    ];
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function openImportCsv(array &$form, FormStateInterface $form_state): AjaxResponse {

    $response = new AjaxResponse();
    if (!isset($form_state->getValue('import_location_csv_file')[0])) {
      $content = $this->t('Please upload the Location CSV file to start the import process.');
      $response->addCommand(new InvokeCommand(
      '#status-message', 'addClass', ['messages messages--error']));
      $response->addCommand(new HtmlCommand('#status-message', $content));
    }
    else {
      $response->addCommand(new InvokeCommand(
      '#status-message', 'removeClass', ['messages messages--error']));
      $response->addCommand(new HtmlCommand('#status-message', ''));
      $file_obj = $form_state->getValue('import_location_csv_file');
      if ($file_obj) {
        $file_obj_data = File::load(reset($file_obj));
        $file_path = 'public://location_csv/' . $file_obj_data->getFilename();
        $country_name = explode('.', $file_obj_data->getFilename())[0];
        $imported = $this->handleFileData($file_path, $country_name);
        if ($imported) {
          $this->messenger->addMessage(t('Location CSV imported successfully.'));
        }
        $response->addCommand(new CloseModalDialogCommand());
        $response->addCommand(new RedirectCommand(Url::fromRoute('erpw_location.manage_location')->toString()));
      }
    }
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
    return ['config.import_location_modal_form'];
  }

  /**
   * To import data as location.
   *
   * @param string $filepath
   *   The filepath.
   * @param string $country_name
   *   The country name.
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
    $levels = [];
    foreach ($headerData as $header) {
      $levels[] = explode("_", $header)[0] . explode("_", $header)[1];
    }
    $level_count = count(array_unique($levels));
    $langcode_count = $header_count / $level_count;
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
    if (count($csvData) == 0) {
      $this->messenger->addError(t('Please import the CSV with valid hierarchy data.'));
      $response = new AjaxResponse();
      $content = $this->t('Please upload the Location CSV file to start the import process.');
      $response->addCommand(new InvokeCommand(
      '#status-message', 'addClass', ['messages messages--error']));
      $response->addCommand(new HtmlCommand('#status-message', $content));

      return FALSE;
    }
    // Import hierarchy and add translation.
    $hierarchy_level = [];
    for ($k = 0; $k < count($csv_langcodes); $k++) {
      $j = $k;
      $l = $k;
      $langcode = $csv_langcodes[$k];
      for ($i = 1; $i <= $level_count; $i++) {
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
      if (!empty($result) && $result[0]->name == $country_name) {
        $entity_id = $result[0]->id;
        $location_entity = LocationEntity::load($entity_id);
        // If the given translation exists, update the translation.
        if ($location_entity->hasTranslation($langcode)) {
          $location_entity = $location_entity->getTranslation($langcode);
          for ($i = 1; $i <= $level_count; $i++) {
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
    // Create taxonomy with country name.
    if (empty(taxonomy_term_load_multiple_by_name($country_name, 'country'))) {
      $term = Term::create([
        'name' => $country_name,
        'vid' => 'country',
      ]);
      $term->save();
      $country_term_id = $term->id();
    }
    else {
      $term = taxonomy_term_load_multiple_by_name($country_name, 'country');
      $term = reset($term);
      $country_term_id = $term->id();
    }
    // Save term reference in the location entity.
    $taxonomy_reference = ['target_id' => $country_term_id];
    $location_entity->set('field_location_taxonomy_term', $taxonomy_reference);
    $location_entity->save();

    // Import locations from CSV.
    for ($i = 1; $i < count($csvData); $i++) {
      $this->importLocationData($csvData[$i], $csv_langcodes, $country_term_id);
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function importLocationData($location, $csv_langcodes, $country_term_id) {
    $j = 0;
    $parent_term_id = $country_term_id;
    for ($i = 0; $i < count($csv_langcodes); $i++) {
      $term_exists = FALSE;
      for ($k = 0; $k < count($csv_langcodes); $k++) {
        $lang_code = $csv_langcodes[$k];
        // Check if term exists at level $i+1.
        if (isset($location[$j + $k])) {
          $term = taxonomy_term_load_multiple_by_name($location[$j + $k], 'country');
          if (!empty($term)) {
            $term = reset($term);
            $tid = $term->id();
            $ancestors = $this->entityManager->getStorage('taxonomy_term')->loadAllParents($tid);
            $ancestors = array_reverse(array_keys($ancestors));
            $term_depth = taxonomy_term_depth_get_by_tid($tid);
            $current_level = ($j / count($csv_langcodes)) + 1;
            // Check if term exists at the same depth.
            if (($term_depth - 1 == $i + 1) && $term_depth - 1 == $current_level && $ancestors[0] == $country_term_id) {
              $term_exists = TRUE;
              $parent_term_id = $tid;
            }
          }
          if ($term_exists || $k > 0) {

            $term = Term::load($parent_term_id);
            if (!$term->hasTranslation($lang_code)) {
              $term->addTranslation($lang_code, [
                'name' => $location[$j + $k],
              ])->save();
            }
            $parent_term_id = $term->id();
          }
          else {
            // Create the location term.
            $term = Term::create([
              'name' => $location[$j + $k],
              'vid' => 'country',
              'parent' => [
                'target_id' => $parent_term_id,
              ],
            ]);
            $term->save();
            $parent_term_id = $term->id();
          }
        }
      }
      $j = $j + $k;
    }
  }

}
