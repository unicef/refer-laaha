<?php

namespace Drupal\erpw_in_app_notification;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Notification entity entities.
 *
 * @ingroup erpw_in_app_notification
 */
class NotificationEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Notification entity ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\erpw_in_app_notification\Entity\NotificationEntity $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.notification_entity.edit_form',
      ['notification_entity' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
