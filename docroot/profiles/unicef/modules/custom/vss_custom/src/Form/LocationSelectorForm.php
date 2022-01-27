<?php

namespace Drupal\vss_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Form\FormStateInterface;

/**
 * Constructs a new LocationSelectorForm object.
 */
class LocationSelectorForm extends FormBase {

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

      if (!empty($form_state->getValue('country'))) {
        $selected_domain = $form_state->getValue('country');
      }
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
    $form_state->setResponse($response);
    return;
  }

}
