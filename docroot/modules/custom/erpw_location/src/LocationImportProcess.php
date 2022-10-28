<?php

namespace Drupal\erpw_location;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\erpw_location\Entity\LocationEntity;
use Drupal\taxonomy\Entity\Term;

/**
 * Manages location import.
 */
class LocationImportProcess {

  use StringTranslationTrait;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The entity manager.
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
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a EntityReferenceRevisionsOrphanManager object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(Connection $connection,
    EntityTypeManagerInterface $entity_manager,
    LanguageManagerInterface $language_manager,
    MessengerInterface $messenger) {
    $this->connection = $connection;
    $this->entityManager = $entity_manager;
    $this->languageManager = $language_manager;
    $this->messenger = $messenger;
  }

  /**
   * Batch operation for importing location.
   *
   * @param array $items
   *   The row of location.
   * @param array $header
   *   The header of location.
   * @param Iterable|array $context
   *   The context array.
   */
  public function batchImportLocationItem(array $items, array $header, &$context) {
    $location = $items['location'];
    $csv_langcodes = $items['csv_langcodes'];
    $country_term_id = $items['country_term_id'];
    $level_count = $items['level_count'];

    $this->importLocationData($location, $header, $csv_langcodes, $country_term_id, $level_count);

    $context['message'] = 'Importing Location Items...';
    $context['results'][] = $items;

  }

  /**
   * Batch dispatch submission finished callback.
   */
  public static function batchSubmitFinished($success, $results, $operations) {
    return \Drupal::service('erpw_location.import_location')->doBatchSubmitFinished($success, $results, $operations);
  }

  /**
   * Sets a batch for processing location import.
   */
  public function setBatch($file_obj) {
    $file_obj_data = $this->entityManager->getStorage('file')->load(reset($file_obj));
    $file_path = 'public://location_csv/' . $file_obj_data->getFilename();
    $country_name = explode('.', $file_obj_data->getFilename())[0];
    $country_name = explode("_", $country_name)[0];
    if (($handle = fopen($file_path, 'r')) === FALSE) {
      return;
    }

    // Read the csv data.
    $header_data = [];
    $csv_data = [];
    while (($data = fgetcsv($handle)) !== FALSE) {
      if (empty($header_data)) {
        $header_data = $data;
      }
      else {
        $csv_data[] = $data;
      }
    }
    fclose($handle);
    $header_count = count($header_data);
    $levels = [];
    foreach ($header_data as $header) {
      $levels[] = explode("_", $header)[0] . explode("_", $header)[1];
    }
    $level_count = count(array_unique($levels));
    $langcode_count = $header_count / $level_count;
    $csv_langcodes = [];
    for ($i = 0; $i < $langcode_count; $i++) {
      $csv_langcodes[$i] = explode("_", $header_data[$i])[2];
    }
    // Check if the language in the csv is an active language.
    $active_languages = $this->languageManager->getLanguages();
    $active_languages_list = array_keys($active_languages);
    foreach ($csv_langcodes as $langcode) {
      if (!in_array($langcode, $active_languages_list)) {
        $this->messenger->addError($this->t('%langcode is not a valid langcode available in the system.', [
          '%langcode' => $langcode,
        ]));
        return FALSE;
      }
    }
    if (count($csv_data) == 0) {
      $this->messenger->addError($this->t('Please import the CSV with valid hierarchy data.'));
      return FALSE;
    }

    // Import hierarchy and add translation.
    $hierarchy_level = [];
    for ($k = 0; $k < count($csv_langcodes); $k++) {
      $j = $k;
      $l = $k;
      $langcode = $csv_langcodes[$k];
      for ($i = 1; $i <= $level_count; $i++) {
        $hierarchy_level['level_' . $i] = $csv_data[0][$j];
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
            $location_entity->set('level_' . $i, $csv_data[0][$l]);
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
    $operations = [];
    $items = [];
    for ($i = 1; $i < count($csv_data); $i++) {
      $items = [
        'location' => $csv_data[$i],
        'csv_langcodes' => $csv_langcodes,
        'country_term_id' => $country_term_id,
        'level_count' => $level_count,
      ];
      $operations[] = ['_import_location_batch_dispatcher',
        [
          'erpw_location.import_location:batchImportLocationItem',
          $items,
          $header_data,
        ],
      ];
    }

    $batch = [
      'title' => $this->t('Importing location'),
      'operations' => $operations,
      'init_message' => $this->t('Importing...'),
      'progress_message' => $this->t('Processed @current out of @total.'),
      'error_message' => $this->t('An error occurred during processing'),
      'finished' => [LocationImportProcess::class, 'batchSubmitFinished'],
    ];

    batch_set($batch);
  }

  /**
   * Finished callback for the batch process.
   *
   * @param bool $success
   *   Whether the batch completed successfully.
   * @param array $results
   *   The results array.
   * @param array $operations
   *   The operations array.
   */
  public function doBatchSubmitFinished($success, array $results, array $operations) {
    if ($success) {
      $message = $this->formatPlural(
        count($results),
        'One item processed.', '@count items processed.'
      );
    }
    else {
      $message = $this->t('Finished with an error.');
    }
    $this->messenger->addStatus($message);
  }

  /**
   * {@inheritdoc}
   */
  protected function importLocationData($location, $header, $csv_langcodes, $country_term_id, $level_count) {
    $j = 0;
    $parent_term_id = $country_term_id;
    for ($i = 0; $i < count($csv_langcodes); $i++) {
      $term_exists = FALSE;
      for ($k = 0; $k < count($csv_langcodes); $k++) {
        $lang_code = $csv_langcodes[$k];
        // Check if term exists at level $i+1.
        if (isset($location[$j + $k])) {
          $terms = taxonomy_term_load_multiple_by_name($location[$j + $k], 'country');
          if (!empty($terms)) {
            foreach ($terms as $term) {
              if (($j + $k) > '0') {
                $header_key = explode('_', $header[$j + $k]);
                if ($header_key[1] > 1) {
                  $parent_name = $header_key[0] . '_' . ($header_key[1] - 1) . '_' . $header_key[2];
                  $parent_key = array_search($parent_name, $header);
                  if ($parent_key) {
                    $parent = [];
                    $parent = taxonomy_term_load_multiple_by_name($location[$parent_key], 'country');
                    if (!empty($parent)) {
                      $parent_term_id = '';
                      foreach ($parent as $parent_term) {
                        if ($parent_term->id() != $term->parent->target_id) {
                          $parent_term_id = $parent_term->id();
                        }
                      }
                      if ($parent_term_id) {
                        continue;
                      }
                    }
                  }
                }
              }
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
          }
          if ($term_exists || $k > 0) {
            $term = $this->entityManager->getStorage('taxonomy_term')->load($parent_term_id);
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
            if ($i == ($level_count - 1)) {
              $ancestors = $this->entityManager->getStorage('taxonomy_term')->loadAllParents($term->id());
              $ancestors = array_reverse(array_keys($ancestors));
              $query = $this->connection->insert('erpw_location');
              $query->fields([
                'country_tid',
                'level1',
                'level2',
                'level3',
                'level4',
              ]);
              $query->values([
                $country_term_id,
                $ancestors[1] ?? '',
                $ancestors[2] ?? '',
                $ancestors[3] ?? '',
                $ancestors[4] ?? '',
              ]);
              $query->execute();
            }
          }
        }
      }
      $j = $j + $k;
    }
  }

}
