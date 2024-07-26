<?php

namespace Drupal\erpw_webform\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\domain\DomainNegotiatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a custom block to Add a Service according to active language.
 *
 * @Block(
 *   id = "custom_add_service_block",
 *   admin_label = @Translation("Custom Add New Service Block"),
 *   category = @Translation("eRPW Custom")
 * )
 */
class AddServiceBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The Current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The domain negotiator service.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * The cache service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Constructs a new CustomAddUserBlock instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager, AccountProxyInterface $current_user, DomainNegotiatorInterface $domain_negotiator, CacheBackendInterface $cache) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
    $this->currentUser = $current_user;
    $this->domainNegotiator = $domain_negotiator;
    $this->cache = $cache;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager'),
      $container->get('current_user'),
      $container->get('domain.negotiator'),
      $container->get('cache.default')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $user = $this->currentUser;
    $active_domain_id = $this->domainNegotiator->getActiveDomain()->id();
    $cache_tags = ['custom_block', 'user_list'];
    $cache_id = 'custom_add_service_block_' . $user->id() . $active_domain_id;

    // Try to load data from cache.
    $cache_data = $this->cache->get($cache_id);

    // Check if data is not in cache.
    if (!$cache_data) {
      // If data is not in cache, execute the build logic.
      $link = Link::createFromRoute(t('Add a New Service'), 'erpw_webform.service_webforms', [], []);

      $hasPermission = $user->hasPermission('access content');

      $markup = '<div class="new-service-type button-with-icon">' . $link->toString() . '</div>';

      // Check if the user has the permission to show the additional markup.
      if ($hasPermission) {
        $translated_text = t('Service Changes Submissions');
        $current_language = $this->languageManager->getCurrentLanguage()->getId();
        $link_url = '/' . $current_language . '/service-providers';
        $link = Link::fromTextAndUrl(t('Service Updates History'), Url::fromUserInput($link_url));
        $translated_markup = '<div id="block-changes-submissions">' . $translated_text . $link->toString() . '</div>';
        $markup = $translated_markup . $markup;
      }

      // Build the render array.
      $build = [
        '#type' => 'markup',
        '#markup' => $markup,
        '#attached' => [
          'library' => [
            'erpw_webform/erpw_webform_offline_add',
          ],
        ],
      ];

      // Store the result in cache.
      $this->cache->set($cache_id, $build, Cache::PERMANENT, $cache_tags);
    }
    else {
      // If data is in cache, use the cached result.
      $build = $cache_data->data;
    }

    // @todo Block cache. - Done
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $user_role = $account->getRoles();
    if ($account->isAuthenticated() && !in_array('txb_service_viewer', $user_role)) {
      return AccessResult::allowed();
    }
    return AccessResult::forbidden();
  }

}
