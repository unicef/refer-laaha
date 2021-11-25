<?php

namespace Drupal\vss_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a 'SocialIconsFooterBlock' block.
 *
 * @Block(
 *  id = "hero_banner_block",
 *  admin_label = @Translation("Hero Banner block"),
 * )
 */
class HeroBannerBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
    $instance->routeMatch = $container->get('current_route_match');
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
    $vid = 'categories';
    $parent_tid = $term_id;
    $depth = 1;
    $load_entities = TRUE;
    $child_terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($vid, $parent_tid, $depth, $load_entities);
    $subcat_details = [];
    $cat_details = [];

    if ($child_terms) {
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($term_id);
      if ($term->hasTranslation($langcode)) {
        $term = $term->getTranslation($langcode);
      }
      $name = $term->label();
      $cat_color = $term->get('field_category_color')->color;
      $cat_icon = $term->get('field_icon')->target_id;
      $file = $this->entityTypeManager->getStorage('file')->load($cat_icon);
      $cat_ic = $file->getFileUri();
      $cat_ic = str_replace('public://', 'sites/default/files/', $cat_ic);
      foreach ($child_terms as $k => $child) {
        if ($child->hasTranslation($langcode)) {
          $child = $child->getTranslation($langcode);
        }
        $cat_details['cat_name'] = $name;
        $cat_details['cat_icon'] = $cat_ic;
        $cat_details['cat_color'] = $cat_color;

        $subcat_details[$k]['subcat_name'] = $child->get('name')->value;
        $file = $this->entityTypeManager->getStorage('file')->load($child->get('field_sub_category_thumbnail')->target_id);
        $file_url = $file->getFileUri();
        $subcat_details[$k]['sub_category_thumbnail'] = str_replace('public://', 'sites/default/files/', $file_url);
        $url = $term->label() . '/' . $child->get('name')->value;
        $url = str_replace(' ', '-', $url);
        $url = strtolower($url);
        $subcat_details[$k]['url'] = $url;
      }
    }

    $build['#theme'] = 'hero_banner_block';
    $build['#content'] = $subcat_details;
    $build['#category'] = $cat_details;
    $build['#lang_code'] = $langcode;

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
