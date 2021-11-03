<?php

namespace Drupal\vss_custom\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form that configures forms module settings.
 */
class ExitWebsiteConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected $state;

  /**
   * Constructs a new ExitWebsite object.
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->state = $container->get('state');
    return $instance;
  }

  /**
   * Form ID function.
   */
  public function getFormId() {
    return 'exit_website_form';
  }

  /**
   * Config name function.
   */
  protected function getEditableConfigNames() {
    return ['exit_website.config'];
  }

  /**
   * Constructs a new ExitWebsite form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $exit_url = $this->state->get('exit_url');
    $form['#prefix'] = '<div id = "exit-website">';
    $form['#suffix'] = '</div>';
    $form['exit_url'] = [
      '#type' => 'textfield',
      '#title' => 'Exit Website URL',
      '#default_value' => $exit_url,
      '#required' => FALSE,
      '#attributes' => ['placeholder' => 'Enter URL after clicking on exit website'],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->state->set('exit_url', $form_state->getValue('exit_url'));
    parent::submitForm($form, $form_state);
  }

}
