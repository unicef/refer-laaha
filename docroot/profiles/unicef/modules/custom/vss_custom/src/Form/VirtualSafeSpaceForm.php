<?php

namespace Drupal\vss_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Url;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Form\FormStateInterface;

 /**
 * Constructs a new VirtualSafeSpaceForm object.
 */
class VirtualSafeSpaceForm extends FormBase {

  /**
   * Drupal\Core\Language\LanguageManagerInterface.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->languageManager = $container->get('language_manager');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * Form ID function
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'location_form';
  }

  /**
   * Constructs a new VirtualSpace form.
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $all_domains = \Drupal::service('entity_type.manager')->getStorage('domain')->loadMultipleSorted(NULL);
    foreach ($all_domains as $domain) {
      $domain_status = $domain->get('status');
      if ($domain_status == TRUE && $domain->get('name') != 'Virtualsafespace') {
        $domain_name = $domain->get('name');
        $domain_path = $domain->get('path');
        $domain_list[$domain_path] = $domain_name;
      }
    }

    $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
    $languages = \Drupal::languageManager()->getLanguages();
    foreach ($languages as $langcode => $language) {
      if (array_key_exists($langcode, $prefixes)) {
        $lang[$langcode] = $language->getName();
      }
    }

    $form['country'] = [
      '#title' => t('Country'),
      '#type' => 'select',
      '#description' => 'Select country',
      '#options' => ['' => t('Select country')] + $domain_list,
      '#required' => TRUE,
      '#default_value' => !empty($commonConfig['get_help']) ? $commonConfig['get_help'] : '',
    ];

    $form['language'] = [
      '#title' => t('Language'),
      '#type' => 'select',
      '#description' => 'Select language',
      '#options' => ['' => t('Select language')] + $lang ,
      '#required' => TRUE,
      '#default_value' => !empty($commonConfig['get_help']) ? $commonConfig['get_help'] : '',
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $url = $form_state->getValue('country') . '/' . $form_state->getValue('language');
    $response = new TrustedRedirectResponse(Url::fromUri($url)->toString());
    $form_state->setResponse($response);
    return;
  }

}
