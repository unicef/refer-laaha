<?php

namespace Drupal\unicef_iconpicker\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'unicef_iconpicker' widget.
 *
 * @FieldWidget(
 *   id = "unicef_iconpicker",
 *   label = @Translation("Unicef Icon Picker"),
 *   field_types = {
 *     "text",
 *     "string",
 *   }
 * )
 */
class UnicefIconpicker extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'size' => 60,
      'placeholder' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $elements['size'] = [
      '#type' => 'number',
      '#min' => 0,
      '#step' => 1,
      '#title' => $this->t('Field Size'),
      '#description' => $this->t('Select a field size.'),
      '#default_value' => $this->getSetting('size'),
    ];

    $elements['placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder'),
      '#default_value' => $this->getSetting('placeholder'),
      '#description' => $this->t('Text that will be shown inside the field until a value is entered.
        This hint is usually a sample value or a brief description of the expected format.'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    if (!empty($this->getSetting('placeholder'))) {
      $summary[] = $this->t('Placeholder: @placeholder', ['@placeholder' => $this->getSetting('placeholder')]);
    }

    if (!empty($this->getSetting('size'))) {
      $summary[] = $this->t('Field size: @size', ['@size' => $this->getSetting('size')]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['value'] = $element + [
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->value ?? NULL,
      '#size' => $this->getSetting('size'),
      '#icon' => $this->getSetting('icon'),
      '#placeholder' => $this->getSetting('placeholder'),
      '#maxlength' => $this->getFieldSetting('max_length'),
      '#attributes' => [
        'data-iconpicker' => '',
        'class' => [
          'unicef-iconpicker-element',
        ],
      ],
      '#attached' => ['library' => ['unicef_iconpicker/unicef-iconpicker']],
    ];

    return $element;
  }

}
