<?php

namespace Drupal\erpw_webform\Form;

use Drupal\Core\Cache\CacheTagsInvalidator;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\erpw_webform\Batch\ServiceProviderMigrate;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements a configuration form for the migration.
 */
class ServiceProviderMigrationForm extends ConfigFormBase {

  /**
   * The erpw_webform.service_provider_migrate service.
   *
   * @var \Drupal\erpw_webform\Batch\ServiceProviderMigrate
   */
  protected ServiceProviderMigrate $serviceProviderMigrate;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Service to invalidate cache tags.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidator
   */
  protected CacheTagsInvalidator $cacheTagsInvalidator;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\erpw_webform\Batch\ServiceProviderMigrate $serviceProviderMigrate
   *   The batch count service/batch process.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Cache\CacheTagsInvalidator $cacheTagsInvalidator
   *   Cache tags invalidator.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ServiceProviderMigrate $serviceProviderMigrate, EntityTypeManagerInterface $entityTypeManager, CacheTagsInvalidator $cacheTagsInvalidator) {
    parent::__construct($config_factory);
    $this->serviceProviderMigrate = $serviceProviderMigrate;
    $this->entityTypeManager = $entityTypeManager;
    $this->cacheTagsInvalidator = $cacheTagsInvalidator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('erpw_webform.service_provider_migrate'),
      $container->get('entity_type.manager'),
      $container->get('cache_tags.invalidator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'erpw_webform_service_provider_migrate';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['erpw_webform.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['bn_migrate'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Migrate for Bangladesh'),
      '#description' => $this->t('Migrate service providers as webform submissions for Bangladesh in a batch process. This could take a while.'),
      '#default_value' => FALSE,
    ];
    $form['zw_migrate'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Migrate for Zimbabwe'),
      '#description' => $this->t('Migrate service providers as webform submissions for Zimbabwe in a batch process. This could take a while.'),
      '#default_value' => FALSE,
    ];
    $form['sl_migrate'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Migrate for Sierra Leone'),
      '#description' => $this->t('Migrate service providers as webform submissions for Sierra Leone in a batch process. This could take a while.'),
      '#default_value' => FALSE,
    ];
    $form['txb_migrate'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Migrate for Turkey Cross Border'),
      '#description' => $this->t('Migrate service providers as webform submissions for Turkey Cross Border in a batch process. This could take a while.'),
      '#default_value' => FALSE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    if ($form_state->getValue('bn_migrate')) {
      $nids = $this->entityTypeManager->getStorage('node')->getQuery()
        ->condition('type', 'service_provider', 'IN')
        ->condition('field_domain_access', 'bn_erefer_org')
        ->condition('field_service_type', ['301', '306', '311', '316', '321'], 'IN')
        ->execute();
      $operations = [];
      foreach ($nids as $nid) {
        $operations[] = [[$this->serviceProviderMigrate, 'migrate'], [$nid]];
      }
      $batch = [
        'title' => $this->t('Migrating Bangladesh Service Providers...'),
        'operations' => $operations,
        'finished' => [$this->serviceProviderMigrate, 'finishMigration'],
      ];
      batch_set($batch);
    }
    if ($form_state->getValue('txb_migrate')) {
      $nids = $this->entityTypeManager->getStorage('node')->getQuery()
        ->condition('type', 'service_provider', 'IN')
        ->condition('field_domain_access', 'txb_erefer_org')
        ->condition('field_service_type', '4541', 'IN')
        ->execute();
      $operations = [];
      foreach ($nids as $nid) {
        $operations[] = [[$this->serviceProviderMigrate, 'migrate'], [$nid]];
      }
      $batch = [
        'title' => $this->t('Migrating Turkey Cross Border Service Providers...'),
        'operations' => $operations,
        'finished' => [$this->serviceProviderMigrate, 'finishMigration'],
      ];
      batch_set($batch);
    }
    if ($form_state->getValue('zw_migrate')) {
      $nids = $this->entityTypeManager->getStorage('node')->getQuery()
        ->condition('type', 'service_provider', 'IN')
        ->condition('field_domain_access', 'zm_erefer_org')
        ->condition('field_service_type',
          ['426', '431', '436', '441', '446', '451', '3836'],
           'IN')
        ->execute();
      $operations = [];
      foreach ($nids as $nid) {
        $operations[] = [[$this->serviceProviderMigrate, 'migrate'], [$nid]];
      }
      $batch = [
        'title' => $this->t('Migrating Zimbabwe Service Providers...'),
        'operations' => $operations,
        'finished' => [$this->serviceProviderMigrate, 'finishMigration'],
      ];
      batch_set($batch);
    }
    if ($form_state->getValue('sl_migrate')) {
      $nids = $this->entityTypeManager->getStorage('node')->getQuery()
        ->condition('type', 'service_provider', 'IN')
        ->condition('field_domain_access', 'sl_erefer_org')
        ->condition('field_service_type',
          ['4396', '4401', '4406', '4411', '4416', '4426',
            '4431', '4436', '4936', '4941', '4991', '5541',
          ],
           'IN')
        ->execute();
      $operations = [];
      foreach ($nids as $nid) {
        $operations[] = [[$this->serviceProviderMigrate, 'migrate'], [$nid]];
      }
      $batch = [
        'title' => $this->t('Migrating Sierra Leone Service Providers...'),
        'operations' => $operations,
        'finished' => [$this->serviceProviderMigrate, 'finishMigration'],
      ];
      batch_set($batch);
    }
  }

}
