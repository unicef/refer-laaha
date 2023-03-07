<?php

namespace Drupal\erpw_field_access\Plugin\Derivative;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a default implementation for menu link plugins.
 *
 * @see \Drupal\user\Plugin\Derivative\UserLocalTask
 */
class FieldAccessMenuLink extends DeriverBase implements ContainerDeriverInterface {
  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates a UserLocalTask object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The translation manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, TranslationInterface $string_translation) {
    $this->entityTypeManager = $entity_type_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('string_translation')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [];

    $entity_definitions = $this->entityTypeManager->getDefinitions();
    foreach ($entity_definitions as $bundle_type_id => $bundle_entity_type) {
      if (!$entity_type_id = $bundle_entity_type->getBundleOf()) {
        continue;
      }

      $entity_type = $entity_definitions[$entity_type_id];
      if (!$base_route = $entity_type->get('field_ui_base_route')) {
        continue;
      }

      if ($entity_type_id === 'node') {
        $this->derivatives["erpw_field_access_{$bundle_type_id}"] = [
          'route_name' => 'erpw_field_access.settings',
          'weight' => 10,
          'title' => $this->t('eRPW Field Access'),
          'base_route' => $base_route,
        ] + $base_plugin_definition;
      }

    }

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
