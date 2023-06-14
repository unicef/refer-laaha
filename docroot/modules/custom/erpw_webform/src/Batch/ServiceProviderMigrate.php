<?php

namespace Drupal\erpw_webform\Batch;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

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
   * Function to total investment from serviceProvider.
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
    $context['message'] = 'Migrating Service Provider: ' . $serviceProvider->label();
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
      $serviceType = $serviceProvider->get('field_service_type')->getValue()[0]['target_id'];
      $organisation = $serviceProvider->get('field_select_organisation')->getValue()[0]['target_id'];

      // Array of values.
      $ageGroupValue = $serviceProvider->get('field_age_group')->getValue();
      if (!empty($ageGroupValue)) {
        $ageGroups = [];
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
        $targetGroups[] = $targetGroup['value'];
      }

      $disabilities = $serviceProvider->get('field_accessibility_for_persons_')->getValue();
      if (!empty($disabilities)) {
        $accessiblities = [];
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
      // $ws_storage = $this->entityTypeManager->getStorage('webform_submission');
      // $webformSubmission = $ws_storage->create();
      // $webformSubmission->save();
      // dump($serviceProvider);
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
