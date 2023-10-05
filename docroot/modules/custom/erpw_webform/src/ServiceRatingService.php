<?php

namespace Drupal\erpw_webform;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\domain\DomainNegotiatorInterface;

/**
 * Class is used for the service rating services.
 */
class ServiceRatingService {

    /**
   * Drupal\domain\DomainNegotiatorInterface definition.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * Drupal\language\ConfigurableLanguageManagerInterface definition.
   *
   * @var \Drupal\language\ConfigurableLanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;


  /**
   * ServiceRatingService constructor.
   * 
   * @param \Drupal\domain\DomainNegotiatorInterface $domain_negotiator
   *   DomainNegotiator object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   Entity Manager Object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Location Manager object.
   */
  public function __construct(
    DomainNegotiatorInterface $domain_negotiator,
    EntityTypeManagerInterface $entity_manager,
    LanguageManagerInterface $language_manager,
    ) {
    $this->domainNegotiator = $domain_negotiator;
    $this->entityTypeManager = $entity_manager;
    $this->languageManager = $language_manager;
  }

  /**
   * Helper function to load and process entities based on type.
   *
   * @param string $entityType
   *   The entity type to load (e.g., 'node').
   * @param string $bundle
   *   The bundle (content type) of the entities to load.
   * @param string $fieldName
   *   The name of the field containing allowed domains.
   *
   * @return array
   *   An array of processed entity options.
   */
  public function loadAndProcessEntities($entityType, $bundle, $fieldName) {
    $options = [];

    // Get the current domain and language.
    $current_domain = $this->domainNegotiator->getActiveDomain()->id();
    $current_language = $this->languageManager->getCurrentLanguage()->getId();

    // Load entities of the specified type and bundle.
    $entities = $this->entityTypeManager->getStorage($entityType)
      ->loadByProperties(['type' => $bundle]);

    foreach ($entities as $id => $entity) {
      $name = $entity->get('title')->getValue()[0]['value'];
      $allowed_domains = $entity->get($fieldName)->getValue();
      foreach ($allowed_domains as $domain_id => $allowed_domain) {
        if ($current_domain == $allowed_domain['target_id']) {
          if ($entity->hasTranslation($current_language)) {
            $translated = $entity->getTranslation($current_language);
            $options[$id] = $translated->get('title')->getValue()[0]['value'];
          }
          else {
            $options[$id] = $name;
          }
        }
      }
    }

    return $options;
  }

}
