<?php

namespace Drupal\erpw_webform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Session\AccountInterface;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\WebformSubmissionForm;

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
      dump($webform_submission);
      $output = [];
      $fields = $webform_submission->getData();
      $location = '';
      $country = '';
      $level_1 = '';
      $level_2 = '';
      $level_3 = '';
      $level_4 = '';
      foreach ($fields as $key => $content) {
        $element = $this->entityTypeManager->getStorage('webform')->load($webform_submission->getWebform()->id())->getElement($key);
        if ($key != 'erpw_workflow') {
          $roles = $this->currentUser->getRoles();
          if (isset($element['#access_view_roles'])) {
            foreach ($roles as $role) {
              if (in_array($role, $element['#access_view_roles'])) {
                if ($key == 'location') {
                  if ($content['location_options'] != '') {
                    $country = $this->entityTypeManager()->getStorage('location')->load($content['location_options'])->getName();
                    $location = $country . '.';
                  }
                  if ($content['level_1'] != '') {
                    $level_1 = $this->entityTypeManager()->getStorage('taxonomy_term')->load($content['level_1'])->getName();
                    $location = $level_1 . ', ' . $location;
                  }
                  if ($content['level_2'] != '') {
                    $level_2 = $this->entityTypeManager()->getStorage('taxonomy_term')->load($content['level_2'])->getName();
                    $location = $level_2 . ', ' . $location;
                  }
                  if ($content['level_3'] != '') {
                    $level_3 = $this->entityTypeManager()->getStorage('taxonomy_term')->load($content['level_3'])->getName();
                    $location = $level_3 . ', ' . $location;
                  }
                  if ($content['level_4'] != '') {
                    $level_4 = $this->entityTypeManager()->getStorage('taxonomy_term')->load($content['level_4'])->getName();
                    $location = $level_4 . ', ' . $location;
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
                    $orgLabel = $this->entityTypeManager->getStorage('node')->load($content)->get('title')->getValue()[0]['value'];
                    $output[] = [$element['#title'] => $orgLabel];
                  }
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
              if ($content['location_options'] != '') {
                $country = $this->entityTypeManager()->getStorage('location')->load($content['location_options'])->getName();
                $location = $country . '.';
              }
              if (!empty($location['level_1'])) {
                $level_1 = $this->entityTypeManager()->getStorage('taxonomy_term')->load($content['level_1'])->getName();
                $location = $level_1 . ', ' . $location;
              }
              if (!empty($location['level_2'])) {
                $level_2 = $this->entityTypeManager()->getStorage('taxonomy_term')->load($content['level_2'])->getName();
                $location = $level_2 . ', ' . $location;
              }
              if (!empty($location['level_3'])) {
                $level_3 = $this->entityTypeManager()->getStorage('taxonomy_term')->load($content['level_3'])->getName();
                $location = $level_3 . ', ' . $location;
              }
              if (!empty($location['level_4'])) {
                $level_4 = $this->entityTypeManager()->getStorage('taxonomy_term')->load($content['level_4'])->getName();
                $location = $level_4 . ', ' . $location;
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
                $orgLabel = $this->entityTypeManager->getStorage('node')->load($content)->get('title')->getValue()[0]['value'];
                $output[] = [$element['#title'] => $orgLabel];
              }
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
          <div class="service-provider-details">
            <div class="service-detail-heading">
              <h3>' . t('Service Details') . '</h3>
              <div class="edit-delete-links">
                <span class="edit-link">
                  <a href=' . $edit_url . '>Edit</a>
                </span>
              </div>
            </div>
          </div>';
      }
      foreach ($output as $item) {
        foreach ($item as $key => $value) {
          $markup .= '<div class="pair-container"><span class="label">' . Markup::create($key) . ':</span>';
          if (is_array($value)) {
            $markup .= '<span class="value">' . Markup::create(implode(", ", $value)) . '</span>';
          }
          else {
            $markup .= '<span  class="value">' . Markup::create($value) . '</span>';
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
