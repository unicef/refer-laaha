<?php

namespace Drupal\erpw_webform\Plugin\views\filter;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Drupal\views\Plugin\ViewsHandlerManager;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * Views Handler Plugin Manager.
   *
   * @var \Drupal\views\Plugin\ViewsHandlerManager
   */
  protected $joinHandler;

  /**
   * Constructs a new TransactionId instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\views\Plugin\ViewsHandlerManager $join_handler
   *   The join handler.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ViewsHandlerManager $join_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->joinHandler = $join_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.views.join')
    );
  }

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
    // @todo Remove hard-coded way of loading workflow states.
    $states = [
      'approve' => 'Approved',
      'draft' => 'Draft',
      'in_review_with_focal_point' => 'In Review with Focal Point',
      'in_review' => 'In Review with GBV Coordination',
      'edits_in_review_with_focal_point' => 'Edits In Review with Focal Point',
      'edits_in_review_with_gbv_coordination' => 'Edits In Review with GBV Coordination',
      'deletion_in_review_with_focal_point' => 'Deletion In Review with Focal Point',
      'deletion_in_review_with_gbv_coordination' => 'Deletion In Review with GBV Coordination',
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
      $join = $this->joinHandler->createInstance('standard', $configuration);
      $this->query->addRelationship('webform_submission_data_workflow1', $join, 'webform_submission_data');
      $this->query->addWhere($this->options['group'], 'webform_submission_data_workflow1.value', $this->value[0], '=');
    }
  }

}
