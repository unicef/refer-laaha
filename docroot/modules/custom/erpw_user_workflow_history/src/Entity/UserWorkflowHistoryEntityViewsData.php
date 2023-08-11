<?php

namespace Drupal\erpw_user_workflow_history\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for User workflow history entity entities.
 */
class UserWorkflowHistoryEntityViewsData extends EntityViewsData {

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
