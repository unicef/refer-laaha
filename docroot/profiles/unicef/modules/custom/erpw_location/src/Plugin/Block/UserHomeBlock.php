<?php

namespace Drupal\erpw_location\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\erpw_location\LocationService;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "user_home_block",
 *   admin_label = @Translation("User Home Block"),
 * )
 */
class UserHomeBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Session\AccountInterface definition.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The location service.
   *
   * @var \Drupal\erpw_location\LocationService
   */
  protected $locationService;

  /**
   * Entity Manager instance.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
  $plugin_id,
  $plugin_definition,
  AccountInterface $current_user,
  LocationService $location_service,
  EntityTypeManagerInterface $entityManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
    $this->locationService = $location_service;
    $this->entityManager = $entityManager;
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
      $container->get('erpw_location.location_services'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $user = $this->entityManager->getStorage('user')->load($this->currentUser->id());
    $tid = $user->field_location_details->value;
    $tid_array = explode(",", $tid);
    $location = '';
    if (!empty($tid)) {
      $ancestors_prev = $this->locationService->getAllAncestors($tid_array[0]);
      $ancestors = array_reverse($ancestors_prev);
      foreach ($ancestors as $key => $value) {
        $location .= " " . $this->locationService->getTaxonomyTermById($value);
      }
      foreach ($tid_array as $key => $value) {
        if ($key != 0) {
          $location .= " " . $this->locationService->getTaxonomyTermById($value);
        }
      }
    }
    return [
      '#theme' => 'homepage_user_location',
      '#location' => $location,
      '#tid' => !empty($ancestors_prev[0]) ? $ancestors_prev[0] : 0,
      '#cache' => ['max-age' => 0],
    ];
  }
}
