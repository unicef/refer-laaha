<?php

namespace Drupal\erpw_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;

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
    $link = Link::createFromRoute(t('Add user'), 'erpw_custom.sign_up_form', [], ['query' => ['destination' => '/user-list']]);

    $build = [
      '#type' => 'markup',
      '#markup' => '<div class="plus-icon button-with-icon">' . $link->toString() . '</div>',
    ];

    return $build;
  }

}
