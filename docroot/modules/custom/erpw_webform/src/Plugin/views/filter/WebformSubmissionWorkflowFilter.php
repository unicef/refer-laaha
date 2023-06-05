<?php

namespace Drupal\erpw_webform\Plugin\views\filter;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;

/**
 * Custom filter for the webform workflow field.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("webform_submission_workflow_filter")
 */
class WebformSubmissionWorkflowFilter extends ManyToOne {

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
    $this->valueTitle = t('Filter by workflow status');
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
    $states = [
      'approve' => 'Approved',
      'draft' => 'Draft',
      'in_review_with_gbvi_coordinator' => 'In Review with GBVi Coordinator',
      'in_review' => 'In Review with Country Admin',
      'reject' => 'Rejected',
    ];
    // You can add your custom code here to add custom labels for state transitions.
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
