<?php

namespace Drupal\erpw_location;

use Drupal\Core\Cache\Cache;
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
   * @param \Drupal\erpw_location\LocationCookieService $location_cookie
   *   Location cookie service.
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

    $active_domain_id = \Drupal::service('domain.negotiator')->getActiveDomain()->id();
    $cache_tags = ['location'];
    $cache_id = 'location_entities_query_' . $active_domain_id;
    $cache_data = \Drupal::cache()->get($cache_id);

    // Check if data is not in cache.
    if (!$cache_data) {
      // If data is not in cache, execute the build logic.
      $location_entities = $this->entityTypeManager->getStorage('location')->loadByProperties(
        ['type' => 'country', 'status' => 1]);
      $location_options = [];
      foreach ($location_entities as $location) {
        $location_options[$location->id()] = $location->get('name')->getValue()[0]['value'];
      }

      // Store the result in cache.
      \Drupal::cache()->set($cache_id, $location_options, Cache::PERMANENT, $cache_tags);
    }
    else {
      // If data is in cache, use the cached result.
      $location_options = $cache_data->data;
    }

    return $location_options;
  }

  // @todo Cache the result - Done

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
    $active_domain_id = \Drupal::service('domain.negotiator')->getActiveDomain()->id();
    $cache_tags = ['taxonomy_term_list'];
    $cache_id = 'all_ancestors_query_' . $tid . $active_domain_id;
    $cache_data = \Drupal::cache()->get($cache_id);

    // Check if data is not in cache.
    if (!$cache_data) {
      // If data is not in cache, execute the build logic.
      $ancestors = $this->entityTypeManager->getStorage('taxonomy_term')->loadAllParents($tid);
      if (empty($ancestors)) {
        $ancestors = [$tid];
      }
      else {
        $ancestors = array_reverse(array_keys($ancestors));
      }

      // Store the result in cache.
      \Drupal::cache()->set($cache_id, $ancestors, Cache::PERMANENT, $cache_tags);
    }
    else {
      // If data is in cache, use the cached result.
      $ancestors = $cache_data->data;
    }

    // @todo Cache the result - Done
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
    $tid = $this->getTidFromLocationId($location_id);
    $ptids = $this->getAllAncestors($tid);
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

    $cache_tags = ['location', 'taxonomy_term_list'];
    $active_domain_id = \Drupal::service('domain.negotiator')->getActiveDomain()->id();
    $cache_id = 'default_location_query_' . $active_domain_id;
    $cache_data = \Drupal::cache()->get($cache_id);

    // Check if data is not in cache.
    if (!$cache_data) {
      // If data is not in cache, execute the build logic.
      $query = $this->connection->select('location__field_location_taxonomy_term', 'tm');
      $query->innerJoin('taxonomy_term_data', 't', 't.tid = tm.field_location_taxonomy_term_target_id');
      $query->fields('t', ['tid']);
      $result = $query->execute();

      // Store the result in cache.
      \Drupal::cache()->set($cache_id, $result->fetchField(), Cache::PERMANENT, $cache_tags);
    }
    else {
      // If data is in cache, use the cached result.
      $result = $cache_data->data;
    }

    // @todo Cache the query executed result - Done
    return $result;
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
   * @return int|null
   *   The taxonomy term ID if found, or null if not found.
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

  /**
   * Check if only Country level has entry, that is:
   * only the 'location_options' and 'location_tid' keys have values,
   * while all other keys are empty in the given $location_value array.
   *
   * @param array $location_value
   *   The input array containing location values.
   *
   * @return bool
   *   TRUE if only 'location_options' and 'location_tid' have values and all other keys are empty,
   *   FALSE otherwise.
   */
  public function onlyCountryValuePresent(array $location_value): bool {
    // Extract 'location_options' and 'location_tid' from the input array.
    $location_options = $location_value['location_options'];
    $location_tid = $location_value['location_tid'];

    // Check if 'location_options' and 'location_tid' have values while others are empty.
    $hasValues = !empty($location_options) && !empty($location_tid);

    // Iterate through other keys and check if they are empty.
    foreach ($location_value as $key => $value) {
      // Skip 'location_options' and 'location_tid'.
      if ($key === 'location_options' || $key === 'location_tid') {
        continue;
      }

      // Check if the value is not empty.
      if (!empty($value)) {
        $hasValues = FALSE;
        // Break the loop if any non-empty value is found.
        break;
      }
    }

    // Check that location_tid is indeed a location entity id.
    $location_entity_ids = array_keys($this->getLocationEntities());
    if (!in_array($location_tid, $location_entity_ids)) {
      $hasValues = FALSE;
    }

    return $hasValues;
  }

  /**
   * Retrieves the domain associated with a location entity.
   *
   * @param int $location_id
   *   The ID of the location entity.
   *
   * @return string|null
   *   The domain associated with the location entity, or NULL if not found.
   */
  public function getDomainFromLocationEntityId($location_id) {
    // Load the location entity.
    $location_entity = $this->entityTypeManager->getStorage('location')->load($location_id);

    // Get the name of the location entity.
    $location_entity_name = $location_entity->getName();

    // Load all domain entities.
    $domains = $this->entityTypeManager->getStorage('domain')->loadMultiple();

    // Iterate through each domain entity.
    foreach ($domains as $key => $entity) {
      // Get the name of the domain entity.
      $domain_name = $entity->get('name');

      // Check if the location name is part of the domain name.
      if (strpos((string) $domain_name, $location_entity_name) !== FALSE) {
        // If found, store the domain key and exit the loop.
        $location_domain = $key;
        break;
      }
    }

    // Return the domain associated with the location entity.
    return $location_domain ?? NULL;
  }

  /**
   * Retrieves the taxonomy term ID(s) associated with a location entity.
   *
   * This function loads a 'location' entity using the provided location ID
   * and returns the value(s) of the 'field_location_taxonomy_term' field.
   *
   * @param mixed $locationId
   *   The ID of the location entity to load.
   *
   * @return array|null
   *   An array of taxonomy term ID(s) associated with the location entity,
   *   or NULL if the entity could not be loaded or the field is empty.
   *   Each item in the array is an associative array containing:
   *   - target_id: The ID of the referenced taxonomy term.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   Thrown if there is an issue loading the entity.
   */
  public function getTidFromLocationId($locationId) {
    $maxRetries = 50;
    $retryDelay = 100;

    for ($i = 0; $i < $maxRetries; $i++) {
      $location = $this->entityTypeManager->getStorage('location')->load($locationId);

      if ($location) {
        return $location->get('field_location_taxonomy_term')->getValue()[0]['target_id'];
      }

      // If the entity is not loaded, wait for a short period before retrying.
      usleep($retryDelay * 1000);
    }

    // Return $locationId if the entity can't be loaded after max retries,
    // treating it as a taxonomy ID.
    return $locationId;
  }

}
