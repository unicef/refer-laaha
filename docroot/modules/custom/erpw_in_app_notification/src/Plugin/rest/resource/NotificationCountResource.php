<?php

namespace Drupal\erpw_in_app_notification\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;

/**
 * Provides a Notification Count Resource.
 *
 * @RestResource(
 *   id = "notification_count_resource",
 *   label = @Translation("Notification Count Resource"),
 *   uri_paths = {
 *     "canonical" = "/ajax/getnotificaitoncount"
 *   }
 * )
 */
class NotificationCountResource extends ResourceBase {

  /**
   * Responds to entity GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   */
  public function get() {
    $response = [
      'count' => \Drupal::service('erpw_in_app_notification.default')->getCountByUid(),
    ];
    return new ModifiedResourceResponse($response, 200);
  }

}
