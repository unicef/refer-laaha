<?php

namespace Drupal\erpw_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a custom block to Add User according to active language.
 *
 * @Block(
 *   id = "custom_add_user_block",
 *   admin_label = @Translation("Custom Add New User Block"),
 *   category = @Translation("eRPW Custom")
 * )
 */
class CustomAddUserBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new CustomAddUserBlock instance.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $user = \Drupal::currentUser();
    $active_domain_id = \Drupal::service('domain.negotiator')->getActiveDomain()->id();
    $cache_tags = ['custom_block', 'user_list'];
    $cache_id = 'custom_add_user_block_query_' . $user->id() . $active_domain_id;

    // Try to load data from cache.
    $cache_data = \Drupal::cache()->get($cache_id);

    // Check if data is not in cache.
    if (!$cache_data) {
      // If data is not in cache, execute the build logic.
      $link = Link::createFromRoute(t('Add user'), 'erpw_custom.sign_up_form', [], ['query' => ['destination' => '/user-list']]);

      $hasPermission = $user->hasPermission('view users of their own country');

      $markup = '<div class="plus-icon button-with-icon">' . $link->toString() . '</div>';

      // Check if the user has the permission to show the additional markup.
      // todo make this translatable.
      // todo don't redirect them to only english page.
      if ($hasPermission) {
        $markup = '<div id="user-status-listing">User Status Listing<a href="/en/users-status">Users by Status</a></div>' . $markup;
      }

      // Build the render array.
      $build = [
        '#type' => 'markup',
        '#markup' => $markup,
      ];

      // Store the result in cache.
      \Drupal::cache()->set($cache_id, $build, Cache::PERMANENT, $cache_tags);
    }
    else {
      // If data is in cache, use the cached result.
      $build = $cache_data->data;
    }

    // @todo Block cache. - Done
    return $build;
  }

}
