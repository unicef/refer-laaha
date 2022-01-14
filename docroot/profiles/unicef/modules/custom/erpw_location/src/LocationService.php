<?php

namespace Drupal\erpw_location;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManager;

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
   * LocationService constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityManager
   *   Entity Manager Object.
   * @param \Drupal\Core\Language\LanguageManager $languageManager
   *   Location Manager object.
   */
  public function __construct(EntityTypeManagerInterface $entityManager, LanguageManager $languageManager) {
    $this->entityManager = $entityManager;
    $this->languageManager = $languageManager;
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
        $location_entity->getTranslation($langcode)->get('level_' . $i)->getValue()[0]['value'] : $location_entity->get('level_' . $i)->getValue()[0]['value'];
        array_push($location_levels, $level_label);
      }

    }
    return $location_levels;
  }

  /**
   * Get subsequent children a location term.
   */
  public function getChildrenByTid($location_tid, $q) {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $tree = $this->entityManager->getStorage('taxonomy_term')->loadTree(
        'country',
        $location_tid,
        1,
        TRUE
        );
    $result = [];
    foreach ($tree as $term) {
      $term_name = $term->hasTranslation($langcode) ? $term->getTranslation($langcode)->get('name')->value : $term->get('name')->value;
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
  public function taxonomyTermExist($tid) {
    $term = $this->entityManager
      ->getStorage('taxonomy_term')
      ->load($tid);
    return $term->id();
  }

  /**
   * Process taxonomy data.
   */
  public function processTaxonomyData($string, $pid, $level = 0) {
    if ($level == 4) {
      $level_term_id = $this->taxonomyTermCreate($string, 'country', [$pid]);
      return $level_term_id;
    }
    if ($this->clean($string) != 0) {

      $term_string_level = $this->clean($string);
      $level_term_id = $this->taxonomyTermExist($term_string_level);
    }
    else {

      $level_term_id = $this->taxonomyTermCreate($string, 'country', [$pid]);
    }
    return $level_term_id;
  }

}
