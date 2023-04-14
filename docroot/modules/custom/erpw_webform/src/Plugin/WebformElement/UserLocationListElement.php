<?php

namespace Drupal\erpw_webform\Plugin\WebformElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a 'user_location_list_element' webform element.
 *
 * @WebformElement(
 *   id = "user_location_list_element",
 *   label = @Translation("Location List Form"),
 *   description = @Translation("Renders the location list form"),
 *   category = @Translation("Custom"),
 * )
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
      '#theme' => 'webform_user_location_list_element',
      '#theme_wrappers' => ['form_element'],
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

    return $element;
  }

}
