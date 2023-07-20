<?php

namespace Drupal\erpw_location\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\views\Views;
use Drupal\user\Entity\User;

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
    $view = Views::getView('manage_in_review_webform_services_listing');
    $view->setDisplay('page_1');
    $view->execute();
    // Unset rows that are not in review workflow states as per roles.
    // TODO replace this code with query alter.
    foreach ($view->result as $key => $row) {
      $sid = $row->sid;
      $webformSubmission = \Drupal::entityTypeManager()->getStorage('webform_submission')->load($sid);
      $state = $webformSubmission->getData()['erpw_workflow']['workflow_state'];
      $current_user = User::load(\Drupal::currentUser()->id());
      // Get the user's roles.
      $roles = $current_user->getRoles();
      if (in_array('service_provider_focal_point', $roles)) {
        if ($state != 'in_review_with_focal_point' && $state != 'edits_in_review_with_focal_point' && $state != 'deletion_in_review_with_focal_point') {
          unset($view->result[$key]);
        }
      }
      elseif (in_array('country_admin', $roles) || in_array('interagency_gbv_coordinator', $roles)) {
        if ($state != 'in_review' && $state != 'edits_in_review_with_gbv_coordination' && $state != 'deletion_in_review_with_gbv_coordination') {
          unset($view->result[$key]);
        }
      }
      elseif (in_array('administrator', $roles) || in_array('super_admin', $roles)) {
        if ($state != 'in_review_with_focal_point' && $state != 'in_review' && $state != 'edits_in_review_with_focal_point' && $state != 'edits_in_review_with_gbv_coordination' && $state != 'deletion_in_review_with_focal_point' && $state != 'deletion_in_review_with_gbv_coordination') {
          unset($view->result[$key]);
        }
      }
      else {
        unset($view->result[$key]);
      }
    }
    $count = count($view->result);

    return [
      '#theme' => 'pending_service_count',
      '#count' => $count,
      '#description' => $this->t('Service provider information changes'),
      '#manage_service_link' => Url::fromRoute('view.manage_in_review_webform_services_listing.page_1')->toString(),
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
