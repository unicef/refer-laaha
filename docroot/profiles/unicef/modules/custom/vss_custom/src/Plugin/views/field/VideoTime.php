<?php

namespace Drupal\vss_custom\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler to flag the node type.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("field_video_time")
 */
class VideoTime extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $id = $values->field_sub_category_taxonomy_term_field_data_nid;
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $video_time = $this->getVideoTime($id, $langcode);
    return $video_time;
  }

  /**
   * {@inheritdoc}
   */
  public function getVideoTime($id, $langcode) {
    $res = '';
    if (!empty($id)) {
      $query = \Drupal::database()->select('node_field_data', 'n');
      $query->join('node__field_content', 'fc', 'fc.entity_id = n.nid');
      $query->join('paragraph__field_video_time', 'vt', 'vt.entity_id = fc.field_content_target_id');
      $query->condition('vt.bundle', 'video');
      $query->condition('n.nid', $id, '=');
      $query->condition('n.langcode', $langcode, '=');
      $query->condition('n.type', 'video');
      $query->fields('vt', ['field_video_time_value']);
      $res = $query->execute()->fetchField();
    }
    return $res;
  }

}
