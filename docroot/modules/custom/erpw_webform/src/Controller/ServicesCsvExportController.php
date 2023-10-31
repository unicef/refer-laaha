<?php

namespace Drupal\erpw_webform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for custom CSV export.
 */
class ServicesCsvExportController extends ControllerBase {

  /**
   * Add a private property to store the EntityTypeManager.
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
   *
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   *
   */
  public function getChildTermId($ptid) {
    $tree = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree(
    'country',
    $ptid
    );
    $result = [];
    $result[$ptid] = $ptid;
    foreach ($tree as $term) {
      $result[$term->tid] = $term->tid;
    }
    return $result;
  }

  /**
   *
   */
  public function erpwQuery(QueryPluginBase &$query, mixed $column_value, string $alias_slug, string $op = '=') {
    $configuration = [
      'table' => 'webform_submission_data',
      'field' => 'sid',
      'left_table' => 'webform_submission',
      'left_field' => 'sid',
      'operator' => '=',
    ];
    $join = Views::pluginManager('join')->createInstance('standard', $configuration);
    $query->addRelationship('webform_submission_data_' . $alias_slug, $join, 'webform_submission_data');
    $query->addWhere(1, "webform_submission_data_$alias_slug.value", $column_value, $op);
  }

  /**
   * Download CSV file containing entities and their fields/values.
   *
   * @param string $node
   *   The value of the contextual filter.
   *
   *   The CSV file download response.
   */
  public function downloadCsv($node) {
    // Fetch the entities from your Views listing using the contextual filter value.
    $view_id = 'service_based_service_providers_listing';

    // Get the View object.
    $view = Views::getView($view_id);
    $view->setOffset(0);
    $view->setItemsPerPage(0);
    $view->build();
    // Set the contextual filter values in the View object.
    $view->setArguments([$node]);
    // Access the QueryPluginBase object.
    $query = $view->query;
    $cookie_tid = \Drupal::service('erpw_location.location_cookie')->getCookieValue();
    $ptids = $this->getChildTermId($cookie_tid);
    $activeDomain = \Drupal::service('domain.negotiator')->getActiveDomain()->id();
    $current_user = User::load($this->currentUser->id());
    $roles = $current_user->getRoles();
    $user_org_id = $current_user->get('field_organisation')->getValue()[0]['target_id'];
    $servicelabel = '';

    // For Services with only domain as location, location entity id is set instead of tid.
    $location_entity = erpw_webform_getLocationEntityIdByTid($cookie_tid);
    if (!empty($location_entity)) {
      $ptids = array_merge([strval($location_entity)], $ptids);
    }

    if ($service_type = \Drupal::routeMatch()->getRawParameter('node')) {
      // Service type Join.
      $this->erpwQuery($query, $service_type, 'st');
    }
    // Domain Join.
    $this->erpwQuery($query, $activeDomain, 'domain');

    // Location Join.
    $this->erpwQuery($query, $ptids, 'location', 'IN');

    // Workflow join.
    $workflow_states = ['approve', 'edits_in_review_with_focal_point', 'edits_in_review_with_gbv_coordination'];
    $this->erpwQuery($query, $workflow_states, 'workflow', 'IN');
    // Execute the View to ensure the query is built.
    $view->execute();
    $csv_rows = [];
    $csvkeys = [];
    $csvvalues = [];
    // Generate key value pair.
    foreach ($view->result as $row) {
      $webform_submission = $row->_entity;
      if (!is_null($webform_submission)) {
        $webformSubmission = $this->entityTypeManager->getStorage('webform_submission')->load($webform_submission->id());
        $webformID = $webformSubmission->get('webform_id')->getValue()[0]['target_id'];
        $webform = $this->entityTypeManager->getStorage('webform')->load($webformID);
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
          $servicetype = $this->entityTypeManager->getStorage('node')->load(intval($stype));
          if ($servicetype instanceof Node) {
            $servicelabel = $servicetype->get('title')->getValue()[0]['value'];
          }
          else {
            $servicelabel = t('Not available');
          }
          $output[] = ['Service Type' => $servicelabel];
        }
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
                      $orgLabel = $this->entityTypeManager->getStorage('node')->load($content)->get('title')->getValue()[0]['value'];
                      $output[] = [$element['#title'] => $orgLabel];
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
                  if ($this->entityTypeManager->getStorage('node')->load($content) instanceof Node) {
                    $orgLabel = $this->entityTypeManager->getStorage('node')->load($content)->get('title')->getValue()[0]['value'];
                  }
                  else {
                    $orgLabel = t('Not available');
                  }
                  $output[] = [$element['#title'] => $orgLabel];
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
        // Sort the output alphabetically.
        usort($output, function ($a, $b) {
          $keyA = key($a);
          $keyB = key($b);
          return strcmp($keyA, $keyB);
        });
        foreach ($output as $item) {
          $row = [];
          foreach ($item as $key => $value) {
            // Add each key and value as a separate entry in the row array.
            if ($key == 'Name of focal point for referrals (first/last name)') {
              $key = 'Name of focal point for referrals';
            }
            $row[] = $key;
            if (gettype($value) == 'array') {
              $row[] = implode(',', $value);
            }
            else {
              $row[] = $value;
            }
          }
          // Add the row to the CSV rows array.
          $csv_rows[] = $row;
        }
      }
      // Add a custom separator line.
      $csv_rows[] = [];
      $csv_rows[] = ['----------------------'];
      $csv_rows[] = [];
    }

    // Create a temporary file to store the CSV data.
    $temp_file = tempnam(sys_get_temp_dir(), 'csv_export');

    // Open the temporary file for writing.
    $file_handle = fopen($temp_file, 'w');

    // Write the CSV headers (empty) to the file.
    fputcsv($file_handle, []);

    // Write the CSV rows to the file.
    foreach ($csv_rows as $row) {
      if (is_array($row)) {
        fputcsv($file_handle, $row);
      }
      else {
        // Custom separator line.
        fputcsv($file_handle, [$row]);
      }
    }

    // Close the file handle.
    fclose($file_handle);

    // Send the CSV file as a download.
    $response = new Response(file_get_contents($temp_file));
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $servicelabel . ' Services"');

    // Delete the temporary file after the download.
    unlink($temp_file);

    return $response;
  }

}
