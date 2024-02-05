<?php

namespace Drupal\erpw_in_app_notification\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Notification processed entity entities.
 */
class NotificationProcessedEntityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
