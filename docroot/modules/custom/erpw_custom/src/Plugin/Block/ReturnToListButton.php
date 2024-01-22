<?php

namespace Drupal\erpw_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a custom block to Add back button to return to a specific list of data.
 *
 * @Block(
 *   id = "return_to_list_button",
 *   admin_label = @Translation("Return To List Block"),
 *   category = @Translation("eRPW Custom")
 * )
 */
class ReturnToListButton extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The RouteMatch service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a new CustomAddUserBlock instance.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RequestStack $request_stack, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->requestStack = $request_stack;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
          $configuration,
          $plugin_id,
          $plugin_definition,
          $container->get('request_stack'),
          $container->get('current_route_match')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $referer = $this->requestStack->getCurrentRequest()->headers->get('referer');
    $cache_tags = ['return_to_list_button'];
    $cache_id = 'return_to_list_button_query_' . serialize($referer);
    $cache_data = \Drupal::cache()->get($cache_id);

    // Check if data is not in cache.
    if (!$cache_data) {
      // If data is not in cache, execute the build logic.
      $referer = $this->requestStack->getCurrentRequest()->headers->get('referer');
      $markup = '<a href="' . $referer . '" class="button button-border">' . t('Return to list') . '</a>';
      $build = [
        '#markup' => $markup,
        '#weight' => 105,
      ];

      // Store the result in cache.
      \Drupal::cache()->set($cache_id, $build, Cache::PERMANENT, $cache_tags);
    }
    else {
      // If data is in cache, use the cached result.
      $build = $cache_data->data;
    }

    // @todo Block cache - Done
    return $build;
  }

}
