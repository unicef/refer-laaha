<?php

namespace Drupal\erpw_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ModalForm class.
 */
class LanguageSelector extends FormBase {

  /**
   * The Current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Drupal\domain\DomainNegotiatorInterface definition.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * Entity Manager instance.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * Drupal\language\ConfigurableLanguageManagerInterface definition.
   *
   * @var \Drupal\language\ConfigurableLanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The cookie as a service.
   *
   * @var \Drupal\erpw_location\LocationCookie
   */
  protected $locationCookie;

  /**
   * The location service.
   *
   * @var \Drupal\erpw_location\LocationService
   */
  protected $locationService;

  /**
   * The tempstore factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'language_selector';
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->configfactory = $container->get('config.factory');
    $instance->languageManager = $container->get('language_manager');
    $instance->locationCookie = $container->get('erpw_location.location_cookie');
    $instance->domainNegotiator = $container->get('domain.negotiator');
    $instance->tempStoreFactory = $container->get('tempstore.private')->get('erpw_location_collection');
    $instance->locationService = $container->get('erpw_location.location_services');
    $instance->currentUser = $container->get('current_user');
    $instance->entityManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
    $form['description_1'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="welcome-text">',
      '#markup' => $this->t('Welcome to eRPW'),
      '#suffix' => '</div>',
    ];

    $all_domains = $this->entityManager->getStorage('domain')->loadMultipleSorted(NULL);
    foreach ($all_domains as $domain) {
      $domain_status = $domain->get('status');
      if ($domain_status) {
        $domain_name = $domain->get('name');
        $domain_id = $domain->get('id');
        $domain_list[$domain_id] = $domain_name;
      }
    }

    $form['domain'] = [
      '#prefix' => '<div id="domain-wrapper">',
      '#suffix' => '</div>',
    ];
    $form['domain']['country'] = [
      '#title' => $this->t('Select Country'),
      '#type' => 'select',
      '#options' => ['' => $this->t('Select country')] + $domain_list,
      '#required' => TRUE,
      '#id' => 'country-dropdown',
      '#default_value' => '',
      '#ajax' => [
        'callback' => '::getLanguages',
        'event' => 'change',
        'method' => 'replace',
        'wrapper' => 'domain-wrapper',
        'progress' => [
          'type' => 'throbber',
        ],
      ],
    ];
    $form_state->setRebuild();
    $default_lang = 'en';
    // Get the domain if country is selected.
    if ($selected_domain = $form_state->getValue('country')) {
      $domain = $this->entityManager->getStorage('domain')->load($selected_domain);
    }
    else {
      $domain = $this->domainNegotiator->getActiveDomain();
    }
    $lang = $this->configfactory->get('domain.language.' . $domain->id() . '.language.negotiation');
    $languages = $this->languageManager->getLanguages();
    $prefixes = $lang->get('languages');
    foreach ($languages as $langcode => $language) {
      if (array_key_exists($langcode, $prefixes)) {
        $lang_select[$langcode] = $language->getName();
      }
    }
    $form['domain']['description_2'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="choose-language-text">',
      '#suffix' => '</div>',
      '#markup' => $this->t('Choose your preferred language'),
    ];
    $form['domain']['language_selector'] = [
      '#type' => 'radios',
      '#required' => TRUE,
      '#options' => $lang_select,
      '#default_value' => $default_lang,
    ];
    $form['actions']['lang_selector'] = [
      '#type' => 'submit',
      '#value' => $this->t('SUBMIT'),
    ];
    $form['#cache']['max-age'] = 0;
    return $form;
  }

  /**
   * Get languages ajax function.
   */
  public function getLanguages(array &$form, FormStateInterface $form_state) {
    return $form['domain'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $value = $form_state->getValues();
    if (!empty($value)) {
      $redirect_url = Url::fromUri('base:/' . $value['language_selector']);
      setcookie('userLanguageSelection', 'TRUE', strtotime('+1 year'), '/', NULL, FALSE);
      setcookie('userLanguage', $value['language_selector'], strtotime('+1 year'), '/', NULL, FALSE);
      // Storing the value into coookie and temp storage.
      if ($this->currentUser->isAuthenticated()) {
        $user = $this->entityManager->getStorage('user')->load($this->currentUser->id());
        $default_location = $this->locationService->getUserDefaultLocation($user);
      }
      else {
        $default_location = $this->locationService->getDefaultLocation();
      }
      if (empty($this->locationCookie->getCookieValue())) {
        $this->locationCookie->setCookieValue(base64_encode('country_tid_' . time()));
        $this->tempStoreFactory->set(base64_decode($this->locationCookie->getCookieValue()), $default_location);
      }
      else {
        $this->tempStoreFactory->set(base64_decode($this->locationCookie->getCookieValue()), $default_location);
      }
      $form_state->setRedirectUrl($redirect_url);
    }
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['config.language_selector_form'];
  }

}
