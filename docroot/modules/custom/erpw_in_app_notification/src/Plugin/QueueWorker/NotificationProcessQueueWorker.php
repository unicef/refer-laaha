<?php

namespace Drupal\erpw_in_app_notification\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\Sql\QueryFactory;

/**
 * Notification Process Queue Worker.
 *
 * @QueueWorker(
 *   id = "notification_process_queue_worker",
 *   title = @Translation("Notification Process Queue Worker."),
 *   cron = {"time" = 60}
 * )
 */
class NotificationProcessQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityQuery;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructure for NotificationProcessQueueWorker.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, QueryFactory $entity_query, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityQuery = $entity_query;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.query'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Process item function.
   */
  public function processItem($id) {

  }

}
