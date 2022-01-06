<?php

namespace Drupal\erpw_location;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManager;

/**
 * Class LocationService.
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

}
