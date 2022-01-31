<?php

namespace Drupal\vss_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Constructs a new LocationSelectorForm object.
 */
class LocationSelectorForm extends FormBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Language\LanguageManagerInterface.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Drupal\vss_common_config\VssCommonInterface.
   *
   * @var \Drupal\vss_common_config\VssCommonInterface
   */
  protected $vssCommonService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->languageManager = $container->get('language_manager');
    $instance->vssCommonService = $container->get('vss_common_config.default');
    $instance->pageCacheKillSwitch = $container->get('page_cache_kill_switch');
    return $instance;
  }

  /**
   * Form ID function.
   */
  public function getFormId() {
    return 'location_form';
  }

  /**
   * Constructs a new VirtualSpace form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $lang_id = $this->languageManager->getCurrentLanguage()->getId();
    $data = $this->vssCommonService->getLocationData();
    $html = '';
    if (isset($data['location_selection_title']) && !empty($data['location_selection_title'])) {
      $html .= $data['location_selection_title'];
    }
    if (isset($data['location_selection_description']) && !empty($data['location_selection_description'])) {
      $html .= ' ';
      $html .= strip_tags($data['location_selection_description']);
    }

    $string = str_replace('&nbsp;', '', $html);
    $form['#content'] = $data;
    if ($lang_id == 'ar') {
      $lang_id = 'ar-SA';
    }
    if ($lang_id == 'es') {
      $lang_id = 'es-ES';
      $voiceId = 30;
      $form['#attached']['drupalSettings']['voiceId'] = $voiceId;
    }
    if ($lang_id == 'en') {
      $lang_id = 'en-US';
      $voiceId = 41;
      $form['#attached']['drupalSettings']['voiceId'] = $voiceId;
    }
    $form['#attached']['drupalSettings']['location_selector_landId'] = $lang_id;
    $form['#attached']['drupalSettings']['location_selector_narrate'] = $string;
    $form['#attached']['drupalSettings']['location_selector'] = TRUE;

    $all_domains = \Drupal::service('entity_type.manager')->getStorage('domain')->loadMultipleSorted(NULL);
    foreach ($all_domains as $domain) {
      $domain_status = $domain->get('status');
      if ($domain_status == TRUE) {
        $domain_hostname = $domain->get('hostname');
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
      '#title' => t('Country'),
      '#type' => 'select',
      '#description' => 'Select country',
      '#options' => ['' => t('Select country')] + $domain_list,
      '#required' => TRUE,
      '#id' => 'country-dropdown',
      '#default_value' => '',
      '#ajax' => [
        'callback' => '::getLanguages',
        'event' => 'change',
        'wrapper' => 'domain-wrapper',
        'progress' => [
          'type' => 'throbber',
        ],
      ],
    ];
    $lang_select = [];
    if (!empty($form_state->getValue('country'))) {
      $selected_domain = $form_state->getValue('country');
      // Get the active languages for the given domain.
      // Update form options.
      $domain = \Drupal::entityTypeManager()->getStorage('domain')->load($selected_domain);
      $lang = \Drupal::configFactory()->get('domain.language.' . $domain->id() . '.language.negotiation');
      $languages = \Drupal::languageManager()->getLanguages();
      $prefixes = $lang->get('languages');
      foreach ($languages as $langcode => $language) {
        if (array_key_exists($langcode, $prefixes)) {
          $lang_select[$langcode] = $language->getName();
        }
      }
      $default_lang = NULL;
      if (array_key_exists('en', $lang_select)) {
        $default_lang = 'en';
      }
    }

    $form['domain']['language'] = [
      '#title' => t('Language'),
      '#type' => 'select',
      '#description' => 'Select language',
      '#options' => ['' => t('Select language')] + $lang_select ,
      '#required' => TRUE,
      '#id' => 'language-dropdown',
      '#default_value' => !empty($default_lang) ? $default_lang : '',
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#button_type' => 'primary',
      '#attributes' => ['class' => ['location-selector-btn']],
    ];

    $form['#attached']['library'][] = 'vss_custom/geoip';
    $form['#attached']['drupalSettings']['api_key'] = 'c6ea4bfd74d1403ab52e4bacf7478f36';
    $form['#theme'] = 'location_selector_form';
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get domain path from dropdown.
    $domain = \Drupal::entityTypeManager()->getStorage('domain')->load($form_state->getValue('country'));
    $default_lang = \Drupal::configFactory()->get('domain.config.' . $domain->id() . '.system.site');

    $domain_path = $domain->get('path');
    $domain_lang = $form_state->getValue('language');
    if ($default_lang->get('default_langcode') == $domain_lang) {
      $domain_lang = '';
    }

    // Get selected domain's url.
    $url = $domain_path . $domain_lang . '/home';
    $response = new TrustedRedirectResponse($url);
    $response->headers->setCookie(new Cookie('country-selector', 'TRUE', strtotime('+7 days'), '/', NULL, FALSE));
    $form_state->setResponse($response);

    return;
  }

}
