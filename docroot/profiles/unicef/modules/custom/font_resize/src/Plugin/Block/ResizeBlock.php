<?php

namespace Drupal\font_resize\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'ResizeBlock' block.
 *
 * @Block(
 *  id = "resize_block",
 *  admin_label = @Translation("Resize block"),
 * )
 */
class ResizeBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $markup = '<div class="box">';
    $markup .= '<a class="font_resize-button" id="font_resize-minus" aria-label="" href="#">A-</a> ';
    $markup .= '<a class="font_resize-button" id="font_resize-plus" aria-label="" href="#">A+</a> ';
    $markup .= '</div>';

    return [
      '#type' => 'markup',
      '#attached' => [
        'library' => ['font_resize/font_resize', 'font_resize/font_resize_vss'],
      ],
      '#markup' => $markup,
    ];
  }

}
