<?php
namespace Drupal\erpw_in_app_notification\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
/**
 * Notification Process Queue Worker.
 *
 * @QueueWorker(
 *   id = "notification_process_queue_worker",
 *   title = @Translation("Notification Process Queue Worker."),
 *   cron = {"time" = 60}
 * )
 */
class NotificationProcessQueueWorker extends QueueWorkerBase {
  
  /**
   * Process item function.
   */
  public function processItem($notification) {
    // Process Item.
    // @todo
  }
}