<?php

namespace Drupal\erpw_in_app_notification\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\Sql\QueryFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    $notification = $this->entityTypeManager->getStorage('notification_entity')->load($id);
    $npstorage = \Drupal::entityTypeManager()->getStorage('notification_processed_entity');
    // Check the entity.
    if ($notification->get('field_entity_type')->getString() == 'service') {
      // Load event details.
      $event = \Drupal::service('erpw_in_app_notification.default')->getEventDetailsByEventId($notification->get('field_event_id')->getString());
      // Process the message.
      $message = $event['message'];
      $message = str_replace("@service_name", $notification->get('field_title')->getString(), $message);
      $message = str_replace("@operation", $notification->get('field_operation')->getString(), $message);
      if (!$notification->get('field_workflow_action')->isEmpty()) {
        $message = str_replace("@status", $notification->get('field_workflow_action')->getString(), $message);
      }
      $author = $notification->getOwner();
      $name = $author->get('field_first_name')->getString();
      $name .= ' ' . $author->get('field_last_name')->getString();
      $message = str_replace("@focal_point_name", $name, $message);

      // Process the users.
      if (!$notification->get('field_specific_user')->getString()) {
        $roles = explode(', ', $notification->get('field_role')->getString());
        if (in_array('service_provider_staff', $roles)) {
          // @todo
        }
        if (in_array('service_provider_focal_point', $roles)) {

          // FP based on organisation.
          $org = $notification->get('field_organisation')->getString();
          $userquery = \Drupal::entityTypeManager()->getStorage('user')->getQuery();
          $uids = $userquery->condition('status', 1)
            ->condition('roles', 'service_provider_focal_point')
            ->condition('field_organisation', $org)
            ->accessCheck(FALSE)
            ->execute();
        }
        if (in_array('interagency_gbv_coordinator', $roles) || in_array('country_admin', $roles)) {
          // Based on the root location.
          // \Drupal::service('erpw_location.location_services')->getChildrenByTid();
        }
      }
      else {
        // @todo
      }

      // Create notification.
      if (!empty($uids)) {
        foreach ($uids as $uid) {
          $npstorage->create([
            'field_notification_id' => $notification->id(),
            'field_read' => 0,
            'field_message_string' => $message,
            'field_recipient' => $uid,
            'field_icon' => isset($event['icon_uri']) ? \Drupal::service('file_url_generator')->generateAbsoluteString($event['icon_uri']) : NULL,
          ])->save();
        }
      }

      // Make the Notification entity processed.
      $notification->set('field_processed', TRUE);
      $notification->save();
    }

  }

}
