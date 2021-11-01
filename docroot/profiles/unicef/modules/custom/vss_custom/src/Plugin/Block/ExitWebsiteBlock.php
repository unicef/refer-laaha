<?php

namespace Drupal\vss_custom\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "exit_website_block",
 *   admin_label = @Translation("Exit Website Block"),
 * )
 */
class ExitWebsiteBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
    $instance->vssCommonService = $container->get('vss_common_config.default');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'exit_website',
      '#data' => $this->vssCommonService->getHeaderPhone(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['exit_website_settings'] = $form_state->getValue('exit_website_settings');
  }

}
