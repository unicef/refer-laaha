<?php

namespace Drupal\erpw_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * ModalForm class.
 */
class LanguageSelector extends FormBase {


  /**
   * Drupal\domain\DomainNegotiatorInterface definition.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * Drupal\language\ConfigurableLanguageManagerInterface definition.
   *
   * @var \Drupal\language\ConfigurableLanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'language_selector';
  }

  /**
   * LegalAdminTermsForm constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager) {
    $this->configfactory = $config_factory;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('language_manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
    $form['description_1'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="review-msg">',
      '#markup' => $this->t('Welcome to eRPW'),
      '#suffix' => '</div>',
    ];
    $form['description_2'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="email-notify">',
      '#markup' => $this->t('Choose your preferred language'),
      '#suffix' => '</div>',
    ];
    $domain = \Drupal::service('domain.negotiator')->getActiveDomain();
    $active_lang = $this->configfactory->get('domain.language.' . $domain->id() . '.language.negotiation')->getRawData()['languages'];
    $site_languages = $this->languageManager->getNativeLanguages();
    $languages = [];
    foreach ($active_lang as $languagecode => $language_value) {
      $languages[$languagecode] = $site_languages[$language_value]->getName();
    }
    $form['language_selector'] = [
      '#type' => 'radios',
      '#options' => $languages,
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
    $redirect_url = Url::fromUri('base:/' . $value['language_selector']);
    setcookie('userLanguageSelection', 'TRUE', strtotime('+7 days'), '/', NULL, FALSE);
    setcookie('userLanguage', $value['language_selector'], strtotime('+7 days'), '/', NULL, FALSE);
    $form_state->setRedirectUrl($redirect_url);
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
