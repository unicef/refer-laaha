<?php

namespace Drupal\erpw_webform\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a custom feedback block.
 *
 * @Block(
 *   id = "share_feedback_block",
 *   admin_label = @Translation("Share Feedback Block"),
 * )
 */
class ShareFeedbackBlock extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build() {
        return [
            '#markup' => '<div class="share-feedback-block">
                <a href="/share-feedback">' . t('Share feedback') . '</a>
            </div>',
        ];
    }
}
