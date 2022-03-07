<?php

namespace Drupal\erpw_custom\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\domain\DomainNegotiatorInterface;
use Drupal\erpw_location\LocationCookie;
use Drupal\erpw_location\LocationService;
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
   * LegalAdminTermsForm constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
    LanguageManagerInterface $language_manager,
    LocationCookie $location_cookie,
    DomainNegotiatorInterface $negotiator,
    PrivateTempStoreFactory $temp_store_factory,
    LocationService $location_service,
    AccountProxyInterface $current_user,
    EntityTypeManagerInterface $entity_manager) {
    $this->configfactory = $config_factory;
    $this->languageManager = $language_manager;
    $this->locationCookie = $location_cookie;
    $this->domainNegotiator = $negotiator;
    $this->tempStoreFactory = $temp_store_factory->get('erpw_location_collection');
    $this->locationService = $location_service;
    $this->currentUser = $current_user;
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('language_manager'),
      $container->get('erpw_location.location_cookie'),
      $container->get('domain.negotiator'),
      $container->get('tempstore.private'),
      $container->get('erpw_location.location_services'),
      $container->get('current_user'),
      $container->get('entity_type.manager'),
    );
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
    $form['description_2'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="choose-language-text">',
      '#markup' => $this->t('Choose your preferred language'),
      '#suffix' => '</div>',
    ];
    $domain = $this->domainNegotiator->getActiveDomain();
    $active_lang = $this->configfactory->get('domain.language.' . $domain->id() . '.language.negotiation')->getRawData()['languages'];
    $site_languages = $this->languageManager->getNativeLanguages();
    $languages = [];
    foreach ($active_lang as $languagecode => $language_value) {
      $languages[$languagecode] = $site_languages[$language_value]->getName();
    }
    $form['language_selector'] = [
      '#type' => 'radios',
      '#options' => $languages,
      '#default_value' => $this->t('en'),
    ];
    $form['actions']['lang_selector'] = [
      '#type' => 'submit',
      '#value' => $this->t('SUBMIT'),
    ];
    $form['#cache']['max-age'] = 0;
    return $form;
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
