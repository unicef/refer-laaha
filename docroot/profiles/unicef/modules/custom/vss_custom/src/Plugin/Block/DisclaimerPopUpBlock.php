<?php

namespace Drupal\vss_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'DisclaimerPopUpBlock' block.
 *
 * @Block(
 *  id = "disclaimer_pop_up_block",
 *  admin_label = @Translation("Disclaimer pop up block"),
 * )
 */
class DisclaimerPopUpBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Language\LanguageManagerInterface.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->languageManager = $container->get('language_manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $view_builder = $this->entityTypeManager->getViewBuilder('taxonomy_term');
    $storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $taxonomy_term = $storage->load(1);
    $id = $this->languageManager->getCurrentLanguage()->getId();
    $render = $view_builder->view($taxonomy_term, 'full', $id);
    $html = '';
    $html .= $render['#taxonomy_term']->getName();
    if ($render['#taxonomy_term']->hasField('description') && !empty($render['#taxonomy_term']->get('description')->first())) {
      $html .= '\r\n';
      $html .= strip_tags($render['#taxonomy_term']->description->value);
    }
    $build['#theme'] = 'disclaimer_pop_up_block';
    $build['#content'] = $render;
    $build['#attached']['drupalSettings']['landId'] = $id;
    $build['#attached']['drupalSettings']['narrate'] = $html;
    return $build;
  }

}
