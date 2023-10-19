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
                <div class="feedback-text-block">
                    <div class="feedback-text-header">
                        <p>' . t('Feedback Remainder') . '</p>
                    </div>
                    <div class="feedback-text">
                        <p>' . t('Help Service Providers to improvise their Service Provision information with your valuable feedback. ') . '</p>
                    </div>
                </div>
                <a href="/share-feedback">' . t('Share feedback') . '</a>
            </div>',
        ];
    }
}
