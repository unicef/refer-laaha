<?php

namespace Drupal\vss_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\Cache;

/**
 * Provides a 'DisclaimerPopUpBlock' block.
 *
 * @Block(
 *  id = "disclaimer_pop_up_block",
 *  admin_label = @Translation("Disclaimer pop up block"),
 * )
 */
class DisclaimerPopUpBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
    $data = $this->vssCommonService->getDisclaimer();
    $html = '';
    if (isset($data['disclaimer_title']) && !empty($data['disclaimer_title'])) {
      $html .= $data['disclaimer_title'];
    }
    if (isset($data['disclaimer_description']) && !empty($data['disclaimer_description'])) {
      $html .= ' ';
      $html .= strip_tags($data['disclaimer_description']);
    }
    $string = str_replace('&nbsp;', '', $html);

    $build['#theme'] = 'disclaimer_pop_up_block';
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
    $build['#attached']['drupalSettings']['disclaimer_landId'] = $lang_id;
    $build['#attached']['drupalSettings']['disclaimer_narrate'] = $string;
    $build['#attached']['drupalSettings']['disclaimer'] = TRUE;
    $cookie_name = "player";
    $cookie_langid = $lang_id;
    $cookie_value = $string;
    $cookie_voice = $voiceId;
    setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
    setcookie("voice", $cookie_voice, time() + (86400 * 30), "/");
    setcookie("langid", $lang_id, time() + (86400 * 30), "/");
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
