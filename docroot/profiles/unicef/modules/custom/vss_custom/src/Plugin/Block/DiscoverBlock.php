<?php

namespace Drupal\vss_custom\Plugin\Block;

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Url;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides a 'SocialIconsFooterBlock' block.
 *
 * @Block(
 *  id = "discover_block",
 *  admin_label = @Translation("Discover block"),
 * )
 */
class DiscoverBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $content = $this->vssCommonConfigDefault->getCategories();

    foreach ($content['homepage_hero'] as $term_id => $val) {
      $term_obj = Term::load($term_id);

      foreach ($term_obj->get('field_related_content')->getValue() as $target_id) {
        $node = \Drupal::entityTypeManager()->getStorage('node')->load($target_id['target_id']);
        $node_url = Url::fromRoute('entity.node.canonical', ['node' => $target_id['target_id']]);
        $node_url = $node_url->toString();
        $paragraph = $node->field_assets->getValue();
        $paragraph_obj = Paragraph::load($paragraph['1']['target_id']);
        $paragraph_type = $paragraph_obj->get('type')->getValue()[0]['target_id'];
        $video_time = 0;
        if ($paragraph_type == 'external_videos' || $paragraph_type == 'video') {
          $video_time = $paragraph_obj->get('field_video_time')->getValue()[0]['value'];
        }
        if ($node) {
          $discover_article[] = [
            'term_id' => $term_id,
            'nid' => $target_id['target_id'],
            'node_name' => $node->getTitle(),
            'node_url' => $node_url,
            'thumbnail_img' => $node->get('field_thumbnail_image')->entity->getFileUri(),
            'paragraph_type' => $paragraph_type,
            'video_time' => $video_time,
          ];
        }
      }

      if ($term_obj) {
        $discover[$term_obj->get('weight')->getValue()[0]['value']] = [
          'id' => $term_id,
          'name' => $term_obj->getName(),
          'color' => $term_obj->get('field_category_color')->getValue()[0]['color'],
          'cat_uri' => $term_obj->get('path')->alias,
          'icon' => $term_obj->get('field_icon')->entity->getFileUri(),
          'description' => $term_obj->get('description')->getValue()[0]['value'],
        ];
      }
    }

    ksort($discover);
    $build['#theme'] = 'discover_block';
    $build['#content'] = $discover;
    $build['#content_node'] = $discover_article;
    $build['#lang_code'] = $langcode;
    $build['#cache']['tags'] = $this->getCacheTags();
    $build['#cache']['contexts'] = $this->getCacheContexts();

    return $build;
  }

  /**
   * Get cache tags.
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(),
     ['config:vss_common_config.vsscommonconfig', 'vss_common_config']);
  }

  /**
   * Get cache Contexts.
   */
  public function getCacheContexts() {
    return Cache::mergeTags(parent::getCacheContexts(), ['url.path']);
  }

}
