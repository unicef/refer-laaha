<?php

namespace Drupal\erpw_webform\Plugin\views\cache;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\views\Plugin\views\cache\CachePluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Dynamic Views cache metadata views cache plugin.
 *
 * Advanced caching of query results for Views displays allowing the
 * specification of cache tags, cache contexts, and output / results cache
 * lifetime (which is used to calculate max-age).
 *
 * @ingroup views_cache_plugins
 *
 * @ViewsCache(
 *   id = "dynamic_views_cache_context",
 *   title = @Translation("Dynamic Views Cache Context"),
 *   help = @Translation("Caching based on tags, context, and max-age. Caches will persist until any related cache tags are invalidated or the max-age is reached.")
 * )
 */
class DynamicViewsCacheContext extends CachePluginBase {


  /**
   * {@inheritdoc}
   *
   * @var \Drupal\views\Plugin\views\cache\CachePluginBase
   */
  protected $plugin;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    $this->plugin = $plugin_id;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function summaryTitle() {
    return 'Dynamic Views Cache Context';
  }

  /**
   * {@inheritdoc}
   */
  public function alterCacheMetadata(CacheableMetadata $cache_metadata) {
    $cache_metadata->addCacheContexts(['servicelist']);
  }

}
