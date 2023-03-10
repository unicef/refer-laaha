<?php

namespace Drupal\erpw_field_access\Services;

/**
 * Class SettingsArray.
 *
 * @package Drupal\erpw_field_access\SettingsArray
 */
class SettingsArray {

  /**
   * Function to minify the array.
   */
  public function minify($arr) {
    $settingsArray = [];
    foreach ($arr as $key => $value) {
      if (is_array($value)) {
        foreach ($value as $sub_key => $sub_value) {
          if (is_array($sub_value)) {
            foreach ($sub_value as $country_key => $country_value) {
              if (is_array($country_value)) {
                foreach ($country_value as $state_key => $state_value) {
                  if (is_array($state_value)) {
                    foreach ($state_value as $role => $access) {
                      if ($access != 0) {
                        $settingsArray[$key][$country_key][$state_key][$role] = $access;
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
    return $settingsArray;
  }

}
