<?php

namespace Drupal\erpw_location;

use Drupal\Core\Database\Connection;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class is used for the locations services.
 */
class LocationService {
  /**
   * Entity Manager instance.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * Database Connection instance.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * LocationService constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   Entity Manager Object.
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   Location Manager object.
   * @param \Drupal\Core\Database\Connection $connection
   *   Connection Object.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_manager,
    LanguageManager $language_manager,
    Connection $connection) {
    $this->entityManager = $entity_manager;
    $this->languageManager = $language_manager;
    $this->connection = $connection;
  }

  /**
   * Get Country Level names from location entity id.
   */
  public function getLocationLevels($location_entity_id) {
    $location_entity = $this->entityManager->getStorage('location')->load($location_entity_id);
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $location_levels = [];
    // Get all level label names.
    for ($i = 1; $i <= 4; $i++) {
      if ($location_entity->get('level_' . $i)->getValue()) {
        $level_label = $location_entity->hasTranslation($langcode) ?
        $location_entity->getTranslation($langcode)
          ->get('level_' . $i)->getValue()[0]['value'] : $location_entity->get('level_' . $i)->getValue()[0]['value'];
        array_push($location_levels, $level_label);
      }

    }
    return $location_levels;
  }

  /**
   * Get subsequent children a location term.
   */
  public function getChildrenByTid($location_tid, $q = '') {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $tree = $this->entityManager->getStorage('taxonomy_term')->loadTree(
      'country',
      $location_tid,
      1,
      TRUE
    );
    $result = [];
    foreach ($tree as $term) {
      $term_name = $term->hasTranslation($langcode) ?
        $term->getTranslation($langcode)->get('name')->value : $term->get('name')->value;

      if (str_contains(strtolower($term_name), strtolower($q))) {
        $result[$term->id()] = $term_name;
      }
    }
    return $result;
  }

  /**
   * Clean the autocomplete string.
   */
  public function clean($string) {
    return (int) preg_replace('/[^0-9]/', '', $string);
  }

  /**
   * Create taxonomy data.
   */
  public function taxonomyTermCreate($term, $vocabulary, array $parent = []) {

    // Create the taxonomy term.
    $new_term = $this->entityManager->getStorage('taxonomy_term')->create([
      'name' => $term,
      'vid' => $vocabulary,
      'parent' => $parent,
    ]);

    // Save the taxonomy term.
    $new_term->save();

    // Return the taxonomy term id.
    return $new_term->id();
  }

  /**
   * Taxonomy exist check.
   */
  public function taxonomyTermExist($name, $parent) {
    $entities = $this->entityManager->getStorage('taxonomy_term')->loadByProperties([
      'name' => $name,
      'parent' => $parent,
    ]);
    if ($entities) {
      $first_match = reset($entities);
      return $first_match->id();
    }
    return '';
  }

  /**
   * Taxonomy Update check.
   */
  public function taxonomyTermUpdate($update_tid_value, $string) {
    $term = $this->entityManager->getStorage('taxonomy_term')->load($update_tid_value);
    $term->setName($string);
    $term->save();
    return 1;
  }

  /**
   * Process taxonomy data.
   */
  public function processTaxonomyData($string, $pid, $level = 0, $mode = "", $update_tid_value = 0) {
    preg_match('/\(\d+\)/', $string, $matches);
    if ($level == 4 && $mode == 'update') {
      // If string not matches like value (5).
      if (empty($matches[0])) {
        $level_term_id = $this->taxonomyTermExist($string, $pid);
        if ($mode == 'update' && $level_term_id) {
          return 0;
        }
        if ($level_term_id) {
          return $level_term_id;
        }
        else {
          if ($mode == 'update') {
            $level_term_id = $this->taxonomyTermUpdate($update_tid_value, $string);
            return $level_term_id;
          }
          else {
            $level_term_id = $this->taxonomyTermCreate($string, 'country', [$pid]);
          }
          return $level_term_id;
        }
      }
    }
    // Check if string matches like value (5).
    if (!empty($matches[0])) {
      $tid_array = explode("(", $string);
      $level_term_id = $this->taxonomyTermExist(trim($tid_array[0]), $pid);
    }

    else {
      $level_term_id = $this->taxonomyTermExist($string, $pid);
      if ($mode == 'update' && $level_term_id) {
        return 0;
      }
      if ($level_term_id) {
        return $level_term_id;
      }
      else {
        if ($mode == 'update') {
          $level_term_id = $this->taxonomyTermUpdate($update_tid_value, $string);
          return $level_term_id;
        }
        else {
          $level_term_id = $this->taxonomyTermCreate($string, 'country', [$pid]);
        }
        return $level_term_id;
      }
    }
    return $level_term_id;
  }

  /**
   * Get location entities.
   */
  public function getLocationEntities() {
    $location_entities = $this->entityManager->getStorage('location')->loadByProperties(
      ['type' => 'country', 'status' => 1]);
    $location_options = [];
    foreach ($location_entities as $location) {
      $location_options[$location->id()] = $location->get('name')->getValue()[0]['value'];
    }
    return $location_options;
  }

  /**
   * Get location entities.
   */
  public function addEprwLocation($tid, $country_term_id) {
    $ancestors = $this->getAllAncestors($tid);
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

  /**
   * Get ancestors of taxonomy.
   */
  public function getAllAncestors($tid) {
    $ancestors = $this->entityManager->getStorage('taxonomy_term')->loadAllParents($tid);
    $ancestors = array_reverse(array_keys($ancestors));
    return $ancestors;
  }

  /**
   * Taxonomy exist check.
   */
  public function getTaxonomyTermById($id) {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $term = $this->entityManager->getStorage('taxonomy_term')->load($id);
    if ($term) {
      $term_name = $term->hasTranslation($langcode) ?
        $term->getTranslation($langcode)->get('name')->value : $term->get('name')->value;
      return $term_name;
    }
    return '';
  }

  /**
   * Get location entities.
   */
  public function getLocationEntityByTid($tid) {
    $location_id = $this->getLocationSingleEntityIdByTid($tid);
    return $this->getLocationLevels($location_id);
  }

  /**
   * Get location entity.
   */
  public function getLocationSingleEntityIdByTid($tid) {
    $location_entity_id = "";
    $location_entity = $this->entityManager->getStorage('location')->loadByProperties(
      [
        'field_location_taxonomy_term' => $tid,
        'status' => 1,
        'type' => 'country',
      ]
    );
    if (!empty($location_entity)) {
      $location_entity_id = array_keys($location_entity)[0];
    }
    return $location_entity_id;
  }

  /**
   * Get children by parent id.
   *
   * @param int $location_id
   *   The location id.
   *
   * @return array
   *   Return of children with parent term id.
   */
  public function getChildrenByParent($location_id) {
    $ptids = $this->getAllAncestors($location_id);
    $terms = [];
    if (!empty($ptids)) {
      // Getting zero level parent id.
      $ptid = reset($ptids);
      $child_terms = $this->entityManager->getStorage('taxonomy_term')->loadTree('country', $ptid, NULL, FALSE);
      // All children term with zero level parent id.
      $terms = [$ptid];
      foreach ($child_terms as $child_term) {
        $terms[] = (int) $child_term->tid;
      }
      return $terms;
    }
  }

  /**
   * Get default country ID.
   *
   * @return int
   *   Return of default location id.
   */
  public function getDefaultLocation() {
    $query = $this->connection->select('location__field_location_taxonomy_term', 'tm');
    $query->innerJoin('taxonomy_term_data', 't', 't.tid = tm.field_location_taxonomy_term_target_id');
    $query->fields('t', ['tid']);
    $result = $query->execute();

    return $result->fetchField();
  }

  /**
   * Get user default Location ID.
   *
   * @param object $user
   *   The user object.
   *
   * @return int
   *   Return of default location id.
   */
  public function getUserDefaultLocation($user) {
    if (!empty($user->field_location)) {
      return $user->field_location->getValue()[0]['target_id'];
    }
  }

  /**
   * Validating Location By Term ID.
   *
   * @param int $location_id
   *   The location id.
   * @param string $current_language
   *   The current language.
   *
   * @return int
   *   Return term id of the location.
   */
  public function getSavedLocation($location_id, $current_language = NULL) {
    $language = $current_language ?? 'en';
    $query = $this->connection->select('node__field_location', 'fl');
    $query->fields('fl', ['field_location_target_id']);
    $query->condition('fl.langcode', $language);
    $query->condition('fl.bundle', 'referral_path_way');
    if (!empty($location_id) && is_array($location_id)) {
      $query->condition('fl.field_location_target_id', $location_id, 'IN');
    }
    else {
      $query->condition('fl.field_location_target_id', $location_id);
    }
    $result = $query->execute();
    $saved_loc_id = $result->fetchField();

    return (isset($saved_loc_id) && is_numeric($saved_loc_id)) ? $saved_loc_id : NULL;
  }

  /**
   * Get user location.
   *
   * @param object $user
   *   The user object.
   */
  public function getUserLocation($user) {
    if (!empty($user->field_location)) {
      if (!empty($user->field_location->getValue()) && is_array($user->field_location->getValue())) {
        $tid = $user->field_location->getValue()[0]['target_id'];
        $tid_array = $user->field_location->getValue();
      }
      else {
        $tid = $user->field_location->value;
      }
    }
    $location = '';
    if (!empty($tid)) {
      foreach ($tid_array as $key => $value) {
        if ($key != 0) {
          $location .= $this->getTaxonomyTermById($value['target_id']) . ", ";
        }
      }
      $ancestors_prev = $this->getAllAncestors($tid);
      $ancestors = array_reverse($ancestors_prev);
      foreach ($ancestors as $value) {
        $location .= $this->getTaxonomyTermById($value) . ", ";
      }
      $location = substr(trim($location), 0, -1);
      return $location;
    }
  }

}
