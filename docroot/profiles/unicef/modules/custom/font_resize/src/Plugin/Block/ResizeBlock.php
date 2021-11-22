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

    $label_plus = $this->t('Increase Font Size');
    $label_minus = $this->t('Reduce Font Size');
    $label_original = $this->t('Reset Font Size');

    $markup = '<div class="box">';
    $markup .= '<a class="font_resize-button" id="font_resize-minus" aria-label="" href="#">A-</a> ';
    $markup .= '<a class="font_resize-button" id="font_resize-default" aria-label="" href="#">A</a> ';
    $markup .= '<a class="font_resize-button" id="font_resize-plus" aria-label="" href="#">A+</a> ';
    $markup .= '</div>';

    return array(
      '#type' => 'markup',
      '#attached' => array(
        'library' => array('font_resize/font_resize', 'font_resize/font_resize_example'),
      ),
      '#markup' => $markup,
    );
  }

}
