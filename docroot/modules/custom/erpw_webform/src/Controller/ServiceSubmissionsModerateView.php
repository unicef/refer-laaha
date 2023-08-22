<?php

namespace Drupal\erpw_webform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Session\AccountInterface;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\erpw_webform\Controller\ServiceSubmissionsView;

/**
 * Generate key value pair of elements in the webform submission view page.
 */
class ServiceSubmissionsModerateView extends ControllerBase {
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new ServiceWebforms object.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, AccountInterface $currentUser) {
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
    $container->get('entity_type.manager'),
    $container->get('current_user'),
    );
  }

  /**
   * Generate key value pair of elements in the webform submission.
   */
  public function content(WebformSubmission $webform_submission) {
    if (!is_null($webform_submission)) {
      $output = [];
      $fields = $webform_submission->getData();
      $orginalData = json_decode($fields['orignal_data'], TRUE);
      $changedData = !is_null($orginalData) ? array_diff_assoc($orginalData, $fields) : '';
      $changedUser = !is_null($orginalData) ? $orginalData['erpw_workflow']['changed_user'] : '';
      $oldUserMail = !is_null($orginalData['erpw_workflow']['changed_user']) ? $this->entityTypeManager->getStorage('user')->load($orginalData['erpw_workflow']['changed_user'])->getEmail() : '';
      if (!is_null($this->entityTypeManager->getStorage('user')->load($fields['erpw_workflow']['changed_user']))) {
        $newUserMail = !is_null($fields['erpw_workflow']['changed_user']) ? $this->entityTypeManager->getStorage('user')->load($fields['erpw_workflow']['changed_user'])->getEmail() : '';
      }
      else {
        $newUserMail = '';
      }
      $orgTime = !is_null($orginalData) ? date("d/m/Y - H:i", $orginalData['erpw_workflow']['changed_timestamp']) : '';
      $newTime = date("d/m/Y - H:i", $fields['erpw_workflow']['changed_timestamp']);
      $location = '';
      $country = '';
      $level_1 = '';
      $level_2 = '';
      $level_3 = '';
      $level_4 = '';
      foreach ($fields as $key => $content) {
        $element = $this->entityTypeManager->getStorage('webform')->load($webform_submission->getWebform()->id())->getElement($key);
        if ($key != 'erpw_workflow' && $key != 'submission_domain' && $key != 'service_type') {
          $roles = $this->currentUser->getRoles();
          if (isset($element['#access_view_roles'])) {
            foreach ($roles as $role) {
              if (in_array($role, $element['#access_view_roles'])) {
                if ($key == 'location') {
                  foreach ($content as $lkey => $lvalue) {
                    if ($lkey == 'location_options' && ($lvalue != '' && $lvalue != NULL && $lvalue != 0)) {
                      $country = $this->entityTypeManager()->getStorage('location')->load($lvalue)->getName();
                      $location = $country . '.';
                    }
                    if ($lkey == 'level_1' && ($lvalue != '' && $lvalue != NULL && $lvalue != 0)) {
                      $level_1 = $this->entityTypeManager()->getStorage('taxonomy_term')->load($lvalue)->getName();
                      $location = $level_1 . ', ' . $location;
                    }
                    if ($lkey == 'level_2' && ($lvalue != '' && $lvalue != NULL && $lvalue != 0)) {
                      $level_2 = $this->entityTypeManager()->getStorage('taxonomy_term')->load($lvalue)->getName();
                      $location = $level_2 . ', ' . $location;
                    }
                    if ($lkey == 'level_3' && ($lvalue != '' && $lvalue != NULL && $lvalue != 0)) {
                      $level_3 = $this->entityTypeManager()->getStorage('taxonomy_term')->load($lvalue)->getName();
                      $location = $level_3 . ', ' . $location;
                    }
                    if ($lkey == 'level_4' && ($lvalue != '' && $lvalue != NULL && $lvalue != 0)) {
                      $level_4 = $this->entityTypeManager()->getStorage('taxonomy_term')->load($lvalue)->getName();
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
                      $orgLabel = $this->entityTypeManager->getStorage('node')->load($content)->get('title')->getValue()[0]['value'];
                      $output[] = [$element['#title'] => $orgLabel];
                    }
                  }
                }
                elseif ($element['#type'] == 'webform_mapping') {
                  $form_data = $webform_submission->getData();
                  if (isset($form_data['opening_times'])) {
                    $service_submission_view = new ServiceSubmissionsView($this->entityTypeManager, $this->currentUser);
                    $opening_hours_structured_data = $service_submission_view->getOpeningHoursData($form_data['opening_times']);
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
                  $country = $this->entityTypeManager()->getStorage('location')->load($lvalue)->getName();
                  $location = $country . '.';
                }
                if ($lkey == 'level_1' && ($lvalue != '' && $lvalue != NULL && $lvalue != 0)) {
                  $level_1 = !is_null($this->entityTypeManager()->getStorage('taxonomy_term')->load($lvalue)) ? $this->entityTypeManager()->getStorage('taxonomy_term')->load($lvalue)->getName() : '';
                  $location = $level_1 . ', ' . $location;
                }
                if ($lkey == 'level_2' && ($lvalue != '' && $lvalue != NULL && $lvalue != 0)) {
                  $level_2 = $this->entityTypeManager()->getStorage('taxonomy_term')->load($lvalue)->getName();
                  $location = $level_2 . ', ' . $location;
                }
                if ($lkey == 'level_3' && ($lvalue != '' && $lvalue != NULL && $lvalue != 0)) {
                  $level_3 = $this->entityTypeManager()->getStorage('taxonomy_term')->load($lvalue)->getName();
                  $location = $level_3 . ', ' . $location;
                }
                if ($lkey == 'level_4' && ($lvalue != '' && $lvalue != NULL && $lvalue != 0)) {
                  $level_4 = $this->entityTypeManager()->getStorage('taxonomy_term')->load($lvalue)->getName();
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
                  $orgLabel = $this->entityTypeManager->getStorage('node')->load($content)->get('title')->getValue()[0]['value'];
                  $output[] = [$element['#title'] => $orgLabel];
                }
              }
            }
            elseif ($element['#type'] == 'webform_mapping') {
              $form_data = $webform_submission->getData();
              if (isset($form_data['opening_times'])) {
                $service_submission_view = new ServiceSubmissionsView($this->entityTypeManager, $this->currentUser);
                $opening_hours_structured_data = $service_submission_view->getOpeningHoursData($form_data['opening_times']);
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
      $edit_url = Url::fromRoute('entity.webform_submission.edit_form', [
        'webform' => $webform_submission->getWebform()->id(),
        'webform_submission' => $webform_submission->id(),
      ])->toString();

      if ($this->currentUser->isAnonymous()) {
        $markup = '
          <div class="service-provider-details">
            <div class="service-detail-heading">
            <h3>' . t('Service Details') . '</h3>
            </div>
          </div>';
      }
      else {
        $markup = '
          <div class="service-provider-comparing-box">' . t('Comparing') . '
            <div class="content">
              <div class="row">
                <a href="/user/' . $changedUser . '">
                  <div>' . $orgTime . '</div>
                  <div>' . $oldUserMail . '</div>
                </a>
              </div>
              <div class="row">
                <a href="/user/' . $fields['erpw_workflow']['changed_user'] . '">
                  <div>' . $newTime . '</div>
                  <div>' . $newUserMail . '</div>
                </a>
              </div>
            </div>
          </div>
          <div class="service-provider-details">
            <div class="service-detail-heading">
              <h3>' . t('Service Details Overview') . '</h3>
              <div class="edit-delete-links">
                <span class="review-link">
                  <a href=' . $edit_url . '>Review</a>
                </span>
              </div>
            </div>
          </div>';
      }
      // Assocative array with label and value of moderated content.
      $changedLabelData = [];
      foreach ($fields as $key => $content) {
        $element = $this->entityTypeManager->getStorage('webform')->load($webform_submission->getWebform()->id())->getElement($key);
        foreach ($changedData as $ckey => $cvalue) {
          if ($ckey == $key && !is_null($element['#title'])) {
            $changedLabelData[$element['#title']] = $cvalue;
          }
        }
      }
      // Sort the output alphabetically.
      usort($output, function ($a, $b) {
          $keyA = key($a);
          $keyB = key($b);
          return strcmp($keyA, $keyB);
      });
      foreach ($output as $item) {
        foreach ($item as $key => $value) {
          $markup .= '<div class="pair-container"><span class="label">' . Markup::create($key) . ':</span>';
          $c = 0;
          foreach ($changedLabelData as $ckey => $cvalue) {
            if ($ckey == $key) {
              $c++;
              $markup .= '<div class="pair-container-moderated">';
              if ($key == 'Opening Times' && is_array($value)) {
                $markup .= '<span class="value">' . Markup::create(implode("", $value)) . '</span>';
                continue;
              }
              if (is_array($cvalue)) {
                $markup .= '<span class="value-moderated">' . Markup::create(implode(", ", $cvalue)) . '</span>';
              }
              else {
                $markup .= '<span  class="value-moderated">' . Markup::create($cvalue) . '</span>';
              }
              if (is_array($value)) {
                $markup .= '<span class="value">' . Markup::create(implode(", ", $value)) . '</span>';
              }
              else {
                $markup .= '<span  class="value">' . Markup::create($value) . '</span>';

              }
              $markup .= '</div>';
            }
          }
          if ($c == 0) {
            if (is_array($value)) {
              $markup .= '<span class="value">' . Markup::create(implode(", ", $value)) . '</span>';
            }
            else {
              $markup .= '<span  class="value">' . Markup::create($value) . '</span>';

            }
          }
          $markup .= '</div>';
        }
      }
      return [
        '#type' => 'markup',
        '#markup' => $markup,
      ];
    }
    else {
      return [
        '#type' => 'markup',
        '#markup' => '<h3>' . t('No submission found.') . '</h3>',
      ];
    }
  }

}
