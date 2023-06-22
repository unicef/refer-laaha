<?php

namespace Drupal\erpw_webform\Plugin\views\filter;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Drupal\views\Plugin\ViewsHandlerManager;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\views\Plugin\ViewsHandlerManager $join_handler
   *   The join handler.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ViewsHandlerManager $join_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.views.join')
    );
  }

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
    $nids = $this->entityTypeManager->getStorage('node')->getQuery()->condition('type', 'organisation')->execute();
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
    // @todo Remove this sort and sort in the previous loop.
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
      $join = $this->joinHandler->createInstance('standard', $configuration);
      $this->query->addRelationship('webform_submission_data_org1', $join, 'webform_submission_data');
      $this->query->addWhere($this->options['group'], 'webform_submission_data_org1.value', $this->value, '=');
    }
  }

}
