<?php

namespace Drupal\vss_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
      $vid = 'categories';
      $parent_tid = $term_id;
      $depth = 1;
      $load_entities = TRUE;
      $child_terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($vid, $parent_tid, $depth, $load_entities);
      $subcat_details = [];
      $cat_details = [];

      if ($child_terms) {
        $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
          'tid' => $term_id,
          'field_domain' => $this->domain->getActiveDomain()->id(),
        ]);
        $term = !empty($terms) ? reset($terms) : FALSE;
        if ($term->hasTranslation($langcode) || $term->get('langcode')->value == $langcode) {
          $term = $term->getTranslation($langcode);
          $name = $term->label();
          $cat_color = $term->get('field_category_color')->color;
          $cat_icon = $term->get('field_icon')->target_id;
          $file = $this->entityTypeManager->getStorage('file')->load($cat_icon);
          if ($file) {
            $cat_ic = $file->getFileUri();
            $cat_ic = str_replace('public://', 'sites/default/files/', $cat_ic);
          }
        }
        foreach ($child_terms as $k => $child) {
          if ($child->get('field_domain')->target_id == $this->domain->getActiveDomain()->id() &&
            ($child->hasTranslation($langcode) || $child->get('langcode')->value == $langcode)
          ) {
            if ($child->hasTranslation($langcode)) {
              $child = $child->getTranslation($langcode);
            }
            $cat_details['cat_name'] = $name;
            $cat_details['cat_icon'] = $cat_ic;
            $cat_details['cat_color'] = $cat_color;
            $subcat_details[$k]['subcat_name'] = $child->get('name')->value;
            $file = $this->entityTypeManager->getStorage('file')->load($child->get('field_sub_category_thumbnail')->target_id);
            if ($file) {
              $file_url = $file->getFileUri();
              $subcat_details[$k]['sub_category_thumbnail'] = str_replace('public://', 'sites/default/files/', $file_url);
            }
            $subcat_details[$k]['url'] = ltrim($this->aliaspath->getAliasByPath('/taxonomy/term/' . $child->get('tid')->value), '/');
          }
        }
      }
    }
    if ($this->routeMatch->getRouteName() == 'entity.node.canonical') {
      $nid = $this->routeMatch->getRawParameter('node');
      $node = $this->entityTypeManager->getStorage('node')->load($nid);
      $arr = [];
      $parent_id = [];
      foreach ($node->field_sub_category->getValue() as $value) {
        if (!empty($value['target_id'])) {
          $parent = $this->entityTypeManager->getStorage('taxonomy_term')->loadParents($value['target_id']);
          $parent = reset($parent);
          if ($parent) {
            $term_id = $parent->id();
            $parent_id[$term_id] = $term_id;
            $langcode = $this->languageManager->getCurrentLanguage()->getId();
            $vid = 'categories';
            $parent_tid = $term_id;
            $depth = 1;
            $load_entities = TRUE;
            $arr[$value['target_id']] = $value['target_id'];
          }
        }
      }

      foreach ($parent_id as $val) {
        $child_terms1 = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($vid, $val, $depth, $load_entities);
        $cat_details['cat_name'] = '';
        $cat_details['cat_icon'] = '';
        foreach ($child_terms1 as $child) {
          if ($child->get('field_domain')->target_id == $this->domain->getActiveDomain()->id() &&
            ($child->hasTranslation($langcode) || $child->get('langcode')->value == $langcode)
          ) {
            if ($child->hasTranslation($langcode)) {
              $child = $child->getTranslation($langcode);
            }
            if ((!in_array($child->get('tid')->value, $arr))) {
              $subcat_tid = $child->get('tid')->value;
              $subcat_details[$subcat_tid]['subcat_name'] = $child->get('name')->value;
              $file = $this->entityTypeManager->getStorage('file')->load($child->get('field_sub_category_thumbnail')->target_id);
              if ($file) {
                $file_url = $file->getFileUri();
                $subcat_details[$subcat_tid]['sub_category_thumbnail'] = str_replace('public://', 'sites/default/files/', $file_url);
              }
              $subcat_details[$subcat_tid]['url'] = ltrim(
                $this->aliaspath->getAliasByPath('/taxonomy/term/' . $child->get('tid')->value), '/'
              );
            }
          }
        }
      }

    }
    // dump($subcat_details);
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
