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
   * @var \Drupal\erpw_location\\LocationCookieService
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
   * Database Connection instance.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

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
    $instance->connection = $container->get('database');
    $instance->requestStack = $container->get('request_stack');
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
    $default_lang = 'en';
    // Get the domain if country is selected.
    if ($selected_domain = $form_state->getValue('country')) {
      $domain = $this->entityManager->getStorage('domain')->load($selected_domain);
    }
    else {
      $domain = $this->domainNegotiator->getActiveDomain();
    }
    $form['domain']['country'] = [
      '#title' => $this->t('Select Country'),
      '#type' => 'select',
      '#options' => ['' => $this->t('Select country')] + $domain_list,
      '#required' => TRUE,
      '#id' => 'country-dropdown',
      '#default_value' => $domain->id(),
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
    $form['#attached']['library'][] = 'erpw_custom/erpw_geoip';
    $form['#attached']['drupalSettings']['api_key'] = getenv('opencage_api_key');
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
      $domain = $this->entityManager->getStorage('domain')->load($form_state->getValue('country'));
      $domain_lang = $form_state->getValue('language_selector');
      $domain_path = $domain->get('path');
      $redirect_url = Url::fromUri($domain_path . $domain_lang);

      // Get full domain for setting Location cookie.
      $current_domain = $this->domainNegotiator->getActiveDomain();
      $url = preg_replace('/^[^.]+\./', '', $current_domain->getHostname());
      // Get sliced domain for setting Language cookie.
      $domain_current_url = explode(".", $this->requestStack->getCurrentRequest()->server->get('SERVER_NAME'));
      $domain_slice = array_slice($domain_current_url, -2);
      $domain_site = '.' . $domain_slice[0] . '.' . $domain_slice[1];
      setcookie('userLanguageSelection', 'TRUE', strtotime('+1 year'), '/', $domain_site, FALSE);
      setcookie('userLanguage', $value['language_selector'], strtotime('+1 year'), '/', $domain_site, FALSE);
      $config = $this->configfactory->getEditable('domain.location.' . $domain->get('id'));
      $default_location = $config->get('location');
      if (!$default_location) {
        $default_location = $this->locationService->getDefaultLocation();
      }
      // Storing the value into cookie and temp storage.
      if ($this->currentUser->isAuthenticated()) {
        $user = $this->entityManager->getStorage('user')->load($this->currentUser->id());
        $default_location = ($this->locationService->getUserDefaultLocation($user)) ?? $default_location;
      }
      if (empty($this->locationCookie->getCookieValue())) {
        $this->locationCookie->setDefaultCookieValue();
      }
      setcookie($this->locationCookie->getCookieName(), $default_location, strtotime('+1 year'), '/', $url, FALSE);
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
