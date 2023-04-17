<?php

namespace Drupal\erpw_webform\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\RenderElement;

/**
 * Defines the 'user_location_list_element' element.
 *
 * @RenderElement("user_location_list_element")
 */
class UserLocationListElement extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processUserLocationListElement'],
      ],
      '#pre_render' => [
        [$class, 'preRenderUserLocationListElement'],
      ],
      '#webform_submission' => [
        [$class, 'submitUserLocationListElement'],
      ],
      '#theme' => 'form_element__webform_user_location_list_element',
      '#theme_wrappers' => ['form_element'],
      '#submit' => [
        [$class, 'submitUserLocationListElement'],
      ],
    ];
  }

  /**
   * Processes a 'user_location_list_element' element.
   */
  public static function processUserLocationListElement(&$element, FormStateInterface $form_state, &$complete_form) {
    // Call your custom form and pass any necessary arguments.
    $form = \Drupal::formBuilder()->getForm('Drupal\erpw_location\Form\LocationListForm');

    // Add the custom form to the element's children array.
    $element['#children'] = \Drupal::service('renderer')->render($form);
    dump($element);
    return $element;
  }

  /**
   *
   */
  public static function preRenderUserLocationListElement(array $element) {
    return $element;
  }

  /**
   * Custom submit handler for 'user_location_list_element' element.
   */
  public static function submitUserLocationListElement(&$element, FormStateInterface $form_state, &$complete_form) {
    // Perform some action with the submitted data.
    $submitted_values = $form_state->getValues();
    // ...
    die('submitUserLocationListElement called');
    exit;
  }

}
