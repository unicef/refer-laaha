<?php

namespace Drupal\erpw_webform\Element;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\webform\Element\WebformCompositeBase;

/**
 * Provides a 'webform_example_composite'.
 *
 * Webform composites contain a group of sub-elements.
 *
 *
 * IMPORTANT:
 * Webform composite can not contain multiple value elements (i.e. checkboxes)
 * or composites (i.e. webform_address)
 *
 * @FormElement("location_list_element")
 *
 * @see \Drupal\webform\Element\WebformCompositeBase
 * @see \Drupal\webform_example_composite\Element\WebformExampleComposite
 */
class LocationList extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return parent::getInfo() + ['#theme' => 'location_list'];
  }

  /**
   * {@inheritdoc}
   */
  public static function getCompositeElements(array $element) {
    $elements = [];
    $location_entities = \Drupal::entityTypeManager()->getStorage('location')->loadByProperties(
      ['type' => 'country', 'status' => 1]);
    $location_options = [];
    foreach ($location_entities as $location) {
      $location_options[$location->id()] = $location->get('name')->getValue()[0]['value'];
    }
    asort($location_options);
    if ($terms_array = \Drupal::cache()->get('erpw_locations_list')) {
      $terms_array = $terms_array->data;
    }
    else {
      $terms_array = [];
      // Terms array.
      $level_zero_tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree(
        'country',
        0,
        1,
        TRUE
      );
      foreach ($level_zero_tree as $level_zero) {
        $termid = $level_zero->get('tid')->value;
        foreach ($location_options as $key => $value) {
          if ($value == $level_zero->get('name')->value) {
            $terms_array[$key] = ['name' => $level_zero->get('name')->value];
            $tid = $key;
            $terms_array[$key]['level_label'] =
              ucfirst(
                strtolower(
                  $location_entities[$key]->get('level_1')->getValue()[0]['value']
                )
              );
          }
        }
        $level_one_tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree(
          'country',
          $termid,
          1,
          TRUE
        );
        foreach ($level_one_tree as $level_one) {
          $terms_array[$tid]['children'][$level_one->get('tid')->value] = [
            'name' => $level_one->get('name')->value,
            'parent_id' => $tid,
          ];
          $terms_array[$tid]['children'][$level_one->get('tid')->value]['level_label'] =
            ucfirst(
              strtolower(
                $location_entities[$tid]->get('level_2')->getValue()[0]['value']
              )
            );
          $level_two_tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree(
            'country',
            $level_one->get('tid')->value,
            1,
            TRUE
          );
          foreach ($level_two_tree as $level_two) {
            $terms_array[$tid]['children'][$level_one->get('tid')->value]['children'][$level_two->get('tid')->value] =
              [
                'name' => $level_two->get('name')->value,
                'parent_id' => $level_one->get('tid')->value,
              ];
            $terms_array[$tid]['children'][$level_one->get('tid')->value]['children'][$level_two->get('tid')->value]['level_label'] =
              ucfirst(
                strtolower(
                  $location_entities[$tid]->get('level_3')->getValue()[0]['value']
                )
              );
            $level_three_tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree(
              'country',
              $level_two->get('tid')->value,
              1,
              TRUE
            );
            foreach ($level_three_tree as $level_three) {
              $terms_array[$tid]['children'][$level_one->get('tid')->value]['children'][$level_two->get('tid')->value]['children'][$level_three->get('tid')->value] =
                [
                  'name' => $level_three->get('name')->value,
                  'parent_id' => $level_two->get('tid')->value,
                ];
              $terms_array[$tid]['children'][$level_one->get('tid')->value]['children'][$level_two->get('tid')->value]['children'][$level_three->get('tid')->value]['level_label'] =
                $location_entities[$tid]->get('level_4')->getValue() != [] ?
                  ucfirst(strtolower($location_entities[$tid]->get('level_4')->getValue()[0]['value'])) : '';
              $level_four_tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree(
                'country',
                $level_three->get('tid')->value,
                1,
                TRUE
              );
              foreach ($level_four_tree as $level_four) {
                $terms_array[$tid]['children'][$level_one->get('tid')->value]['children'][$level_two->get('tid')->value]['children'][$level_three->get('tid')->value]['children'][$level_four->get('tid')->value] =
                  [
                    'name' => $level_four->get('name')->value,
                    'parent_id' => $level_three->get('tid')->value,
                  ];
              }
            }
          }
        }
      }
      \Drupal::cache()->set('erpw_locations_list', $terms_array, CacheBackendInterface::CACHE_PERMANENT, ['erpw_locations_list']);
    }
    $elements['location_options'] = [
      '#type' => 'select',
      '#options' => $location_options ?? [],
      '#empty_option' => t('Select Country'),
      '#title' => t('Country'),
      '#required' => TRUE,
      '#weight' => -109,
      '#attributes' => [
        'class' => ['location_options'],
      ],
      '#default_value' => $element['#default_value']['location_options'] ?? '',
    ];
    $elements['level_1'] = [
      '#type' => 'select',
      '#empty_option' => t("Select province/district"),
      '#empty_value' => '',
      '#options' => [],
      '#title' => t("Select province/district"),
      '#weight' => -108,
      '#validated' => TRUE,
      '#default_value' => $element['#default_value']['level_1'] ?? '',
      '#prefix' => '<div id="edit-location-details" ><div id="location-level-1">',
      '#suffix' => '</div>',
      '#attributes' => [
        'class' => ['level_1'],
      ],
    ];
    $elements['level_2'] = [
      '#type' => 'select',
      '#empty_option' => t("Select district/upazila"),
      '#empty_value' => '',
      '#options' => [],
      '#title' => t("Select district/upazila"),
      '#weight' => -106,
      '#validated' => TRUE,
      '#default_value' => $element['#default_value']['level_2'] ?? '',
      '#prefix' => '<div id="location-level-2">',
      '#suffix' => '</div>',
      '#attributes' => [
        'class' => ['level_2'],
      ],
    ];
    $elements['level_3'] = [
      '#type' => 'select',
      '#empty_option' => t("Select Level 3 Label"),
      '#empty_value' => '',
      '#options' => [],
      '#title' => t("Select Level 3 Label"),
      '#weight' => -104,
      '#validated' => TRUE,
      '#prefix' => '<div id="location-level-3">',
      '#suffix' => '</div>',
      '#default_value' => $element['#default_value']['level_3'] ?? '',
      '#attributes' => [
        'class' => ['level_3'],
      ],
    ];
    $elements['level_4'] = [
      '#type' => 'select',
      '#empty_option' => t("Select Level 4 Label"),
      '#empty_value' => '',
      '#options' => [],
      '#title' => t("Select Level 4 Label"),
      '#weight' => -102,
      '#validated' => TRUE,
      '#default_value' => $element['#default_value']['level_4'] ?? '',
      '#prefix' => '<div id="location-level-4">',
      '#suffix' => '</div></div>',
      '#attributes' => [
        'class' => ['level_4'],
      ],
    ];

    $elements['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $elements['#attached']['library'][] = 'erpw_custom/erpw_js';
    $elements['#attached']['drupalSettings']['erpw_webform']['termsArray'] = $terms_array;
    $elements['#attached']['library'][] = 'erpw_location/erpw_location_js';
    return $elements;
  }

}
