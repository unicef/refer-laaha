<?php

namespace Drupal\erpw_in_app_notification\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines 'notification_deletion_process_queue_worker' queue worker.
 *
 * @QueueWorker(
 *   id = "notification_deletion_process_queue_worker",
 *   title = @Translation("Notification Deletion Process Queue Worker"),
 *   cron = {"time" = 86400}
 * )
 */
class NotificationDeletionProcessQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructure for NotificationDeletionProcessQueueWorker.
   */
  public function __construct(array $configuration,
  $plugin_id,
  $plugin_definition,
  EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($id) {
    // Load entity storages.
    $np_storage = $this->entityTypeManager->getStorage('notification_processed_entity');
    $n_storage = $this->entityTypeManager->getStorage('notification_entity');

    // Load and delete notification processed entity if exists.
    $np_entity = $np_storage->load($id);
    if ($np_entity) {
      $np_storage->delete([$np_entity]);
    }

    if ($np_entity) {
      $np_entity_name = $np_entity->label();

      // Extract NIDs from the notification processed entity name.
      if (preg_match('/NID (\d+) (Type|EID)/', $np_entity_name, $matches)) {
        $nid = $matches[1];

        // Load and delete notification entity if exists.
        $n_entity = $n_storage->load($nid);
        if ($n_entity) {
          $n_storage->delete([$n_entity]);
        }
      }
    }
  }

  /**
   * Extracts NIDs after 'NID' and before 'Type' or 'EID' from each entity name.
   *
   * @param mixed $entity_name
   *   An array of entity names of Notification Processed Entities.
   *
   * @return mixed
   *   An array of unique extracted numbers.
   */
  public function extractNidFromProcessedNotifications(mixed $entity_name) {
    if (preg_match('/NID (\d+) (Type|EID)/', $entity_name, $matches)) {
      $number = $matches[1];
    }
    return $number;
  }

}
