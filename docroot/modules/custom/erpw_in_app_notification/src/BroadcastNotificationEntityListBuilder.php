<?php

namespace Drupal\erpw_in_app_notification;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list controller for the broadcast notification entity entity type.
 */
class BroadcastNotificationEntityListBuilder extends EntityListBuilder {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a new BroadcastNotificationEntityListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, DateFormatterInterface $date_formatter) {
    parent::__construct($entity_type, $storage);
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build['table'] = parent::render();

    $total = $this->getStorage()
      ->getQuery()
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    $build['summary']['#markup'] = $this->t('Total broadcast notification entities: @total', ['@total' => $total]);
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['title'] = $this->t('Title');
    $header['type'] = $this->t('Type');
    $header['scheduled_option'] = $this->t('Scheduled Option');
    $header['message_type'] = $this->t('Message Type');
    $header['last_sent'] = $this->t('Sent Time');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\erpw_in_app_notification\BroadcastNotificationEntityInterface $entity */
    $row['title'] = $entity->toLink();
    $row['type'] = $entity->get('field_type')->value == 'scheduled' ? t('Scheduled') : t('No recurrence');
    if (!is_null($entity->get('field_scheduled_options')->value)) {
      $row['scheduled_option'] = $entity->get('field_scheduled_options')->getFieldDefinition()->getSettings()['allowed_values'][$entity->get('field_scheduled_options')->value];
    }
    else {
      $row['scheduled_option'] = t('N/A');
    }
    $row['message_type'] = $entity->get('field_message_type')->getFieldDefinition()->getSettings()['allowed_values'][$entity->get('field_message_type')->value];
    $row['last_sent'] = $entity->get('field_last_sent')->value == '1701367200' ? t('Pending') : date('d/m/y - H:i:s', $entity->get('field_last_sent')->value);
    return $row + parent::buildRow($entity);
  }

  /**
   * Builds a renderable list of operation links for the entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity on which the linked operations will be performed.
   *
   * @return array
   *   A renderable array of operation links.
   *
   * @see \Drupal\Core\Entity\EntityListBuilder::buildRow()
   */
  public function buildOperations(EntityInterface $entity) {
    $build = parent::buildOperations($entity);
    if ($entity->get('field_message_type')->value == 'predefined') {
      unset($build['#links']['translate']);
    }
    if ($entity->get('field_type')->value != 'scheduled') {
      unset($build['#links']['edit']);
      unset($build['#links']['delete']);
    }
    // Generate the URL for the add form with the duplicateid parameter.
    $url = Url::fromRoute('erpw_in_app_notification.duplicatebroadcast', ['id' => $entity->get('id')->value]);
    $duplicatebroadcast = Link::fromTextAndUrl(t('Duplicate'), $url);
    $duplicateLink = [
      'title' => $duplicatebroadcast->getText(),
      'weight' => 90,
      'url' => $duplicatebroadcast->getUrl(),
    ];
    // Add the "duplicatebroadcast" link to the operations.
    $build['#links']['duplicatebroadcast'] = $duplicateLink;
    return $build;
  }

}
