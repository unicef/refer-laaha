<?php

namespace Drupal\erpw_custom\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Cache\CacheTagsInvalidator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ErpwConfigForm.
 *
 * Common configuration form for erpw site.
 */
class ErpwConfigForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidator
   */
  protected $cacheTagsInvalidator;

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * Construct function.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Cache\CacheTagsInvalidator $cache_tags_invalidator
   *   The cache tags invalidator.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request stack.
   * @param \Drupal\Core\Database\Driver\mysql\Connection $database
   *   The database connection.
   */
  public function __construct(
    EntityTypeManager $entity_type_manager,
    CacheTagsInvalidator $cache_tags_invalidator,
    RequestStack $request,
    Connection $database
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
    $this->request = $request;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('cache_tags.invalidator'),
      $container->get('request_stack'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'erpw_config.erpwconfig',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'erpw_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('erpw_config.erpwconfig');
    $common_config = $config->get('erpw_common_config');
    $form['erpwcommonconfig'] = [
      '#type' => 'vertical_tabs',
    ];

    $form['domain_country_details'] = [
      '#type' => 'details',
      '#title' => 'Domain and Counrey Mapping',
      '#group' => 'erpwcommonconfig',
    ];

    $form['domain_country_details']['country_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone'),
      '#description' => $this->t('Enter Phone Number'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => !empty($common_config['phone']) ? $common_config['phone'] : '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config('erpw_config.erpwconfig');
    $config->set('erpw_common_config', $form_state->getValues());
    $config->save();
    // if ($image = $form_state->getValue('disclaimer_image')) {
    //   $file = $this->entityTypeManager->getStorage('file')->load($image[0]);
    //   $file->setPermanent();
    //   $file->save();
    // }
    // Invalidate vss_common_config.
    $this->cacheTagsInvalidator->invalidateTags(['eprw_common_config']);
  }

}
