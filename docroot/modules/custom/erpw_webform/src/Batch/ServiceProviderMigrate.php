<?php

namespace Drupal\erpw_webform\Batch;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webform\Entity\WebformSubmission;

/**
 * Batch process to count transactions.
 */
class ServiceProviderMigrate {
  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs an object.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger channel factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(MessengerInterface $messenger, LoggerChannelFactoryInterface $logger, EntityTypeManagerInterface $entity_type_manager) {
    $this->messenger = $messenger;
    $this->logger = $logger->get('erpw_webform');
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Function to migrate serviceProvider from node to webform submissions.
   *
   * @param int $nid
   *   The node id of the serviceProvider.
   * @param mixed $context
   *   The context variables of the batch.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function migrate(int $nid, &$context) {
    $serviceProvider = $this->entityTypeManager->getStorage('node')->load($nid);
    $context['message'] = 'Migrating: ' . $serviceProvider->label();
    $context['success'] = TRUE;
    $context['results'][] = $nid;
    try {
      // String values.
      $addMoreDetails = $serviceProvider->get('field_add_more_details_service')->getValue()[0]['value'];
      $addMoreDetails = is_null($addMoreDetails) ? '' : $addMoreDetails;
      $costService = $serviceProvider->get('field_cost_of_service')->getValue()[0]['value'];
      $costService = is_null($costService) ? '' : $costService;
      $emailBackup = $serviceProvider->get('field_email_backup_focal_point')->getValue()[0]['value'];
      $emailBackup = is_null($emailBackup) ? '' : $emailBackup;
      $emailFocal = $serviceProvider->get('field_email_of_focal_point')->getValue()[0]['value'];
      $emailFocal = is_null($emailFocal) ? '' : $emailFocal;
      $hotline = $serviceProvider->get('field_phone_number')->getValue()[0]['value'];
      $hotline = is_null($hotline) ? '' : $hotline;
      $nameBackup = $serviceProvider->get('field_name_backup_focalpoint_ref')->getValue()[0]['value'];
      $nameBackup = is_null($nameBackup) ? '' : $nameBackup;
      $nameFocal = $serviceProvider->get('field_name_of_the_focal_point')->getValue()[0]['value'];
      $nameFocal = is_null($nameFocal) ? '' : $nameFocal;
      $staff = $serviceProvider->get('field_number_of_staff_providing_')->getValue()[0]['value'];
      $staff = is_null($staff) ? '' : $staff;
      $openingHours = $serviceProvider->get('field_opening_hours')->getValue()[0]['value'];
      $openingHours = is_null($openingHours) ? '' : $openingHours;
      $phoneBackup = $serviceProvider->get('field_phone_number_backup_focalp')->getValue()[0]['value'];
      $phoneBackup = is_null($phoneBackup) ? '' : $phoneBackup;
      $phoneFocal = $serviceProvider->get('field_phone_number_of_focal_poin')->getValue()[0]['value'];
      $phoneFocal = is_null($phoneFocal) ? '' : $phoneFocal;
      $transportation = $serviceProvider->get('field_transportation_available')->getValue()[0]['value'];
      $transportation = is_null($transportation) ? '' : $transportation;
      $organisation = $serviceProvider->get('field_select_organisation')->getValue()[0]['target_id'];

      // Array of values.
      $ageGroupValue = $serviceProvider->get('field_age_group')->getValue();
      $ageGroups = [];
      if (!empty($ageGroupValue)) {
        foreach ($ageGroupValue as $ageGroup) {
          if ($ageGroup['value'] == '18+') {
            $ageGroups[] = '18-64';
          }
          else {
            $ageGroups[] = $ageGroup['value'];
          }
        }
      }

      $serviceModeValue = $serviceProvider->get('field_mode')->getValue();
      $serviceModes = [];
      foreach ($serviceModeValue as $serviceMode) {
        $serviceModes[] = $serviceMode['value'];
      }

      $targetGroupValue = $serviceProvider->get('field_target_group')->getValue();
      $targetGroups = [];
      foreach ($targetGroupValue as $targetGroup) {
        if ($targetGroup['value'] == 'Host community') {
          $targetGroups[] = 'Host Community';
        }
        else {
          $targetGroups[] = $targetGroup['value'];
        }
      }

      $disabilities = $serviceProvider->get('field_accessibility_for_persons_')->getValue();
      $accessiblities = [];
      if (!empty($disabilities)) {
        foreach ($disabilities as $disability) {
          $accessiblities[] = $disability['value'];
        }
      }

      $locationsValue = $serviceProvider->get('field_location')->getValue();
      $locations = [];
      foreach ($locationsValue as $location) {
        if (!empty($location['target_id']) && !is_null($location['target_id'])) {
          $parents = array_reverse(\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadAllParents($location['target_id']));
          foreach ($parents as $pkey => $parent) {
            switch ($pkey) {
              case 0:
                $label = $parent->get('name')->getValue()[0]['value'];
                if ($label == 'Bangladesh') {
                  $locations['location_options'] = '6';
                }
                elseif ($label == 'Turkey Cross Border') {
                  $locations['location_options'] = '11';
                }
                elseif ($label == 'Sierra Leone') {
                  $locations['location_options'] = '16';
                }
                elseif ($label == 'Zimbabwe') {
                  $locations['location_options'] = '1';
                }
                break;

              case 1:
                $locations['level_1'] = $parent->get('tid')->getValue()[0]['value'];
                break;

              case 2:
                $locations['level_2'] = $parent->get('tid')->getValue()[0]['value'];
                break;

              case 3:
                $locations['level_3'] = $parent->get('tid')->getValue()[0]['value'];
                break;

              case 4:
                $locations['level_4'] = $parent->get('tid')->getValue()[0]['value'];
                break;

              default:
                break;
            }
          }
          $locations['location_tid'] = $location['target_id'];
        }
      }

      // eRPW workflow.
      $workflow = [];
      $revision = $serviceProvider->get('revision_timestamp')->getValue()[0]['value'];
      $created = $serviceProvider->get('created')->getValue()[0]['value'];
      $workflow['changed_timestamp'] = (is_null($revision) || empty($revision)) ? $created : $revision;
      $revisionUser = $serviceProvider->get('revision_uid')->getValue()[0]['target_id'];
      $createdUser = $serviceProvider->get('uid')->getValue()[0]['target_id'];
      $workflow['changed_user'] = (is_null($revisionUser) || empty($revisionUser)) ? $createdUser : $revisionUser;
      $status = $serviceProvider->get('status')->getValue()[0]['value'];
      switch ($status) {
        case '0':
          $workflow['transition'] = "save_as_draft";
          $workflow['workflow_state'] = "draft";
          $workflow['workflow_state_label'] = "Draft";
          $workflow['workflow_state_previous'] = "";
          break;

        case '1':
          $workflow['transition'] = "approve";
          $workflow['workflow_state'] = "approve";
          $workflow['workflow_state_label'] = "Approved";
          $workflow['workflow_state_previous'] = "";
          break;

        default:
          $workflow['transition'] = "save_as_draft";
          $workflow['workflow_state'] = "draft";
          $workflow['workflow_state_label'] = "Draft";
          $workflow['workflow_state_previous'] = "";
          break;
      }

      // Setting Webform ID.
      $webformID = '';
      $serviceType = $serviceProvider->get('field_service_type')->getValue()[0]['target_id'];
      switch ($serviceType) {
        case '301':
          $webformID = 'bn_healthcare_cmr_within72hours';
          break;

        case '306':
          $webformID = 'bn_casemanagement_adults';
          break;

        case '311':
          $webformID = 'bn_gbv_case_management_children';
          break;

        case '316':
          $webformID = 'bn_legal_assistance';
          break;

        case '321':
          $webformID = 'bn_emergency_shelter_safe_house';
          break;

        case '426':
          $webformID = 'zw_health_services';
          break;

        case '431':
          $webformID = 'zw_psychosocial_services';
          break;

        case '436':
          $webformID = 'zw_safety_security_police';
          break;

        case '441':
          $webformID = 'zw_legal_services';
          break;

        case '446':
          $webformID = 'zw_shelter_services';
          break;

        case '451':
          $webformID = 'zw_one_stop_center';
          break;

        case '3836':
          $webformID = 'zw_hotlines';
          break;

        case '4541':
          $webformID = 'txb_health_services';
          break;

        case '4396':
          $webformID = 'sl_medical_services';
          break;

        case '4401':
          $webformID = 'sl_psychosocial_support';
          break;

        case '4406':
          $webformID = 'sl_case_management';
          break;

        case '4411':
          $webformID = 'sl_legal_aid';
          break;

        case '4416':
          $webformID = 'sl_police_services';
          break;

        case '4426':
          $webformID = 'sl_protection_shelter';
          break;

        case '4431':
          $webformID = 'sl_social_services';
          break;

        case '4436':
          $webformID = 'sl_education_training_services';
          break;

        case '4936':
          $webformID = 'sl_livelihood_services';
          break;

        case '4941':
          $webformID = 'sl_hotline';
          break;

        case '4991':
          $webformID = 'sl_advocacy_for_child_survivors';
          break;

        case '5541':
          $webformID = 'sl_referral_for_medical_exam';
          break;

        default:
          $webformID = '';
          break;

      }

      // Setting values for webform submissions.
      $values = [];
      if (!is_null($webformID) && !empty($webformID)) {
        $values = [
          'webform_id' => $webformID,
          'data' => [
            'location' => $locations,
            'organisation' => $organisation,
            'field_number_of_staff_providing_' => $staff,
            'field_opening_hours' => $openingHours,
            'field_cost_of_service' => $costService,
            'transportation_available' => $transportation,
            'accessibility_for_persons' => $accessiblities,
            'age_group' => $ageGroups,
            'target_group' => $targetGroups,
            'field_phone_number' => $hotline,
            'field_mode' => $serviceModes,
            'field_name_of_the_focal_point' => $nameFocal,
            'field_phone_number_of_focal_poin' => $phoneFocal,
            'field_email_of_focal_point' => $emailFocal,
            'field_name_backup_focalpoint_ref' => $nameBackup,
            'field_phone_number_backup_focalp' => $phoneBackup,
            'field_email_backup_focal_point' => $emailBackup,
            'field_add_more_details_service' => $addMoreDetails,
            'erpw_workflow' => $workflow,
          ],
        ];

        /** @var \Drupal\webform\WebformSubmissionInterface $webform_submission */
        $webform_submission = WebformSubmission::create($values);
        $webform_submission->save();
      }
    }
    catch (\Exception $e) {
      $context['success'] = FALSE;
      $this->logger->debug('Migrate Service Provider: ' . $e->getMessage());
    }
  }

  /**
   * Finish callback for batch process.
   *
   * @param mixed|bool $success
   *   Success Flag.
   * @param mixed|array $results
   *   The results array.
   * @param mixed|array $operations
   *   Array of operations functions.
   */
  public function finishMigration($success, $results, $operations) {
    if ($success) {
      $message = $this->formatPlural(
        count($results),
        'One item migrated.',
        '@count items migrated.'
      );
    }
    else {
      $message = $this->t('Finished with an error.');
    }
    $this->messenger->addMessage($message);
  }

}
