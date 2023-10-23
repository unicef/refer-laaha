<?php

namespace Drupal\erpw_in_app_notification;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class HelperService.
 */
class HelperService implements HelperServiceInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new HelperService object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

}
