<?php

namespace Drupal\vss_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'SocialIconsFooterBlock' block.
 *
 * @Block(
 *  id = "featured_stories_block",
 *  admin_label = @Translation("Featured Stories block"),
 * )
 */
class FeaturedStoriesBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->vssCommonConfigDefault = $container->get('vss_common_config.default');
    $instance->languageManager = $container->get('language_manager');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->routeMatch = $container->get('current_route_match');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    if ($this->routeMatch->getRouteName() == 'entity.taxonomy_term.canonical') {
      $term_id = $this->routeMatch->getRawParameter('taxonomy_term');
    }
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    $featured_stories = $this->getContentfromTags($term_id, $langcode);
    foreach ($featured_stories as $k => $v) {
      $content[$k]['title'] = $v->title;
      $thumbnail = $v->field_thumbnail_image_target_id;
      $file = $this->entityTypeManager->getStorage('file')->load($thumbnail);
      if ($file) {
        $thumbnail_final = $file->getFileUri();
        $content[$k]['thumbnail'] = str_replace('public://', 'sites/default/files/', $thumbnail_final);
      }
      $content[$k]['url'] = str_replace(' ', '-', $v->title);
      $content[$k]['type'] = $v->type;
    }

    $build['#theme'] = 'featured_stories_block';
    $build['#content'] = $content;
    $build['#lang_code'] = $langcode;
    $build['#feature_count'] = count($content);

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getContentfromTags($term_id, $langcode) {
    $query = $this->connection->select('taxonomy_term_field_data', 't');
    $query->leftjoin('taxonomy_term__field_sub_category', 'sc', 'sc.entity_id = t.tid');
    $query->join('taxonomy_term__field_tags', 'ft', 'ft.entity_id = t.tid');
    $query->join('node__field_tags', 'nft', 'ft.field_tags_target_id = nft.field_tags_target_id');
    $query->join('node__field_thumbnail_image', 'tmb', 'nft.entity_id = tmb.entity_id');
    $query->join('node__field_sub_category', 'nscat', 'nscat.entity_id = nft.entity_id');
    $query->join('taxonomy_term__parent', 'tp', 'tp.entity_id = nscat.field_sub_category_target_id AND tp.parent_target_id = t.tid');
    $query->join('node_field_data', 'n', 'nft.entity_id = n.nid');
    $query->condition('t.vid', 'categories');
    $query->condition('n.langcode', $langcode);
    $query->condition('t.tid', $term_id);
    $query->condition('sc.field_sub_category_value', 1, '!=');
    $query->fields('nft', ['entity_id']);
    $query->fields('tmb', ['field_thumbnail_image_target_id']);
    $query->fields('n', ['title']);
    $query->fields('n', ['type']);
    $terms = $query->execute()->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);
    return $terms;
  }

}
