<?php

namespace Drupal\vss_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides a 'SocialIconsFooterBlock' block.
 *
 * @Block(
 *  id = "subcategory_hero_block",
 *  admin_label = @Translation("Sub Category Hero block"),
 * )
 */
class SubCategoryHeroBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\vss_common_config\VssCommonInterface definition.
   *
   * @var \Drupal\vss_common_config\VssCommonInterface
   */
  protected $vssCommonConfigDefault;

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
    $instance->vssCommonConfigDefault = $container->get('vss_common_config.default');
    $instance->languageManager = $container->get('language_manager');
    $instance->routeMatch = $container->get('current_route_match');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->aliaspath = $container->get('path_alias.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    if ($this->routeMatch->getRouteName() == 'entity.taxonomy_term.canonical') {
      $term_id = $this->routeMatch->getRawParameter('taxonomy_term');
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($term_id);
      if ($term->get('field_sub_category')->value == 1) {
        if ($term->hasTranslation($langcode)) {
          $term = $term->getTranslation($langcode);
        }
        $nid = $term->get('field_hero_content')->target_id;
        if ($nid) {
          $node = $this->entityTypeManager->getStorage('node')->load($nid);
          $hero = [];
          if ($node) {
            if ($node->hasTranslation($langcode)) {
              $node = $node->getTranslation($langcode);
            }
            $title = $node->title->value;
            $hero['title'] = $title;
            $thumbnail = $node->field_thumbnail_image->entity->getFileUri();
            $hero['thumbnail'] = $thumbnail;
            $hero['url'] = ltrim($this->aliaspath->getAliasByPath('/node/' . $nid), '/');
            $hero['type'] = $node->title->value;
          }
          $build['#theme'] = 'subcategory_hero_block';
          $build['#content'] = $hero;
          $build['#lang_code'] = $langcode;
        }
      }
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
