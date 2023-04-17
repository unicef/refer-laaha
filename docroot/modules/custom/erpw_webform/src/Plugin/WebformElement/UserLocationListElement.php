<?php

namespace Drupal\erpw_webform\Plugin\WebformElement;

use Drupal\webform\Plugin\WebformElementBase;

/**
 * Provides a custom webform element that integrates an existing form.
 *
 * @WebformElement(
 *   id = "user_location_list_element",
 *   label = @Translation("Location list"),
 *   description = @Translation("Integrates an existing form into a webform."),
 *   category = @Translation("Custom"),
 * )
 */
class UserLocationListElement extends WebformElementBase {

  /**
   * {@inheritdoc}
   */
  protected function defineDefaultProperties() {
    // Here you define your webform element's default properties,
    // which can be inherited.
    //
    // @see \Drupal\webform\Plugin\WebformElementBase::defaultProperties
    // @see \Drupal\webform\Plugin\WebformElementBase::defaultBaseProperties
    return [
      'form_id' => 'user_location_list_element',
      'default' => '',
    ] + parent::defineDefaultProperties();
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array $form, array &$form_state, $webform, $element) {
    // Load and render your existing form here.
    $existing_form = \Drupal::formBuilder()->getForm('Drupal\erpw_location\Form\LocationListForm');
    $form['existing_form'] = \Drupal::service('renderer')->render($existing_form);
    return $form;
  }

}
