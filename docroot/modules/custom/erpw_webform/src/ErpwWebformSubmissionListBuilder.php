<?php

namespace Drupal\erpw_webform;

use Drupal\Core\Entity\EntityInterface;
use Drupal\webform\WebformSubmissionListBuilder;

/**
 * Provides a list controller for webform submission entity.
 *
 * @ingroup webform
 */
class ErpwWebformSubmissionListBuilder extends WebformSubmissionListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    $user_roles = $user->get('roles')->getValue();
    $webformID = $entity->get('webform_id')->getValue()[0]['target_id'];
    $webform = $this->entityTypeManager->getStorage('webform')->load($webformID);
    $tpa = $webform->getThirdPartySetting('erpw_webform', 'webform_service_type_map');
    $domainAccess = $user->get('field_domain_access')->getValue();
    foreach ($user_roles as $key_r => $role) {
      if ($role['target_id'] == 'country_admin' || $role['target_id'] == 'interagency_gbv_coordinator' || $role['target_id'] == 'service_provider_staff' || $role['target_id'] == 'service_provider_focal_point') {
        foreach ($domainAccess as $key => $domain) {
          if (array_key_exists($domain['target_id'], $tpa)) {
            return parent::buildRow($entity);
          }
        }
      }
      if ($role['target_id'] == 'super_admin' || $role['target_id'] == 'administrator') {
        return parent::buildRow($entity);
      }
    }
  }

}
