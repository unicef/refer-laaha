<?php

namespace Drupal\erpw_webform\Plugin\views\field;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom Views field plugin.
 *
 * @ViewsField("webform_submission_organisation_field")
 */
class WebformSubmissionOrganisationField extends FieldPluginBase {

  /**
   * Entity Manager instance.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new WebformSubmissionLocationField instance.
   */
  public function __construct(array $configuration,
   $plugin_id,
    $plugin_definition,
  EntityTypeManagerInterface $entityTypeManagerInterface
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManagerInterface;
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
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    if (isset($values->_entity->getData()['organisation'])) {
      if (!is_null($this->entityTypeManager->getStorage('node')->load($values->_entity->getData()['organisation']))) {
        $output = $this->entityTypeManager->getStorage('node')->load($values->_entity->getData()['organisation'])->get('title')->getValue()[0]['value'];
      }
      else {
        $output = t('Not available.');
      }
    }
    else {
      $output = t('Not available.');
    }
    return $output;
  }

}
