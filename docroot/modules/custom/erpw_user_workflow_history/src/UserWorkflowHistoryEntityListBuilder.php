<?php

namespace Drupal\erpw_user_workflow_history;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of User workflow history entity entities.
 *
 * @ingroup erpw_user_workflow_history
 */
class UserWorkflowHistoryEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('User workflow history entity ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\erpw_user_workflow_history\Entity\UserWorkflowHistoryEntity $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.user_workflow_history_entity.edit_form',
      ['user_workflow_history_entity' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
