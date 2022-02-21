<?php

namespace Drupal\vss_custom\Plugin\Block;

use Drupal\paragraphs\Entity\Paragraph;
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
    $instance->pageCacheKillSwitch = $container->get('page_cache_kill_switch');
    $instance->aliaspath = $container->get('path_alias.manager');
    $instance->domain = $container->get('domain.negotiator');
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
    $domain = $this->domain->getActiveDomain()->id();
    $featured_stories = $this->getContentfromTags($term_id, $langcode, $domain);
    shuffle($featured_stories);
    $featured_stories = array_intersect_key($featured_stories, array_flip(array_slice(array_keys($featured_stories), 0, 6)));
    $count = count($featured_stories);
    foreach ($featured_stories as $k => $v) {
      if ($count > 1) {
        $content[$k]['title'] = $v->title;
        $thumbnail = $v->field_thumbnail_image_target_id;
        $node = $this->entityTypeManager->getStorage('node')->load($v->nft_entity_id);
        if ($node->hasTranslation($langcode) || $node->get('langcode')->value == $langcode) {
          $node = $node->getTranslation($langcode);
          if (!$node->get('field_read_time')->isEmpty()) {
            $read_time = $node->field_read_time->getValue()['0']['value'];
            $content[$k]['read_time'] = $read_time;
          }

          $paragraph_video_time = NULL;
          if ($v->type == 'video') {
            if (!$node->get('field_content')->isEmpty()) {
              $paragraph_id = $node->get('field_content')->getValue();
              foreach ($paragraph_id as $content_id) {
                $paragraph_obj = Paragraph::load($content_id['target_id']);
                $paragraph_type = $paragraph_obj->get('type')->getValue()['0']['target_id'];
                if ($paragraph_type == "video") {
                  if (!$paragraph_obj->get('field_video_time')->isEmpty()) {
                    $paragraph_video_time = $paragraph_obj->get('field_video_time')->getValue()['0']['value'];
                    break;
                  }
                }
              }
            }
          }

          $paragraph_podcast_time = NULL;
          if ($v->type == 'podcast') {
            if (!$node->get('field_content')->isEmpty()) {
              $paragraph_podcast = $node->get('field_content')->getValue();
              foreach ($paragraph_podcast as $content_pod_id) {
                $paragraph_pod_obj = Paragraph::load($content_pod_id['target_id']);
                $paragraph_pod_type = $paragraph_pod_obj->get('type')->getValue()['0']['target_id'];
                if ($paragraph_pod_type == "podcast_audio") {
                  if (!$paragraph_pod_obj->get('field_podcast_time')->isEmpty()) {
                    $paragraph_podcast_time = $paragraph_pod_obj->get('field_podcast_time')->getValue()['0']['value'];
                    break;
                  }
                }

              }
            }
          }
        }

        $file = $this->entityTypeManager->getStorage('file')->load($thumbnail);
        if ($file) {
          $thumbnail_final = $file->getFileUri();
          $content[$k]['thumbnail'] = $thumbnail_final;
        }
        $content[$k]['url'] = ltrim($this->aliaspath->getAliasByPath('/node/' . $v->nft_entity_id), '/');
        $content[$k]['type'] = $v->type;
        $content[$k]['video_time'] = $paragraph_video_time;
        $content[$k]['podcast_time'] = $paragraph_podcast_time;

        $this->pageCacheKillSwitch->trigger();
        $build['#theme'] = 'featured_stories_block';
        $build['#content'] = $content;
        $build['#lang_code'] = $langcode;
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

  /**
   * {@inheritdoc}
   */
  public function getContentfromTags($term_id, $langcode, $domain) {
    $query = $this->connection->select('taxonomy_term_field_data', 't');
    $query->leftjoin('taxonomy_term__field_sub_category', 'sc', 'sc.entity_id = t.tid');
    $query->join('taxonomy_term__field_tags', 'ft', 'ft.entity_id = t.tid');
    $query->join('taxonomy_term__field_domain', 'dm', 'dm.entity_id = t.tid');
    $query->join('node__field_tags', 'nft', 'ft.field_tags_target_id = nft.field_tags_target_id');
    $query->join('node__field_thumbnail_image', 'tmb', 'nft.entity_id = tmb.entity_id');
    $query->join('node__field_sub_category', 'nscat', 'nscat.entity_id = nft.entity_id');
    $query->join('taxonomy_term__parent', 'tp', 'tp.entity_id = nscat.field_sub_category_target_id AND tp.parent_target_id = t.tid');
    $query->join('node_field_data', 'n', 'nft.entity_id = n.nid');
    $query->join('node__field_domain_access', 'nd', 'nft.entity_id = nd.entity_id');
    $query->condition('nd.field_domain_access_target_id', $domain);
    $query->condition('t.vid', 'categories');
    $query->condition('nft.langcode', $langcode);
    $query->condition('t.langcode', $langcode);
    $query->condition('t.tid', $term_id);
    $query->condition('dm.field_domain_target_id', $domain);
    $query->condition('sc.field_sub_category_value', 1, '!=');
    $query->fields('nft', ['entity_id']);
    $query->fields('nft', ['entity_id']);
    $query->fields('tmb', ['field_thumbnail_image_target_id']);
    $query->fields('n', ['title']);
    $query->fields('n', ['type']);
    $terms = $query->execute()->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);

    return $terms;
  }

}
