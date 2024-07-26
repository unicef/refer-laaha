<?php

namespace Drupal\erpw_webform\Controller;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\domain\DomainNegotiatorInterface;
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
   * The Domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a new ServiceWebforms object.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, AccountInterface $currentUser, ConfigFactory $config_factory, DomainNegotiatorInterface $domain_negotiator, DateFormatterInterface $date_formatter) {
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
    $this->configFactory = $config_factory;
    $this->domainNegotiator = $domain_negotiator;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
    $container->get('entity_type.manager'),
    $container->get('current_user'),
    $container->get('config.factory'),
    $container->get('domain.negotiator'),
    $container->get('date.formatter')
    );
  }

  /**
   * Generate key value pair of elements in the webform submission.
   */
  public function content(WebformSubmission $webform_submission) {
    $cid = 'service_submissions_view' . $webform_submission->id();
    $markup = '';
    $cache_tags = ['webform_submission:' . $webform_submission->id()];
    if ($cache = $this->cache()->get($cid)) {
      $markup = $cache->data;
      return [
        '#type' => 'markup',
        '#markup' => $markup,
      ];
    }
    else {
      if (!is_null($webform_submission)) {
        $webformSubmission = $this->entityTypeManager()->getStorage('webform_submission')->load($webform_submission->id());
        $webformID = $webformSubmission->get('webform_id')->getValue()[0]['target_id'];
        $webform = $this->entityTypeManager()->getStorage('webform')->load($webformID);
        $tpa = $webform->getThirdPartySetting('erpw_webform', 'webform_service_type_map');
        $activeDomain = $this->domainNegotiator->getActiveDomain()->id();
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
          $servicetype = $this->entityTypeManager()->getStorage('node')->load(intval($stype));
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
                  elseif (isset($element['#type']) && $element['#type'] == 'checkbox') {
                    if ($content != NULL) {
                      if ($content == 1) {
                        $output[] = [$element['#title'] => t('Yes')];
                      }
                      else {
                        $output[] = [$element['#title'] => t('No')];
                      }
                    }
                  }
                  elseif (isset($element['#type']) && $element['#type'] == 'checkboxes') {
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
                  elseif (isset($element['#type']) && $element['#type'] == 'select') {
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
                  elseif (isset($element['#type']) && $element['#type'] == 'webform_entity_select') {
                    if ($element['#title'] = 'Organisation') {
                      if (!empty($content)) {
                        $org_node = $this->entityTypeManager->getStorage('node')->load($content);
                        if ($org_node) {
                          $orgLabel = $org_node->get('title')->getValue()[0]['value'];
                          $output[] = [$element['#title'] => $orgLabel];
                        }
                        else {
                          $output[] = [$element['#title'] => $this->t('Organisation not available or deleted')];
                        }
                      }
                    }
                  }
                  elseif (isset($element['#type']) && $element['#type'] == 'webform_mapping') {
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
              elseif (isset($element['#type']) && $element['#type'] == 'checkbox') {
                if ($content != NULL) {
                  if ($content == 1) {
                    $output[] = [$element['#title'] => t('Yes')];
                  }
                  else {
                    $output[] = [$element['#title'] => t('No')];
                  }
                }
              }
              elseif (isset($element['#type']) && $element['#type'] == 'checkboxes') {
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
              elseif (isset($element['#type']) && $element['#type'] == 'select') {
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
              elseif (isset($element['#type']) && $element['#type'] == 'webform_entity_select') {
                if ($element['#title'] = 'Organisation') {
                  if (!empty($content)) {
                    $org_node = $this->entityTypeManager->getStorage('node')->load($content);
                    if ($org_node) {
                      $orgLabel = $org_node->get('title')->getValue()[0]['value'];
                      $output[] = [$element['#title'] => $orgLabel];
                    }
                    else {
                      $output[] = [$element['#title'] => $this->t('Organisation not available or deleted')];
                    }
                  }
                }
              }
              elseif (isset($element['#type']) && $element['#type'] == 'webform_mapping') {
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

        $last_updated_timestamp = $webform_submission->getChangedTime();
        $formatted_last_updated = $this->dateFormatter->format($last_updated_timestamp, 'custom', 'd/m/Y H:i:s');
        $output[] = ['Last updated time' => $formatted_last_updated];

        $edit_url = Url::fromRoute('entity.webform_submission.edit_form', [
          'webform' => $webform_submission->getWebform()->id(),
          'webform_submission' => $webform_submission->id(),
        ])->toString();
        $user_role = $this->currentUser->getRoles();

        if ($this->currentUser->isAnonymous() || in_array('txb_service_viewer', $user_role)) {
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
          // Ensure 'Last updated time' is always placed at the end.
          if (key($a) == 'Last updated time') {
            return 1;
          }
          elseif (key($b) == 'Last updated time') {
            return -1;
          }

          // Default sorting based on $ordered_elements.
          $key_a = array_search(key($a), $ordered_elements);
          $key_b = array_search(key($b), $ordered_elements);

          return $key_a - $key_b;
        });

        foreach ($output as $item) {
          foreach ($item as $key => $value) {
            if ($key == 'Last updated time') {
              $markup .= '<div class="pair-container"><span class="label">' . Markup::create(t('Last updated time')) . ':</span>';
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

        // Invalidate cache tag when a new submission is created or edited.
        $this->cache()->set($cid, $markup, Cache::PERMANENT, $cache_tags);

        // @todo Cache computed value - done
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

  /**
   * Generate key value pair of elements in the webform submission
   * which are in edit or delete workflow.
   */
  public function contentApproved(WebformSubmission $webform_submission) {
    $roles = $this->currentUser->getRoles();
    $cid = 'service_submissions_view' . $webform_submission->id() . implode("-", $roles);
    $markup = '';
    $cache_tags = ['webform_submission:' . $webform_submission->id(),
      'config:webform_list', 'webform_list',
    ];
    if ($cache = $this->cache()->get($cid)) {
      $markup = $cache->data;
      return [
        '#type' => 'markup',
        '#markup' => $markup,
      ];
    }
    else {
      if (!is_null($webform_submission)) {
        $webformSubmission = $this->entityTypeManager()->getStorage('webform_submission')->load($webform_submission->id());
        $webformID = $webformSubmission->get('webform_id')->getValue()[0]['target_id'];
        $webform = $this->entityTypeManager()->getStorage('webform')->load($webformID);
        $tpa = $webform->getThirdPartySetting('erpw_webform', 'webform_service_type_map');
        $activeDomain = $this->domainNegotiator->getActiveDomain()->id();
        $stype = '';
        foreach ($tpa as $domain => $servicetype) {
          if ($domain == $activeDomain) {
            $stype = $servicetype[0];
          }
        }
        $output = [];
        if (!is_null($stype) && !empty($stype)) {
          $servicetype = $this->entityTypeManager()->getStorage('node')->load(intval($stype));
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
                  elseif (isset($element['#type']) && $element['#type'] == 'checkbox') {
                    if ($content != NULL) {
                      if ($content == 1) {
                        $output[] = [$element['#title'] => t('Yes')];
                      }
                      else {
                        $output[] = [$element['#title'] => t('No')];
                      }
                    }
                  }
                  elseif (isset($element['#type']) && $element['#type'] == 'checkboxes') {
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
                  elseif (isset($element['#type']) && $element['#type'] == 'select') {
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
                  elseif (isset($element['#type']) && $element['#type'] == 'webform_entity_select') {
                    if ($element['#title'] = 'Organisation') {
                      if (!empty($content)) {
                        $org_node = $this->entityTypeManager->getStorage('node')->load($content);
                        if ($org_node) {
                          $orgLabel = $org_node->get('title')->getValue()[0]['value'];
                          $output[] = [$element['#title'] => $orgLabel];
                        }
                        else {
                          $output[] = [$element['#title'] => $this->t('Organisation not available or deleted')];
                        }
                      }
                    }
                  }
                  elseif (isset($element['#type']) && $element['#type'] == 'webform_mapping') {
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
              elseif (isset($element['#type']) && $element['#type'] == 'checkbox') {
                if ($content != NULL) {
                  if ($content == 1) {
                    $output[] = [$element['#title'] => t('Yes')];
                  }
                  else {
                    $output[] = [$element['#title'] => t('No')];
                  }
                }
              }
              elseif (isset($element['#type']) && $element['#type'] == 'checkboxes') {
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
              elseif (isset($element['#type']) && $element['#type'] == 'select') {
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
              elseif (isset($element['#type']) && $element['#type'] == 'webform_entity_select') {
                if ($element['#title'] = 'Organisation') {
                  if (!empty($content)) {
                    $org_node = $this->entityTypeManager->getStorage('node')->load($content);
                    if ($org_node) {
                      $orgLabel = $org_node->get('title')->getValue()[0]['value'];
                      $output[] = [$element['#title'] => $orgLabel];
                    }
                    else {
                      $output[] = [$element['#title'] => $this->t('Organisation not available or deleted')];
                    }
                  }
                }
              }
              elseif (isset($element['#type']) && $element['#type'] == 'webform_mapping') {
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
        $feedback_webform = $this->entityTypeManager()
          ->getStorage('webform')
          ->load($rating_webform);
        $user_role = $this->currentUser->getRoles();
        $user = $this->entityTypeManager()->getStorage('user')->load($this->currentUser->id());
        $user_organisation = $user->get('field_organisation')->getValue()[0]['target_id'];

        if ($feedback_webform && $feedback_webform->isOpen() && $this->currentUser->isAuthenticated()  && !in_array('txb_service_viewer', $user_role)) {
          if (in_array('service_provider_staff', $user_role) || in_array('service_provider_focal_point', $user_role)
          && $org_node->id() == $user_organisation) {
            $output[]['Service Rating Link'] = [
              '#markup' => '',
            ];
          }
          else {
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
        }

        $last_updated_timestamp = $webform_submission->getChangedTime();
        $formatted_last_updated = $this->dateFormatter->format($last_updated_timestamp, 'custom', 'd/m/Y H:i:s');
        $output[] = ['Last updated time' => $formatted_last_updated];

        // Edit URL.
        $edit_url = Url::fromRoute('entity.webform_submission.edit_form', [
          'webform' => $webform_submission->getWebform()->id(),
          'webform_submission' => $webform_submission->id(),
        ])->toString();
        $user_role = $this->currentUser->getRoles();

        if ($this->currentUser->isAnonymous() || in_array('txb_service_viewer', $user_role)) {
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
          if (key($a) == 'Service Rating Link') {
            return 1;
          }
          elseif (key($b) == 'Service Rating Link') {
            return -1;
          }

          // If 'Service Rating Link' exists, adjust the position of 'Last updated time'.
          if (array_key_exists('Service Rating Link', array_flip(array_keys([$a, $b])))) {
            // 'Last updated time' should be second last.
            if (key($a) == 'Last updated time') {
              return 1;
            }
          }
          // 'Last updated time' should be last.
          else {
            if (key($b) == 'Last updated time') {
              return -1;
            }
          }

          // Default sorting based on $ordered_elements.
          $key_a = array_search(key($a), $ordered_elements);
          $key_b = array_search(key($b), $ordered_elements);

          return $key_a - $key_b;
        });

        foreach ($output as $item) {
          foreach ($item as $key => $value) {
            if ($key == 'Service Rating Link') {
              $markup .= '<div class="pair-container"><span class="service-rating-label">' . Markup::create($key) . ':</span>';
            }
            elseif ($key == 'Last updated time') {
              $markup .= '<div class="pair-container"><span class="label">' . Markup::create(t('Last updated time')) . ':</span>';
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

        // Invalidate cache tag when a new submission is created or edited.
        $this->cache()->set($cid, $markup, Cache::PERMANENT, $cache_tags);

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

    $focal_point_roles = [
      'service_provider_focal_point',
      'super_admin',
      'administrator',
    ];
    $gbv_coordination_roles = [
      'country_admin',
      'interagency_gbv_coordinator',
      'super_admin',
      'administrator',
    ];

    switch ($workflow_state) {
      case 'in_review_with_focal_point':
      case 'edits_in_review_with_focal_point':
      case 'deletion_in_review_with_focal_point':
      case 'archive_in_review_with_focal_point':
      case 'restore_service_in_review_with_focal_point':
        if (in_array($user_role, $focal_point_roles)) {
          $can_edit = TRUE;
        }
        break;

      case 'in_review':
      case 'edits_in_review_with_gbv_coordination':
      case 'deletion_in_review_with_gbv_coordination':
      case 'archive_in_review_with_gbv_coordination':
      case 'restore_service_in_review_with_gbv_coordination':
        if (in_array($user_role, $gbv_coordination_roles)) {
          $can_edit = TRUE;
        }
        break;

      case 'draft':
      case 'approve':
      case 'reject':
      case 'archived':
      case 'rejected_archive_service_request':
        $can_edit = TRUE;
        break;

      default:
        $can_edit = FALSE;
    }

    return $can_edit;
  }

}
