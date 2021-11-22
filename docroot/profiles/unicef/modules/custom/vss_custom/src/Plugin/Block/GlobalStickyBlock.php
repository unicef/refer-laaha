<?php

namespace Drupal\vss_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'SocialIconsFooterBlock' block.
 *
 * @Block(
 *  id = "global_sticky_block",
 *  admin_label = @Translation("Global Sticky block"),
 * )
 */
class GlobalStickyBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\vss_common_config\VssCommonInterface definition.
   *
   * @var \Drupal\vss_common_config\VssCommonInterface
   */
  protected $vssCommonConfigDefault;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->vssCommonConfigDefault = $container->get('vss_common_config.default');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $content = $this->vssCommonConfigDefault->getCategories();
    $build['#theme'] = 'global_sticky_block';
    $build['#content'] = $content;

    return $build;
  }

}
