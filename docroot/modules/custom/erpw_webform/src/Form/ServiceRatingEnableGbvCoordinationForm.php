<?php

namespace Drupal\erpw_webform\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom form example form.
 */
class ServiceRatingEnableGbvCoordinationForm extends FormBase {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Drupal\domain\DomainNegotiatorInterface definition.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->configFactory = $container->get('config.factory');
    $instance->domainNegotiator = $container->get('domain.negotiator');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'service_rating_enable_gbv_coordination_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $activeDomainId = $this->domainNegotiator->getActiveDomain()->id();
    $config = $this->configFactory->get('erpw_webform.service_rating.settings');
    $gbvCoordinationStatus = $config->get($activeDomainId . '_service_rating_gbv_org_filter_status');

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $gbvCoordinationStatus ? $this->t('Disable for GBV Coordination') : $this->t('Enable for GBV Coordination'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $activeDomainId = $this->domainNegotiator->getActiveDomain()->id();
    $config = $this->configFactory->getEditable('erpw_webform.service_rating.settings');
    $formStateValues = $form_state->getValues()['submit'];
    $value = strtolower($formStateValues->render());
    if (str_contains($value, 'enable')) {
      $config->set($activeDomainId . '_service_rating_gbv_org_filter_status', TRUE);
    }
    else {
      $config->set($activeDomainId . '_service_rating_gbv_org_filter_status', FALSE);
    }
    $config->save();

  }

}
