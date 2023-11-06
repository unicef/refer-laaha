<?php

namespace Drupal\erpw_location;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Class is used for the locations services.
 */
class LocationService {
  /**
   * Entity Manager instance.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * An language manager instance.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Database Connection instance.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The Location Cookie Service.
   *
   * @var \Drupal\erpw_location\LocationCookieService
   */
  protected $locationCookie;

  /**
   * LocationService constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Manager Object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Location Manager object.
   * @param \Drupal\Core\Database\Connection $connection
   *   Connection Object.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LanguageManagerInterface $language_manager,
    Connection $connection,
    LocationCookieService $location_cookie) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->connection = $connection;
    $this->locationCookie = $location_cookie;
  }

  /**
   * Get Country Level names from location entity id.
   */
  public function getLocationLevels($location_entity_id) {
    $location_entity = $this->entityTypeManager->getStorage('location')->load($location_entity_id);
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
    if (!$location_tid) {
      return;
    }
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $tree = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree(
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
    $new_term = $this->entityTypeManager->getStorage('taxonomy_term')->create([
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
    $entities = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
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
    $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($update_tid_value);
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
    $location_entities = $this->entityTypeManager->getStorage('location')->loadByProperties(
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
    $ancestors = $this->entityTypeManager->getStorage('taxonomy_term')->loadAllParents($tid);
    $ancestors = array_reverse(array_keys($ancestors));
    return $ancestors;
  }

  /**
   * Taxonomy exist check.
   */
  public function getTaxonomyTermById($id) {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($id);
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
    $query = $this->entityTypeManager->getStorage('location')->getQuery();
    $query->condition('field_location_taxonomy_term.target_id', $tid);
    $query->accessCheck(FALSE);
    $location_entity = $query->execute();
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
      $child_terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('country', $ptid, NULL, FALSE);
      // All children term with zero level parent id.
      $terms = [$ptid];
      foreach ($child_terms as $child_term) {
        $terms[] = (int) $child_term->tid;
      }
      return $terms;
    }
    else {
      return [];
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
    else {
      $this->locationCookie->setDefaultCookieValue();
      return $this->locationCookie->getCookieValue();
    }
  }

  /**
   * Validating Location By Term ID.
   *
   * @param int $location_id
   *   The location id.
   * @param string $bundle
   *   The entity bundle.
   * @param int $org
   *   The organisation.
   * @param int $service_type
   *   The service type.
   *
   * @return int
   *   Return term id of the location.
   */
  public function getSavedLocation($location_id, $bundle, $org = NULL, $service_type = NULL) {
    $query = $this->connection->select('node', 'n');
    $query->innerJoin('node__field_location', 'fl', 'fl.entity_id = n.nid');
    if ($bundle == 'service_provider' && !empty($org) && !empty($service_type)) {
      $query->innerJoin('node__field_select_organisation', 'org', 'org.entity_id = n.nid');
      $query->innerJoin('node__field_service_type', 'st', 'st.entity_id = n.nid');
      $query->condition('st.field_service_type_target_id', $service_type);
      $query->condition('org.field_select_organisation_target_id', $org);
    }
    $query->fields('n', ['nid']);
    $query->condition('n.type', $bundle, '=');
    if (!empty($location_id) && is_array($location_id)) {
      $query->condition('fl.field_location_target_id', $location_id, 'IN');
    }
    else {
      $query->condition('fl.field_location_target_id', $location_id);
    }
    $result = $query->execute();
    $node_id = $result->fetchField();

    return (isset($node_id) && is_numeric($node_id)) ? $node_id : NULL;
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

  /**
   * Get user pending service count.
   *
   * @param int $uid
   *   The user id.
   * @param array $roles
   *   The user roles.
   */
  public function getPendingServiceCount($uid, array $roles) {
    $query = $this->connection->select('node', 'n');
    $query->leftJoin('node__field_location', 'fl', 'fl.entity_id = n.nid');
    $query->leftJoin('node__field_select_organisation', 'org', 'org.entity_id = n.nid');
    $query->leftJoin('content_moderation_state_field_data', 'cms', 'cms.content_entity_id = n.nid');
    $query->condition('n.type', 'service_provider');
    $query->condition('cms.moderation_state', 'draft');
    if (isset($roles[1]) && $roles[1] == 'country_admin' || isset($roles[1]) && $roles[1] == 'interagency_gbv_coordinator') {
      $loc_ids = $this->getUserLocationTermIds($uid);
      $query->condition('fl.field_location_target_id', $loc_ids, 'IN');
    }
    elseif (isset($roles[1]) && $roles[1] == 'service_provider_focal_point') {
      $org_id = $this->getUserOragisation($uid);
      $loc_ids = $this->getUserLocationTermIds($uid);
      $query->condition('org.field_select_organisation_target_id', $org_id);
      $query->condition('fl.field_location_target_id', $loc_ids, 'IN');
    }

    // Get the total draft services.
    $count = $query->countQuery()->execute()->fetchField();

    return $count;
  }

  /**
   * Get user current location term ids.
   *
   * @param int $uid
   *   The user id.
   */
  public function getUserLocationTermIds($uid) {
    $user = $this->entityTypeManager->getStorage('user')->load($uid);
    if (!empty($user->field_location) && !empty($user->field_location->getValue())) {
      $tid = $user->field_location->getValue()[0]['target_id'];
      return $this->getChildrenByParent($tid);
    }

    return [];
  }

  /**
   * Get user current organisation.
   *
   * @param int $uid
   *   The user id.
   */
  protected function getUserOragisation($uid) {
    $user = $this->entityTypeManager->getStorage('user')->load($uid);
    if (!empty($user->field_organisation) && !empty($user->field_organisation->getValue())) {
      $org_id = $user->field_organisation->getValue()[0]['target_id'];
    }

    return $org_id ?? '';
  }

  /**
   * Get taxonomy term ID by name.
   *
   * @param string $vocabulary
   *   The machine name of the vocabulary.
   * @param string $termName
   *   The name of the term to search for.
   *
   * @return int|null The taxonomy term ID if found, or null if not found.
   */
  public function getTaxonomyTermIdByName($vocabulary, $termName) {
    $query = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', $vocabulary)
      ->condition('name', $termName)
      ->accessCheck(FALSE)
      ->range(0, 1);

    $tids = $query->execute();

    if (!empty($tids)) {
      return reset($tids);
    }

    return NULL;
  }

  /**
   * Checks if a term ID corresponds to a location entity.
   *
   * @param int $tid
   *   The term ID to check.
   *
   * @return bool
   *   TRUE if the term ID is a location entity (in the predefined list), FALSE otherwise.
   */
  public function isLocationEntity($tid) {
    $location_ids = array_keys($this->getLocationEntities());
    if (in_array($tid, $location_ids)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Get the country term ID associated with a location entity.
   *
   * @param int $country_location_id
   *   The location entity ID for which the country term ID is requested.
   *
   * @return int|null
   *   The country term ID if found, or NULL if not found.
   */
  public function getCountryTidbyLocationId($country_location_id) {
    $country_tid = NULL;
    $location_entity = $this->entityTypeManager->getStorage('location')->load($country_location_id);
    if (!empty($location_entity->get('field_location_taxonomy_term')->getValue())) {
      $country_tid = $location_entity->get('field_location_taxonomy_term')->getValue()[0]['target_id'];
    }
    return $country_tid;
  }

}
