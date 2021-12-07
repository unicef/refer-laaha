<?php

namespace Drupal\vss_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'RecommendedRelatedContentBock' block.
 *
 * @Block(
 *  id = "recommended_related_content_bock",
 *  admin_label = @Translation("Recommended related content bock"),
 * )
 */
class RecommendedRelatedContentBock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#theme'] = 'recommended_related_content_block_wrapper';
    return $build;
  }

}
