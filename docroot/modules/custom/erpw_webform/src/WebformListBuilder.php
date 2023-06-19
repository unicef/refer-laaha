<?php

namespace Drupal\erpw_webform;

use Drupal\Core\Entity\EntityInterface;
use Drupal\webform\WebformEntityListBuilder;

/**
 * Defines a class to build a listing of webform entities.
 *
 * @see \Drupal\webform\Entity\Webform
 */
class WebformListBuilder extends WebformEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    $user_roles = $user->get('roles')->getValue();
    $tpa = $entity->getThirdPartySetting('erpw_webform', 'webform_service_type_map');
    $currentDomain = \Drupal::service('domain.negotiator')->getActiveDomain()->id();
    foreach ($user_roles as $key_r => $role) {
      if ($role['target_id'] != 'administrator') {
        if (!is_null($tpa)) {
          if (array_key_exists($currentDomain, $tpa)) {
            return parent::buildRow($entity);
          }
        }
      }
      if ($role['target_id'] == 'administrator') {
        return parent::buildRow($entity);
      }
    }
  }

}
