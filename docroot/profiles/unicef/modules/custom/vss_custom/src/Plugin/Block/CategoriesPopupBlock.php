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
    $instance->domain = $container->get('domain.negotiator');
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
      if ($v != 0) {
        $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
          'tid' => $k,
          'field_domain' => $this->domain->getActiveDomain()->id(),
        ]);
        $term = !empty($terms) ? reset($terms) : FALSE;
        if ($term) {
          if ($term->hasTranslation($langcode) || $term->get('langcode')->value == $langcode) {
            $term = $term->getTranslation($langcode);
          }
          $name = $term->label();
          $cat_popup[$k]['name'] = $name;
          $cat_popup[$k]['cat_icon'] = $term->get('field_icon')->entity->getFileUri();;
          $cat_popup[$k]['cat_color'] = $term->get('field_category_color')->color;
          $cat_popup[$k]['cat_desc'] = strip_tags($term->get('description')->value);
          $cat_popup[$k]['url'] = ltrim($this->aliaspath->getAliasByPath('/taxonomy/term/' . $k), '/');
          $cat_popup[$k]['alt'] = $term->get('field_icon')->getValue()[0]['alt'];
        }
      }
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
