<?php

namespace Drupal\erpw_webform\Plugin\views\filter;

use Drupal\node\Entity\Node;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;

/**
 * Custom filter for the webform organisation field.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("webform_submission_organisation_filter")
 */
class WebformSubmissionOrganisationFilter extends ManyToOne {

  /**
   * The current display.
   *
   * @var string
   *   The current display of the view.
   */
  protected $currentDisplay;

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->valueTitle = t('Filter by organisation');
    $this->definition['options callback'] = [$this, 'generateOptions'];
    $this->currentDisplay = $view->current_display;
  }

  /**
   * Helper function that generates the options.
   *
   * @return array
   *   An array of states and their ids.
   */
  public function generateOptions() {
    $activeDomain = \Drupal::service('domain.negotiator')->getActiveDomain()->id();
    $nids = \Drupal::entityQuery('node')->condition('type', 'organisation')->execute();
    $organizations = Node::loadMultiple($nids);

    // Create an array to store the organization name and machine name key-value pairs.
    $organizationNames = [];

    foreach ($organizations as $organization) {
      $domains = $organization->get('field_domain_access')->getValue();
      foreach ($domains as $domain) {
        if ($domain['target_id'] == $activeDomain) {
          $organizationName = $organization->label();
          $organizationMachineName = $organization->id();
          // Store the name and machine name as key-value pairs.
          $organizationNames[$organizationMachineName] = $organizationName;
        }
      }
    }
    asort($organizationNames);
    $states = $organizationNames;
    return $states;
  }

  /**
   * Helper function that builds the query.
   */
  public function query() {
    if (!empty($this->value)) {
      $configuration = [
        'table' => 'webform_submission_data',
        'field' => 'sid',
        'left_table' => 'webform_submission',
        'left_field' => 'sid',
        'operator' => '=',
      ];
      $join = Views::pluginManager('join')->createInstance('standard', $configuration);
      $this->query->addRelationship('webform_submission_data', $join, 'webform_submission_data');
      $this->query->addWhere('AND', 'webform_submission_data.value', $this->value, 'IN');
    }
  }

}
