<?php

namespace Drupal\vss_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\Cache;

/**
 * Provides a 'LocationSelectorBlock' block.
 *
 * @Block(
 *  id = "location_selector_block",
 *  admin_label = @Translation("Location Selector block"),
 * )
 */
class LocationSelectorBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * Drupal\vss_common_config\VssCommonInterface.
   *
   * @var \Drupal\vss_common_config\VssCommonInterface
   */
  protected $vssCommonService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->languageManager = $container->get('language_manager');
    $instance->vssCommonService = $container->get('vss_common_config.default');
    $instance->pageCacheKillSwitch = $container->get('page_cache_kill_switch');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $lang_id = $this->languageManager->getCurrentLanguage()->getId();
    $data = $this->vssCommonService->getLocationData();
    $html = '';
    if (isset($data['location_selection_title']) && !empty($data['location_selection_title'])) {
      $html .= $data['location_selection_title'];
    }
    if (isset($data['location_selection_description']) && !empty($data['location_selection_description'])) {
      $html .= ' ';
      $html .= strip_tags($data['location_selection_description']);
    }

    $data['location_selection_form'] = \Drupal::formBuilder()->getForm('\Drupal\vss_custom\Form\LocationSelectorForm');

    $string = str_replace('&nbsp;', '', $html);
    $build['#theme'] = 'location_selector_block';
    $build['#content'] = $data;
    if ($lang_id == 'ar') {
      $lang_id = 'ar-SA';
    }
    if ($lang_id == 'es') {
      $lang_id = 'es-ES';
      $voiceId = 30;
      $build['#attached']['drupalSettings']['voiceId'] = $voiceId;
    }
    if ($lang_id == 'en') {
      $lang_id = 'en-US';
      $voiceId = 41;
      $build['#attached']['drupalSettings']['voiceId'] = $voiceId;
    }
    $build['#attached']['drupalSettings']['location_landId'] = $lang_id;
    $build['#attached']['drupalSettings']['location_narrate'] = $string;
    $build['#attached']['drupalSettings']['location_selector'] = TRUE;
    $this->pageCacheKillSwitch->trigger();
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
