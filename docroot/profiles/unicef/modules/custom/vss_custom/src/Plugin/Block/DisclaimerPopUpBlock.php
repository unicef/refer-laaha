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
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $id = $this->languageManager->getCurrentLanguage()->getId();
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
    $build['#attached']['drupalSettings']['disclaimer_landId'] = $id;
    $build['#attached']['drupalSettings']['disclaimer_narrate'] = $string;
    $build['#attached']['drupalSettings']['disclaimer'] = true;
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
