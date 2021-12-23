<?php

namespace Drupal\vss_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\Cache;

/**
 * Provides a 'SocialIconsFooterBlock' block.
 *
 * @Block(
 *  id = "categories_popup_block",
 *  admin_label = @Translation("Categories popup block"),
 * )
 */
class CategoriesPopupBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
    $content = $this->vssCommonConfigDefault->getCategories()['homepage_hero'];
    foreach ($content as $k => $v) {
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($k);
      if ($term->hasTranslation($langcode)) {
        $term = $term->getTranslation($langcode);
      }
      $name = $term->label();
      $cat_popup[$k]['name'] = $name;
      $cat_icon = $term->get('field_icon')->target_id;
      $file = $this->entityTypeManager->getStorage('file')->load($cat_icon);
      if ($file) {
        $cat_ic = $file->getFileUri();
        $cat_ic = str_replace('public://', 'sites/default/files/', $cat_ic);
      }
      $cat_popup[$k]['cat_icon'] = $cat_ic;
      $cat_popup[$k]['cat_desc'] = strip_tags($term->get('description')->value);
      $cat_popup[$k]['url'] = ltrim($this->aliaspath->getAliasByPath('/taxonomy/term/' . $k), '/');
    }
    $build['#theme'] = 'categories_popup_block';
    $build['#content'] = $cat_popup;
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
