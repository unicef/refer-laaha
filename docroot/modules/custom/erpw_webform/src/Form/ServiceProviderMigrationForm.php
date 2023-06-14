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
    $form['migrate'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Migrate'),
      '#description' => $this->t('Migrate service providers as webform submissions in a batch process. This could take a while.'),
      '#default_value' => FALSE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    if ($form_state->getValue('migrate')) {
      $nids = $this->entityTypeManager->getStorage('node')->getQuery()
        ->condition('type', 'service_provider', 'IN')
        ->execute();
      $operations = [];
      foreach ($nids as $nid) {
        $operations[] = [[$this->serviceProviderMigrate, 'migrate'], [$nid]];
      }
      $batch = [
        'title' => $this->t('Migrating Service Providers...'),
        'operations' => $operations,
        'finished' => [$this->serviceProviderMigrate, 'finishMigration'],
      ];
      batch_set($batch);
    }
  }

}
