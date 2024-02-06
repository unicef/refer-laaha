<?php

namespace Drupal\erpw_multisite\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Controller to delete content of other domains from site after Multisite.
 */
class DeleteContentController extends ControllerBase {

  /**
   * Function to delete webforms, submissions and nodes of other domains.
   */
  public function deleteContentPage() {
    $content_types = ['referral_path_way', 'service_provider', 'organisation', 'service_type'];

    $url = Url::fromRoute('erpw_multisite.delete_webforms_and_submissions');
    $link = Link::fromTextAndUrl($this->t('Delete Webform Submissions & Webforms'), $url);
    $content = [
      '#markup' => $link->toString() . '<br>',
    ];

    foreach ($content_types as $content_type) {
      $url = Url::fromRoute('erpw_multisite.delete_nodes', ['content_type' => $content_type]);
      $link = Link::fromTextAndUrl($this->t('Delete Nodes for content type: @type', ['@type' => $content_type]), $url);

      $content['nodes'][] = [
        '#markup' => $link->toString() . '<br>',
      ];
    }

    return $content;
  }

}
