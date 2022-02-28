<?php

namespace Drupal\erpw_location\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\erpw_location\LocationService;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\erpw_location\LocationCookie;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "select_user_location",
 *   admin_label = @Translation("Select User Location"),
 * )
 */
class UserHomeBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configfactory;

  /**
   * The cookie as a service.
   *
   * @var \Drupal\erpw_location\LocationCookie
   */
  protected $locationCookie;

  /**
   * The tempstore factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LocationService $location_service,
    EntityTypeManagerInterface $entity_manager,
    ConfigFactoryInterface $config_factory,
    LocationCookie $location_cookie,
    PrivateTempStoreFactory $temp_store_factory) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->locationService = $location_service;
    $this->entityManager = $entity_manager;
    $this->configfactory = $config_factory;
    $this->locationCookie = $location_cookie;
    $this->tempStoreFactory = $temp_store_factory->get('erpw_location_collection');
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
      $container->get('erpw_location.location_services'),
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('erpw_location.location_cookie'),
      $container->get('tempstore.private'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form_config = $this->configfactory->get('erpw_location.settings');
    $cookie_value = $this->locationCookie->getCookieValue();
    $tid = $this->tempStoreFactory->get(base64_decode($cookie_value));
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
    $title = !empty($form_config->get('title')) ? $form_config->get('title') : "";
    $descripton = !empty($form_config->get('description')) ? $form_config->get('description') : "";
    return [
      '#theme' => 'homepage_user_location',
      '#title' => $title,
      '#description' => $descripton,
      '#location' => $location,
      '#tid' => !empty($ancestors_prev[0]) ? $ancestors_prev[0] : 0,
      '#cache' => [
        'contexts' => ['user'],
      ],
    ];
  }

}
