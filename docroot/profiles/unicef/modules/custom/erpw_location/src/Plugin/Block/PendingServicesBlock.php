<?php

namespace Drupal\erpw_location\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\views\Views;

/**
 * Provides a block for counting pending services.
 *
 * @Block(
 *   id = "pending_count_services_block",
 *   admin_label = @Translation("Pending Count Services Block"),
 * )
 */
class PendingServicesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The Current user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
  }

  /**
   * Required Dependency injection were added .
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return static
   */

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
   array $configuration,
  $plugin_id,
  $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the total pending services.
    $view = Views::getView('moderated_content');
    $view->setDisplay('moderated_content');
    $view->execute();
    $count = count($view->result);

    return [
      '#theme' => 'pending_service_count',
      '#count' => $count,
      '#description' => $this->t('Service provider information changes'),
      '#manage_service_link' => Url::fromRoute('view.moderated_content.moderated_content')->toString(),
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'erpw view pending service block');
  }

}
