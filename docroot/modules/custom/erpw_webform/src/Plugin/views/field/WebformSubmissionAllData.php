<?php

namespace Drupal\erpw_webform\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * Custom Views field plugin.
 *
 * @ViewsField("webform_submission_all_data")
 */
class WebformSubmissionAllData extends FieldPluginBase {

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
        $output[] = ['webformID' => $webformID];
        // Get the "Submitted By" user ID from the submission.
        $submitted_by_uid = $webformSubmission->get('uid')->target_id;

        // Load the user entity based on the user ID.
        $user = User::load($submitted_by_uid);

        if ($user) {
          // Get user information, such as name and email.
          $output[] = ['Submitted By' => $user->getAccountName()];
        }
        else {
          $output[] = ['Submitted By' => t('Not available')];
        }
        if (!is_null($stype) && !empty($stype)) {
          $servicetype = \Drupal::entityTypeManager()->getStorage('node')->load(intval($stype));
          if ($servicetype instanceof Node) {
            $servicelabel = $servicetype->get('title')->getValue()[0]['value'];
          }
          else {
            $servicelabel = t('Not available');
          }
          $output[] = ['Service Type' => $servicelabel];
          $output[] = ['Service Type Color' => $servicetype->get('field_service_type_color')->getValue()[0]['color']];
          $output[] = ['Service Type Icon' => $servicetype->get('field_service_type_icon')->getValue()[0]['value']];
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
          if ($key == 'erpw_workflow') {
            $op = '';
            $opClass = '';
            if ($content['workflow_state'] == 'approve') {
              $op = 'Approved';
              $opClass = 'approved-workflow';
            }
            elseif ($content['workflow_state'] == 'reject') {
              $op = 'Rejected';
              $opClass = 'rejected-workflow';
            }
            elseif ($content['workflow_state'] == 'draft') {
              $op = 'Draft';
              $opClass = 'draft-workflow';
            }
            elseif ($content['workflow_state'] == 'in_review') {
              $op = 'In Review with GBV Coordination';
              $opClass = 'in-review-coordination-workflow';
            }
            elseif ($content['workflow_state'] == 'in_review_with_focal_point') {
              $op = 'In Review with Focal Point';
              $opClass = 'in-review-focal-point-workflow';
            }
            elseif ($content['workflow_state'] == 'edits_in_review_with_focal_point') {
              $op = 'Edits In Review with Focal Point';
              $opClass = 'edits-in-review-focal-point-workflow';

            }
            elseif ($content['workflow_state'] == 'edits_in_review_with_gbv_coordination') {
              $op = 'Edits In Review with GBV Coordination';
              $opClass = 'edits-in-review-coordination-workflow';
            }
            elseif ($content['workflow_state'] == 'deletion_in_review_with_focal_point') {
              $op = 'Deletion In Review with Focal Point';
              $opClass = 'deletion-in-review-focal-point-workflow';
            }
            elseif ($output == 'deletion_in_review_with_gbv_coordination') {
              $op = 'Deletion In Review with GBV Coordination';
              $opClass = 'deletion-in-review-coordination-workflow';
            }
            elseif ($output == 'deleted') {
              $op = 'Deleted';
              $opClass = 'deleted-workflow';
            }
            else {
              $op = t('Not available.');
            }
            $output[] = ['Status' => $op];
            $output[] = ['StatusClass' => $opClass];
          }
        }
        // Sort the output alphabetically.
        usort($output, function ($a, $b) {
          $keyA = key($a);
          $keyB = key($b);
          return strcmp($keyA, $keyB);
        });
      }
      $output[]['sid'] = $submissionID;
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

}
