<?php

namespace Drupal\erpw_ussd_api\Plugin\views\field;

use Drupal\node\Entity\Node;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Custom Views field plugin.
 *
 * @ViewsField("ussd_services_data")
 */
class UssdServicesData extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    if ($values->_entity->getData() !== NULL) {
      $submissionID = $values->_entity->get('sid')->getValue()[0]['value'];
      if (!is_null($values)) {
        $webformSubmission = \Drupal::entityTypeManager()->getStorage('webform_submission')->load($submissionID);
        $webformID = $webformSubmission->get('webform_id')->getValue()[0]['target_id'];
        $webform = \Drupal::entityTypeManager()->getStorage('webform')->load($webformID);
        $tpa = $webform->getThirdPartySetting('erpw_webform', 'webform_service_type_map');
        $activeDomain = \Drupal::service('domain.negotiator')->getActiveDomain()->id();
        $stype = '';
        foreach ($tpa as $domain => $servicetype) {
          if ($domain == $activeDomain) {
            $stype = $servicetype[0];
          }
        }
        $output = [];

        if (!is_null($stype) && !empty($stype)) {
          $servicetype = \Drupal::entityTypeManager()->getStorage('node')->load(intval($stype));
          if ($servicetype instanceof Node) {
            $servicelabel = $servicetype->get('title')->getValue()[0]['value'];
            $output[] = ['Service Type' => $servicelabel];
          }
          else {
            $servicelabel = t('Not available');
            $output[] = ['Service Type' => $servicelabel];
          }
        }
        $fields = $webformSubmission->getData();
        $location = '';
        $country = '';
        $level_1 = '';
        $level_2 = '';
        $level_3 = '';
        $level_4 = '';
        foreach ($fields as $key => $content) {
          $element = \Drupal::entityTypeManager()->getStorage('webform')->load($webformSubmission->getWebform()->id())->getElement($key);
          if ($key != 'erpw_workflow' && $key != 'submission_domain' && $key != 'service_type') {
            $roles = \Drupal::service('current_user')->getRoles();
            if (isset($element['#access_view_roles'])) {
              foreach ($roles as $role) {
                if (in_array($role, $element['#access_view_roles'])) {
                  if ($key == 'location') {
                    foreach ($content as $lkey => $lvalue) {
                      if ($lkey == 'location_options' && ($lvalue != '' && $lvalue != NULL && $lvalue != 0)) {
                        $country = \Drupal::entityTypeManager()->getStorage('location')->load($lvalue)->getName();
                        $location = $location . $country . '.';
                      }
                      if ($lkey == 'level_1' && ($lvalue != '' && $lvalue != NULL && $lvalue != 0)) {
                        $level_1 = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($lvalue)->getName();
                        $location = $level_1 . ', ' . $location;
                      }
                      if ($lkey == 'level_2' && ($lvalue != '' && $lvalue != NULL && $lvalue != 0)) {
                        $level_2 = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($lvalue)->getName();
                        $location = $level_2 . ', ' . $location;
                      }
                      if ($lkey == 'level_3' && ($lvalue != '' && $lvalue != NULL && $lvalue != 0)) {
                        $level_3 = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($lvalue)->getName();
                        $location = $level_3 . ', ' . $location;
                      }
                      if ($lkey == 'level_4' && ($lvalue != '' && $lvalue != NULL && $lvalue != 0)) {
                        $level_4 = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($lvalue)->getName();
                        $location = $level_4 . ', ' . $location;
                      }
                    }
                    $output[] = ['Location' => $location];
                  }
                  elseif ($element['#type'] == 'checkbox') {
                    if ($content != NULL) {
                      if ($content == 1) {
                        $output[] = [$element['#title'] => t('Yes')];
                      }
                      else {
                        $output[] = [$element['#title'] => t('No')];
                      }
                    }
                  }
                  elseif ($element['#type'] == 'checkboxes') {
                    $values = [];
                    if (gettype($content) == 'array' & $content != NULL) {
                      foreach ($content as $key) {
                        if ($element['#options'][$key] != NULL) {
                          array_push($values, $element['#options'][$key]);
                        }
                      }
                      $output[] = [$element['#title'] => $values];
                    }
                    else {
                      if ($content != NULL) {
                        $output[] = [$element['#title'] => $element['#options'][$content]];
                      }
                    }
                  }
                  elseif ($element['#type'] == 'radios') {
                    if ($element['#options'][$content] != NULL) {
                      $output[] = [$element['#title'] => $element['#options'][$content]];
                    }
                  }
                  elseif ($element['#type'] == 'select') {
                    $values = [];
                    if (gettype($content) == 'array' & $content != NULL) {
                      foreach ($content as $key) {
                        if ($element['#options'][$key] != NULL) {
                          array_push($values, $element['#options'][$key]);
                        }
                      }
                      $output[] = [$element['#title'] => $values];
                    }
                    else {
                      if ($content != NULL) {
                        $output[] = [$element['#title'] => $element['#options'][$content]];
                      }
                    }
                  }
                  elseif ($element['#type'] == 'webform_entity_select') {
                    if ($element['#title'] = 'Organisation') {
                      if (!empty($content)) {
                        $orgLabel = \Drupal::entityTypeManager()->getStorage('node')->load($content)->get('title')->getValue()[0]['value'];
                        $output[] = [$element['#title'] => $orgLabel];
                      }
                    }
                  }
                  elseif ($element['#type'] == 'webform_mapping') {
                    $form_data = $webformSubmission->getData();
                    if (isset($form_data['opening_times'])) {
                      $opening_hours_structured_data = $this->getOpeningHoursData($form_data['opening_times']);
                      if ($opening_hours_structured_data != NULL && !empty($opening_hours_structured_data)) {
                        $output[]['Opening Times'] = $opening_hours_structured_data;
                      }
                    }
                  }
                  elseif ($key == 'orignal_data') {
                  }
                  else {
                    if ($content != "") {
                      $output[] = [$element['#title'] => $content];
                    }
                  }
                }
              }
            }
            else {
              if ($key == 'location') {
                foreach ($content as $lkey => $lvalue) {
                  if ($lkey == 'location_options' && ($lvalue != '' && $lvalue != NULL && $lvalue != 0)) {
                    $country = \Drupal::entityTypeManager()->getStorage('location')->load($lvalue)->getName();
                    $location = $location . $country . '.';
                  }
                  if ($lkey == 'level_1' && ($lvalue != '' && $lvalue != NULL && $lvalue != 0)) {
                    $level_1 = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($lvalue)->getName();
                    $location = $level_1 . ', ' . $location;
                  }
                  if ($lkey == 'level_2' && ($lvalue != '' && $lvalue != NULL && $lvalue != 0)) {
                    $level_2 = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($lvalue)->getName();
                    $location = $level_2 . ', ' . $location;
                  }
                  if ($lkey == 'level_3' && ($lvalue != '' && $lvalue != NULL && $lvalue != 0)) {
                    $level_3 = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($lvalue)->getName();
                    $location = $level_3 . ', ' . $location;
                  }
                  if ($lkey == 'level_4' && ($lvalue != '' && $lvalue != NULL && $lvalue != 0)) {
                    $level_4 = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($lvalue)->getName();
                    $location = $level_4 . ', ' . $location;
                  }
                }
                $output[] = ['Location' => $location];
              }
              elseif (isset($element['#type']) && $element['#type'] === 'checkbox') {
                if ($content != NULL) {
                  if ($content == 1) {
                    $output[] = [$element['#title'] => t('Yes')];
                  }
                  else {
                    $output[] = [$element['#title'] => t('No')];
                  }
                }
              }
              elseif (isset($element['#type']) && $element['#type'] === 'checkboxes') {
                $values = [];
                if (gettype($content) == 'array' & $content != NULL) {
                  foreach ($content as $key) {
                    if ($element['#options'][$key] != NULL) {
                      array_push($values, $element['#options'][$key]);
                    }
                  }
                  $output[] = [$element['#title'] => $values];
                }
                else {
                  if ($content != NULL) {
                    $output[] = [$element['#title'] => $element['#options'][$content]];
                  }
                }
              }
              elseif (isset($element['#type']) && $element['#type'] == 'radios') {
                if ($content != NULL && !empty($content) && $element['#options'][$content] != NULL) {
                  $output[] = [$element['#title'] => $element['#options'][$content]];
                }
              }
              elseif (isset($element['#type']) && $element['#type'] === 'select') {
                $values = [];
                if (gettype($content) == 'array' & $content != NULL) {
                  foreach ($content as $key) {
                    if ($element['#options'][$key] != NULL) {
                      array_push($values, $element['#options'][$key]);
                    }
                  }
                  $output[] = [$element['#title'] => $values];
                }
                else {
                  if ($content != NULL) {
                    $output[] = [$element['#title'] => $element['#options'][$content]];
                  }
                }
              }
              elseif (isset($element['#type']) && $element['#type'] === 'webform_entity_select') {
                if ($element['#title'] = 'Organisation') {
                  if (!empty($content)) {
                    $org = \Drupal::entityTypeManager()->getStorage('node')->load($content);
                    // Null check for org.
                    $orgLabel = is_null($org) ? 'N/A' : $org->get('title')->getValue()[0]['value'];
                    $output[] = [$element['#title'] => $orgLabel];
                  }
                }
              }
              elseif (isset($element['#type']) && $element['#type'] === 'webform_mapping') {
                $form_data = $webformSubmission->getData();
                if (isset($form_data['opening_times'])) {
                  $opening_hours_structured_data = $this->getOpeningHoursData($form_data['opening_times']);
                  if ($opening_hours_structured_data != NULL && !empty($opening_hours_structured_data)) {
                    $output[]['Opening Times'] = $opening_hours_structured_data;
                  }
                }
              }
              elseif ($key === 'orignal_data') {
              }
              else {
                if ($content != '') {
                  $output[] = [$element['#title'] => $content];
                }
              }
            }
          }
        }
        // Sort the output alphabetically.
        usort($output, function ($a, $b) {
          $keyA = key($a);
          $keyB = key($b);
          return strcmp($keyA, $keyB);
        });
      }

      $outputArray = [];

      foreach ($output as $innerArray) {
        foreach ($innerArray as $key => $value) {
          $outputArray[$key] = $value;
        }
      }
      $restData = json_encode($outputArray);
    }
    else {
      $restData = t('Not available.');
    }
    return $restData;
  }

  /**
   * Helper function which provides the opening hours in a structured format.
   */
  public function getOpeningHoursData(array $opening_hours_data) {
    $temp_opening_hours = [];
    $updated_opening_hours = [];
    foreach ($opening_hours_data as $key => $value) {
      $key = strtolower($key);
      switch (trim($key)) {
        case 'monday':
        case 'mon':
          $temp_opening_hours[0][$key] = "<p class='opening-hours-value'>" . ucfirst($key) . " : " . $value . '</p>';
          break;

        case 'tuesday':
        case 'tue':
          $temp_opening_hours[1][$key] = "<p class='opening-hours-value'>" . ucfirst($key) . " : " . $value . '</p>';
          break;

        case 'wednesday':
        case 'wed':
          $temp_opening_hours[2][$key] = "<p class='opening-hours-value'>" . ucfirst($key) . " : " . $value . '</p>';
          break;

        case 'thursday':
        case 'thu':
          $temp_opening_hours[3][$key] = "<p class='opening-hours-value'>" . ucfirst($key) . " : " . $value . '</p>';
          break;

        case 'friday':
        case 'fri':
          $temp_opening_hours[4][$key] = "<p class='opening-hours-value'>" . ucfirst($key) . " : " . $value . '</p>';
          break;

        case 'saturday':
        case 'sat':
          $temp_opening_hours[5][$key] = "<p class='opening-hours-value'>" . ucfirst($key) . " : " . $value . '</p>';
          break;

        case 'sunday':
        case 'sun':
          $temp_opening_hours[6][$key] = "<p class='opening-hours-value'>" . ucfirst($key) . " : " . $value . '</p>';
          break;

        default:
          $temp_opening_hours[][$key] = "<p class='opening-hours-value'>" . ucfirst($key) . " : " . $value . '</p>';
      }
    }
    ksort($temp_opening_hours);
    foreach ($temp_opening_hours as $key => $value) {
      $updated_opening_hours[key($value)] = reset($value);
    }
    return $updated_opening_hours;
  }

}
