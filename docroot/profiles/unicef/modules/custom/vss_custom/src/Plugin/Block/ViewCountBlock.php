<?php

namespace Drupal\vss_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'SocialIconsFooterBlock' block.
 *
 * @Block(
 *  id = "view_count_block",
 *  admin_label = @Translation("View Count block"),
 * )
 */
class ViewCountBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Language\LanguageManagerInterface.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->pageCacheKillSwitch = $container->get('page_cache_kill_switch');
    $instance->routeMatch = $container->get('current_route_match');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    if ($this->routeMatch->getRouteName() == 'entity.node.canonical') {
      $nid = $this->routeMatch->getRawParameter('node');
    }
    $this->pageCacheKillSwitch->trigger();
    $view_count = $this->getViewCount($nid);
    $build['#theme'] = 'view_count_block';
    $build['#content'] = empty($view_count) ? 1 : $view_count;

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getViewCount($nid) {
    $query = $this->connection->select('nodeviewcount', 'nvc');
    $query->condition('nvc.nid', $nid);
    $query->addExpression("COUNT(DISTINCT (nvc.nid))", "nvc");
    $query->groupBy("nvc.uip");
    $node = $query->execute()->fetchField();
    return $node;
  }

}
