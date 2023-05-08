<?php

namespace Drupal\erpw_webform;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\webform\WebformSubmissionListBuilder;

/**
 * Provides a list controller for webform submission entity.
 *
 * @ingroup webform
 */
class WebformSubmissionList extends WebformSubmissionListBuilder {

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    /** @var \Drupal\webform\WebformSubmissionListBuilder $instance */
    $instance = parent::createInstance($container, $entity_type);
    $instance->initialize();
    return $instance;
  }

  /* ************************************************************************ */
  // Row functions.
  /* ************************************************************************ */

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    // $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    // $user_roles = $user->get('roles')->getValue();
    // $webformID = $entity->get('webform_id')->getValue()[0]['target_id'];
    // $webform = $this->entityTypeManager->getStorage('webform')->load($webformID);
    // $tpa = $webform->getThirdPartySetting('erpw_webform', 'webform_service_type_map');
    // $domainAccess = $user->get('field_domain_access')->getValue();
    // dump($tpa);
    // foreach ($user_roles as $key_r => $role) {
    //   if ($role == "country_admin" || $role == "interagency_gbv_coordinator" || $role == "service_provider_staff" || $role == "service_provider_focal_point") {
    //     foreach ($domainAccess as $key => $domain) {
    //       if (array_key_exists($domain['target_id'], $tpa)) {
    //         dump($domain['target_id']);
    //         dump($tpa);
    //         return parent::buildRow($entity);
    //       }
    //     }
    //   }
    //   if ($role['target_id'] == "super_admin" || $role['target_id'] == "administrator") {
    //     return parent::buildRow($entity);
    //   }
    // }
  }

}
