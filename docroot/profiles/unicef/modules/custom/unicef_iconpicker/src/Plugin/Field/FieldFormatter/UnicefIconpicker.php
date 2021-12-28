<?php

namespace Drupal\unicef_iconpicker\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'unicef_iconpicker' formatter.
 *
 * @FieldFormatter(
 *   id = "unicef_iconpicker_formatter_type",
 *   label = @Translation("Unicef Icon"),
 *   field_types = {
 *     "text",
 *     "string",
 *   }
 * )
 */
class UnicefIconpicker extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      // Implement default settings.
      'size' => '1',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];
    $elements['size'] = [
      '#type'           => 'number',
      '#title'          => $this->t('Icon Ratio'),
      '#description'    => $this->t('Select an icon ratio.'),
      '#default_value'  => $this->getSetting('size'),
      '#step'  => 0.1,
      '#min'  => 1,
      '#max'  => 50,
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    // Implement settings summary.
    $summary[] = $this->t('Ratio: @size', [
      '@size'     => $this->getSetting('size'),
    ]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $size = $this->getSetting('size');
      $safe_value = $this->viewValue($item);
      print_r($safe_value);
      die;
      $elements[$delta] = [
        '#theme' => 'unicef_iconpicker_formatter',
        '#icon' => $safe_value,
        '#size' => $size,
      ];
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    // The text value has no text format assigned to it, so the user input
    // should equal the output, including newlines.
    return nl2br(Html::escape($item->value));
  }

}
