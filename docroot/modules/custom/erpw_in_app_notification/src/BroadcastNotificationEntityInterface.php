<?php

namespace Drupal\erpw_in_app_notification;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a broadcast notification entity entity type.
 */
interface BroadcastNotificationEntityInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
