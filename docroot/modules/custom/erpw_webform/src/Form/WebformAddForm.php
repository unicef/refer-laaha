<?php

namespace Drupal\erpw_webform\Form;

use Drupal\webform\WebformEntityAddForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an overrided webform add form.
 */
class WebformAddForm extends WebformEntityAddForm {

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
    $instance->domainNegotiator = $container->get('domain.negotiator');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareEntity() {
    return parent::prepareEntity();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    return parent::buildForm($form, $form_state);

  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $service_type_options = [];
    $current_domain = $this->domainNegotiator->getActiveDomain()->id();
    $current_language = $this->languageManager->getCurrentLanguage()->getId();
    // The values for the dropdown box.
    $service_types = $this->entityTypeManager->getStorage('node')
      ->loadByProperties(['type' => 'service_type']);
    foreach ($service_types as $id => $service_type) {
      $name = $service_type->get('title')->getValue()[0]['value'];
      $allowed_domains = $service_type->get('field_domain_access')->getValue();
      foreach ($allowed_domains as $domain_id => $allowed_domain) {
        if ($current_domain == $allowed_domain['target_id']) {
          if ($service_type->hasTranslation($current_language)) {
            $translated = $service_type->getTranslation($current_language);
            $service_type_options[$id] = $translated->get('title')->getValue()[0]['value'];
          }
          else {
            $service_type_options[$id] = $name;
          }
        }
      }
    }

    // Filter out the list and remove existing service types.
    $existingServiceTypes = $this->entityTypeManager->getStorage('webform')
      ->loadMultiple();
    foreach ($existingServiceTypes as $indivisualWebform) {
      $tpa = $indivisualWebform->getThirdPartySetting('erpw_webform', 'webform_service_type_map');
      if (!is_null($tpa) && array_key_exists($current_domain, $tpa)) {
        if ($tpa[$current_domain] != NULL) {
          unset($service_type_options[$tpa[$current_domain][0]]);
        }
      }
    }
    if (empty($service_type_options)) {
      $form['service_type'] = [
        '#title' => t('Select Service Type'),
        '#type' => 'select',
        '#weight' => -10,
        '#required' => TRUE,
        '#description' => "Select the service type for which the webform is being created.",
        "#empty_option" => t('- Select -'),
        '#options' => $service_type_options,
        '#disabled' => TRUE,
        '#prefix' => '<div class="empty-service-type-notice">* Forms for all the service types have been created. Create a new service type in order to create its corresponding form.</div>',
      ];
    }
    else {
      $form['service_type'] = [
        '#title' => t('Select Service Type'),
        '#type' => 'select',
        '#weight' => -10,
        '#required' => TRUE,
        '#description' => "Select the service type for which the webform is being created.",
        "#empty_option" => t('- Select -'),
        '#options' => $service_type_options,
      ];
    }
    $form_state->set('current_domain_webform', $current_domain);
    $form['#attached']['library'][] = 'erpw_webform/erpw_webform_form';
    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $query = $this->entityTypeManager->getStorage('webform')->getQuery();
    $webform_ids = $query->condition('category', 'eRPW')->accessCheck(FALSE)->execute();
    $webforms = $this->entityTypeManager->getStorage('webform')->loadMultiple($webform_ids);
    $current_domain = $form_state->get('current_domain_webform');
    foreach ($webforms as $webform) {
      $settings = $webform->getThirdPartySetting('erpw_webform', 'webform_service_type_map');
      if (isset($settings[$current_domain])) {
        if ($settings[$current_domain][0] == $form_state->getValues()['service_type']) {
          $form_state->setErrorByName('service_type', $this->t('A webform of the selected service type already exists.'));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $current_domain = $form_state->get('current_domain_webform');
    $webform = $this->getEntity();
    $checkArray[$current_domain][] = $form_state->getValues()['service_type'];
    $webform->setThirdPartySetting('erpw_webform', 'webform_service_type_map', $checkArray);
    parent::save($form, $form_state);
  }

}
