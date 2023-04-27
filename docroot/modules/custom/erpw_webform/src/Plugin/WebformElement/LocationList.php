<?php

namespace Drupal\erpw_webform\Plugin\WebformElement;

use Drupal\erpw_location\Entity\LocationEntity;
use Drupal\taxonomy\Entity\Term;
use Drupal\webform\Plugin\WebformElement\WebformCompositeBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Provides a 'webform_example_composite' element.
 *
 * @WebformElement(
 *   id = "location_list_element",
 *   label = @Translation("Location List"),
 *   description = @Translation("Provides a webform element example."),
 *   category = @Translation("eRPW"),
 *   multiline = TRUE,
 *   composite = TRUE,
 *   states_wrapper = TRUE,
 * )
 *
 * @see \Drupal\webform_example_composite\Element\WebformExampleComposite
 * @see \Drupal\webform\Plugin\WebformElement\WebformCompositeBase
 * @see \Drupal\webform\Plugin\WebformElementBase
 * @see \Drupal\webform\Plugin\WebformElementInterface
 * @see \Drupal\webform\Annotation\WebformElement
 */
class LocationList extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  protected function formatHtmlItemValue(array $element, WebformSubmissionInterface $webform_submission, array $options = []) {
    return $this->formatTextItemValue($element, $webform_submission, $options);
  }

  /**
   * {@inheritdoc}
   */
  protected function formatTextItemValue(array $element, WebformSubmissionInterface $webform_submission, array $options = []) {
    $value = $this->getValue($element, $webform_submission, $options);
    $value['level_1'] = Term::load($value['level_1'])?->label();
    $value['level_2'] = Term::load($value['level_2'])?->label();
    $value['level_3'] = Term::load($value['level_3'])?->label();
    $value['level_4'] = Term::load($value['level_4'])?->label();
    $value['location_options'] = LocationEntity::load($value['location_options'])?->label();
    $lines = [];
    $lines[] = ($value['level_4'] ? $value['level_4'] : '') .
      ($value['level_3'] ? ', ' . $value['level_3'] : '') .
      ($value['level_2'] ? ', ' . $value['level_2'] : '') .
      ($value['level_1'] ? ', ' . $value['level_1'] : '') .
      ($value['location_options'] ? ', ' . $value['location_options'] : '');
    return $lines;
  }

}
