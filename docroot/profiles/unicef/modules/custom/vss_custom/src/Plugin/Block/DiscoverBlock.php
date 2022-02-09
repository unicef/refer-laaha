<?php

namespace Drupal\vss_custom\Plugin\Block;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Block\BlockBase;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->vssCommonConfigDefault = $container->get('vss_common_config.default');
    $instance->languageManager = $container->get('language_manager');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->domain = $container->get('domain.negotiator');
    $instance->aliaspath = $container->get('path_alias.manager');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $query_params = \Drupal::request()->query->all();
    $is_amp = array_key_exists('amp', $query_params);
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $count = 0;
    $discover = NULL;
    $discover_article = NULL;
    $domain_arr = [];
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'field_domain' => $this->domain->getActiveDomain()->id(),
      'field_sub_category' => 0,
      'vid' => 'categories',
    ]);
    foreach ($terms as $term_id => $val) {
      if ($val && ($val->hasTranslation($langcode) || $val->get('langcode')->value == $langcode)) {
        if (isset($val) && !$val->get('field_related_content')->isEmpty()) {
          $val = $val->getTranslation($langcode);
          foreach ($val->get('field_related_content')->getValue() as $target_id) {
            $node = $this->entityTypeManager->getStorage('node')->load($target_id['target_id']);
            foreach ($node->get('field_domain_access')->getValue() as $v) {
              $domain_arr[] = $v['target_id'];
            }
            if (isset($node) && (in_array($this->domain->getActiveDomain()->id(), $domain_arr)) && ($node->hasTranslation($langcode) || $node->get('langcode')->value == $langcode)) {
              $node = $node->getTranslation($langcode);
              $node_url = ltrim($this->aliaspath->getAliasByPath('/node/' . $target_id['target_id']), '/');

              $node_type = NULL;
              if ($node->getType() != NULL) {
                $node_type = $node->getType();
              }

              $node_read_time = NULL;
              if (!$node->get('field_read_time')->isEmpty()) {
                $node_read_time = $node->get('field_read_time')->getValue()['0']['value'];
              }

              $paragraph_video_time = NULL;
              if ($node_type == 'video') {
                if (!$node->get('field_content')->isEmpty()) {
                  $paragraph = $node->field_content->getValue();
                  foreach ($paragraph as $content_id) {
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
              if ($node_type == 'podcast') {
                if (!$node->get('field_content')->isEmpty()) {
                  $paragraph_podcast = $node->field_content->getValue();
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
              if (!$node->get('field_thumbnail_image')->isEmpty()) {
                $thumbnail_img = $node->get('field_thumbnail_image')->entity->getFileUri();
              }

              if ($node) {
                $discover_article[] = [
                  'term_id' => $term_id,
                  'nid' => $target_id['target_id'],
                  'node_name' => $node->getTitle(),
                  'node_url' => $node_url,
                  'thumbnail_img' => $thumbnail_img,
                  'node_type' => $node_type,
                  'node_read_time' => $node_read_time,
                  'paragraph_video_time' => $paragraph_video_time,
                  'paragraph_audio_time' => $paragraph_podcast_time,
                ];
              }
            }
          }
        }
        $weight = $val->get('weight')->getValue()[0]['value'];
        if ($val->get('weight')->getValue()[0]['value'] == 0) {
          $count = $count + 1;
        }

        if ($count > 1) {
          $weight = $term_length + $count;
        }

        $discover[$weight][] = [
          'id' => $term_id,
          'name' => $val->getName(),
          'color' => $val->get('field_category_color')->getValue()[0]['color'],
          'cat_uri' => $val->get('path')->alias,
          'icon' => $val->get('field_icon')->entity->getFileUri(),
          'description' => $val->get('description')->getValue()[0]['value'],
        ];
      }
    }

    ksort($discover);
    $build['#theme'] = 'discover_block';
    $build['#content'] = $discover;
    $build['#content_node'] = $discover_article;
    $build['#lang_code'] = $langcode;
    $build['#is_amp'] = $is_amp;
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
