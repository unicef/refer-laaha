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
  public function buildHeader() {
    $header = parent::buildHeader();
    unset($header['description']);
    unset($header['category']);
    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    $user_roles = $user->get('roles')->getValue();
    $tpa = $entity->getThirdPartySetting('erpw_webform', 'webform_service_type_map');
    $currentDomain = \Drupal::service('domain.negotiator')->getActiveDomain()->id();
    $row = parent::buildRow($entity);
    unset($row['description']);
    unset($row['category']);
    foreach ($user_roles as $key_r => $role) {
      if ($role['target_id'] != 'administrator') {
        if (!is_null($tpa)) {
          if (array_key_exists($currentDomain, $tpa)) {
            unset($row['operations']['data']['#links']['test']);
            unset($row['operations']['data']['#links']['results']);
            unset($row['operations']['data']['#links']['settings']);
            return $row;
          }
        }
      }
      if ($role['target_id'] == 'administrator') {
        return $row;
      }
    }
  }

}
