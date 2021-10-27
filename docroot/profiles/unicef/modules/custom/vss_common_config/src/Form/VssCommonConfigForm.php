<?php

namespace Drupal\vss_common_config\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Class VssCommonConfigForm.
 *
 * Common configuration form for vss site.
 */
class VssCommonConfigForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Construct function.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(
    EntityTypeManager $entity_type_manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'vss_common_config.vsscommonconfig',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vss_common_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('vss_common_config.vsscommonconfig');
    $domains = $this->entityTypeManager->getStorage('domain')->loadMultiple();
    foreach ($domains as $domain) {
      $domainId = $domain->id();
      $hostname = $domain->get('name');
      $form[$domainId] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Enter details for "@domain"', ['@domain' => $hostname]),
      ];
      $form[$domainId][$domainId . '_phone'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Phone'),
        '#description' => $this->t('Enter Phone Number'),
        '#maxlength' => 255,
        '#size' => 64,
        '#default_value' => (NULL !== $config->get($domainId . '_phone')) ? $config->get($domainId . '_phone') : '',
      ];
      $form[$domainId][$domainId . '_email'] = [
        '#type' => 'email',
        '#title' => $this->t('Email'),
        '#description' => $this->t('Enter Email'),
        '#default_value' => (NULL !== $config->get($domainId . '_email')) ? $config->get($domainId . '_email') : '',
      ];
      $form[$domainId][$domainId . '_address'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Address'),
        '#description' => $this->t('Address for Domain'),
        '#default_value' => (NULL !== $config->get($domainId . '_address')) ? $config->get($domainId . '_address') : '',
      ];
    }
    if (count($domains) === 0) {
      $form['domain_theme_switch_message'] = [
        '#markup' => $this->t('Zero domain records found. Please @link to create the domain.', [
          '@link' => Link::fromTextAndUrl($this->t('click here'), Url::fromRoute('domain.admin'))->toString(),
        ]),
      ];
      return $form;
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $domains = $this->entityTypeManager->getStorage('domain')->loadMultiple();
    $config = $this->config('vss_common_config.vsscommonconfig');
    foreach ($domains as $domain) {
      $domainId = $domain->id();
      $config->set($domainId . '_phone', $form_state->getValue($domainId . '_phone'));
      $config->set($domainId . '_email', $form_state->getValue($domainId . '_email'));
      $config->set($domainId . '_address', $form_state->getValue($domainId . '_address'));
    }
    $config->save();

  }

}
