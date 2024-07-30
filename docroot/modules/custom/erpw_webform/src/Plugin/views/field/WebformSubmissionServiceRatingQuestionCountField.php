<?php

namespace Drupal\erpw_webform\Plugin\views\field;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom Views field plugin.
 *
 * @ViewsField("webform_submission_service_rating_question_count_field")
 */
class WebformSubmissionServiceRatingQuestionCountField extends FieldPluginBase {

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
    if ($values->_entity->get('webform_id')->getValue() != NULL && str_contains($values->_entity->get('webform_id')->getValue()[0]["target_id"], "webform_service_rating_")) {
      $webform_id = $values->_entity->get('webform_id')->getValue()[0]["target_id"];
      $webform = $this->entityTypeManager->getStorage('webform')->load($webform_id);
      $questions_count = $this->getRatingQuestionCount($webform->getElementsDecoded());
      if (isset($questions_count)) {
        $output = [
          '#markup' => '<div class="service-rating-feedback-question-count">' . t('Number of questions : ') . '<span>' . $questions_count . '</span></div>',
        ];
      }
    }
    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function getRatingQuestionCount(array $webform_elements) {
    $count = 0;
    foreach ($webform_elements as $element_key => $element) {
      if ($element['#type'] == 'radios') {
        $count++;
      }
    }
    return $count;
  }

}
