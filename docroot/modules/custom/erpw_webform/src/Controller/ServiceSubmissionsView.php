<?php

namespace Drupal\erpw_webform\Controller;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\webform\Entity\WebformSubmission;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generate key value pair of elements in the webform submission view page.
 */
class ServiceSubmissionsView extends ControllerBase {
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
  public function __construct(EntityTypeManagerInterface $entityTypeManager, AccountInterface $currentUser, ConfigFactory $config_factory,) {
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
    $container->get('entity_type.manager'),
    $container->get('current_user'),
    $container->get('config.factory'),
    );
  }

  /**
   * Generate key value pair of elements in the webform submission.
   */
  public function content(WebformSubmission $webform_submission) {
    if (!is_null($webform_submission)) {
      $webformSubmission = \Drupal::entityTypeManager()->getStorage('webform_submission')->load($webform_submission->id());
      $webformID = $webformSubmission->get('webform_id')->getValue()[0]['target_id'];
      $webform = \Drupal::entityTypeManager()->getStorage('webform')->load($webformID);
      $tpa = $webform->getThirdPartySetting('erpw_webform', 'webform_service_type_map');
      $activeDomain = \Drupal::service('domain.negotiator')->getActiveDomain()->id();
      $stype = '';
      $form_data = $webform_submission->getData();
      $workflow_state = $form_data['erpw_workflow']['workflow_state'];
      $current_user = $this->currentUser;
      $user_role = $current_user->getRoles();
      if ($user_role[0] == 'authenticated') {
        $can_edit = $this->checkUserEditRights($workflow_state, $user_role[1]);
      }
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
        }
        else {
          $servicelabel = t('Not available');
        }
        $output[] = ['Service Type' => $servicelabel];
      }

      // Get the elements directly from the configuration object.
      $webform_config = $this->configFactory->get('webform.webform.' . $webformID);
      $elements = $webform_config->get('elements');

      // Ensure that $elements is an array before decoding from YAML.
      if (is_string($elements)) {
        $elements = Yaml::decode($elements);
      }
      $ordered_elements = [];
      // Get the element titles for reference of setting the order.
      $this->orderElements($elements, $ordered_elements);

      $fields = $webform_submission->getData();
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
                      $location = $location . $country . '.';
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
                  $country = $this->entityTypeManager()->getStorage('location')->load($lvalue)->getName();
                  $location = $location . $country . '.';
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
      elseif (!$can_edit) {
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
      // Sort the elements based on their order in the webform.
      usort($output, function ($a, $b) use ($ordered_elements) {
        $key_a = array_search(key($a), $ordered_elements);
        $key_b = array_search(key($b), $ordered_elements);

        return $key_a - $key_b;
      });
      foreach ($output as $item) {
        foreach ($item as $key => $value) {
          $markup .= '<div class="pair-container"><span class="label">' . Markup::create($key) . ':</span>';
          if ($key == 'Opening Times' && is_array($value)) {
            $markup .= '<span class="value">' . Markup::create(implode("", $value)) . '</span>';
          }
          elseif (is_array($value)) {
            $markup .= '<span class="value">' . Markup::create(implode(", ", $value)) . '</span>';
          }
          else {
            $markup .= '<span  class="value">' . Markup::create($value) . '</span>';
          }
          $markup .= '</div>';
        }
      }

      // @todo Cache computed value.
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

  /**
   * Generate key value pair of elements in the webform submission which are in edit or delete
   * workflow.
   */
  public function content_approved(WebformSubmission $webform_submission) {
    if (!is_null($webform_submission)) {
      $webformSubmission = \Drupal::entityTypeManager()->getStorage('webform_submission')->load($webform_submission->id());
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
        $servicelabel = $servicetype->get('title')->getValue()[0]['value'];
        $output[] = ['Service Type' => $servicelabel];
      }

      // Check if the service is in Edit workflow.
      $node = erpw_webform_load_wsa_node_by_sid($webform_submission->id());
      if ($node) {
        $fields = json_decode($node->field_submission_data->value, TRUE);
      }
      else {
        $fields = $webform_submission->getData();
      }

      // Get the elements directly from the configuration object.
      $webform_config = $this->configFactory->get('webform.webform.' . $webformID);
      $elements = $webform_config->get('elements');

      // Ensure that $elements is an array before decoding from YAML.
      if (is_string($elements)) {
        $elements = Yaml::decode($elements);
      }
      $ordered_elements = [];
      // Get the element titles for reference of setting the order.
      $this->orderElements($elements, $ordered_elements);

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
                      $location = $location . $country . '.';
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
                  $country = $this->entityTypeManager()->getStorage('location')->load($lvalue)->getName();
                  $location = $location . $country . '.';
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
      // Service Rating Link.
      $service_type_id = $servicetype->get('nid')->getValue()[0]['value'];
      $rating_webform = 'webform_service_rating_' . $service_type_id;
      $feedback_webform = \Drupal::entityTypeManager()
        ->getStorage('webform')
        ->load($rating_webform);

      if ($feedback_webform && $feedback_webform->isOpen() && $this->currentUser->isAuthenticated()) {
        // Get the URL for the loaded webform.
        $routeName = 'erpw_webform.webform.feedback_form';

        // Define the route parameters as an associative array.
        $routeParameters = [
          'webform' => $rating_webform,
          'servicesid' => $webform_submission->id(),
        ];

        // Create a URL object based on the route name and parameters.
        $url = Url::fromRoute($routeName, $routeParameters);

        // Create a Link object with a link text and the URL.
        $service_rating_text = t('Give Feedback');
        $service_rating_url = $url->toString();

        $output[]['Service Rating Link'] = [
          '#markup' => '<a class="service-feedback-form" href="' . $service_rating_url . '">' . $service_rating_text . '</a>',
        ];
      }

      // Edit URL.
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
        if ($node) {
          $markup = '
          <div class="service-provider-details">
            <div class="service-detail-heading">
              <h3>' . t('Service Details') . '</h3>
              <div class="edit-delete-links">
                <span class="edit-link">
                  <p>' . t('This service is currently going through the review process. Hence direct edits are not allowed.') . '</p>
                </span>
              </div>
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
      }

      // Sort the elements based on their order in the webform.
      usort($output, function ($a, $b) use ($ordered_elements) {
        // Ensure 'Service Rating Link' is always placed at the end.
        if (key($a) === 'Service Rating Link') {
          return 1;
        }
        elseif (key($b) === 'Service Rating Link') {
          return -1;
        }

        $key_a = array_search(key($a), $ordered_elements);
        $key_b = array_search(key($b), $ordered_elements);

        return $key_a - $key_b;
      });

      foreach ($output as $item) {
        foreach ($item as $key => $value) {
          if ($key == 'Service Rating Link') {
            $markup .= '<div class="pair-container"><span class="service-rating-label">' . Markup::create($key) . ':</span>';
          }
          else {
            $markup .= '<div class="pair-container"><span class="label">' . Markup::create($key) . ':</span>';
          }

          if ($key == 'Opening Times' && is_array($value)) {
            $markup .= '<span class="value">' . Markup::create(implode("", $value)) . '</span>';
          }
          elseif (is_array($value)) {
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

  /**
   * Provides a reference of the order of the elements by extracting titles
   * from the given nested elements array.
   *
   * @param array $elements
   *   The array of nested elements to be processed.
   * @param array $filtered_elements
   *   (Reference) An array to store the extracted titles of the elements.
   */
  private function orderElements(array $elements, array &$filtered_elements) {
    foreach ($elements as $key => $value) {
      // Check if the current element has a '#type' key and is not a 'details' type.
      if (isset($value['#type']) && $value['#type'] !== 'details') {
        // Extract the title if it exists, otherwise use an empty string.
        $title = $value['#title'] ?? '';

        // Add the title to the filtered elements array.
        $filtered_elements[] = $title;
      }

      // If the current element has nested elements, recursively call the function.
      if (is_array($value) && !empty($value)) {
        $this->orderElements($value, $filtered_elements);
      }
    }
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

  /**
   * Checks whether the current user can make edits to a given service.
   *
   * @param string $workflow_state
   *   The current workflow state.
   * @param string $user_role
   *   The current user's role.
   *
   * @return bool
   *   TRUE if the user can edit, FALSE otherwise.
   */
  public function checkUserEditRights(string $workflow_state, string $user_role): bool {
    $can_edit = FALSE;

    $focal_point_roles = ['service_provider_focal_point', 'super_admin', 'administrator'];
    $gbv_coordination_roles = ['country_admin', 'interagency_gbv_coordinator', 'super_admin', 'administrator'];

    switch ($workflow_state) {
      case 'in_review_with_focal_point':
      case 'edits_in_review_with_focal_point':
      case 'deletion_in_review_with_focal_point':
        if (in_array($user_role, $focal_point_roles)) {
          $can_edit = TRUE;
        }
        break;

      case 'in_review':
      case 'edits_in_review_with_gbv_coordination':
      case 'deletion_in_review_with_gbv_coordination':
        if (in_array($user_role, $gbv_coordination_roles)) {
          $can_edit = TRUE;
        }
        break;

      case 'draft':
      case 'approve':
      case 'reject':
        $can_edit = TRUE;
        break;

      default:
        $can_edit = FALSE;
    }

    return $can_edit;
  }

}
