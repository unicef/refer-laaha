<?php

namespace Drupal\erpw_custom\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Symfony\Component\HttpFoundation\Request;
use Drupal\webform\Entity\Webform;

class DeleteContentController extends ControllerBase {
  public function deleteContentPage() {
    $content_types = ['referral_path_way', 'service_provider', 'organisation', 'service_type'];
  
    $url = Url::fromRoute('erpw_custom.delete_webforms_and_submissions');
    $link = Link::fromTextAndUrl($this->t('Delete Webform Submissions & Webforms'), $url);
    $content = [
      '#markup' => $link->toString() . '<br>',
    ];
  
    foreach ($content_types as $content_type) {
      $url = Url::fromRoute('erpw_custom.delete_nodes', ['content_type' => $content_type]);
      $link = Link::fromTextAndUrl($this->t('Delete Nodes for content type: @type', ['@type' => $content_type]), $url);
  
      $content['nodes'][] = [
        '#markup' => $link->toString() . '<br>',
      ];
    }
  
    return $content;
  }
  

  public function deleteNodes(Request $request, $content_type) {
    // Get the content type and execute your custom function.
    $nids = $this->findNidsOfOtherDomains($content_type);

    if (!empty($nids)) {
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $nodes = $node_storage->loadMultiple($nids);
      $count = count($nodes);

      foreach ($nodes as $node) {
        $node->delete();
      }

      $message = '<div>' . $this->t('@count Nodes deleted successfully for content type: @type', [
        '@count' => $count,
        '@type' => $content_type]) . '<br>' .
        $this->t('Following are the node ids which were deleted: @nids', ['@nids' => $nids]) . '</div><br>';
    }
    else {
      $message = $this->t('No nodes to delete for content type: @type', ['@type' => $content_type]);
    }

    $render_array = [
      '#markup' => $message,
    ];
  
    return $render_array;
  
  }


  /**
   * Finds node IDs of a given content type in domains other than the current one.
   *
   * @param string $bundle_name
   *   The machine name of the content type (bundle).
   *
   * @return array|null
   *   An array of node IDs or null if current url doesn't have a subdomain.
   */
  private function findNidsOfOtherDomains($bundle_name) {
    // Get the current domain from the request URL.
    $domain_current_url = explode(".", \Drupal::service('request_stack')->getCurrentRequest()->server->get('SERVER_NAME'));

    // List of domains without subdomains where the condition should be applied.
    $domains_without_subdomain = ['refer-laaha', 'stage', 'erefer'];

    // Check if the current domain is in the list of domains without subdomains.
    if (!in_array($domain_current_url[0], $domains_without_subdomain)) {
      // Get the active domain ID.
      $current_domain = \Drupal::service('domain.negotiator')->getActiveDomain()->id();

      // Query nodes in the specified bundle for the current domain.
      $domain_query = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
        ->condition('type', $bundle_name)
        ->condition('field_domain_access', $current_domain)
        ->accessCheck(TRUE);
      $domain_nids = $domain_query->execute();

      // Query all nodes in the specified bundle.
      $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
        ->condition('type', $bundle_name)
        ->accessCheck(TRUE);
      $nids = $query->execute();

      // Calculate the set difference to find nodes in other domains.
      $final_nids = array_diff($nids, $domain_nids);

      return $final_nids;
    } 
    else {
      return [];
    }
  }

  public function deleteWebformsAndSubmissions() {
    // Get a list of webforms matching the pattern.
    $current_domain = \Drupal::service('domain.negotiator')->getActiveDomain()->id();
    $pattern = '/^(.*?)_erefer_org$/';

    if (preg_match($pattern, $current_domain, $matches)) {
      $subdomain = $matches[1];
      if ($subdomain == 'zm') {
        $subdomain = 'zw';
      }
    }

    $query = \Drupal::database()->select('webform', 'w')
      ->fields('w', ['webform_id'])
      ->condition('webform_id', $subdomain . '%', 'NOT LIKE')
      ->condition('webform_id', 'webform_service_rating_%', 'NOT LIKE')
      ->condition('webform_id', 'template_%', 'NOT LIKE')
      ->execute();

    $webform_ids = $query->fetchCol();
  
    // Delete submissions for each webform.
    foreach ($webform_ids as $index => $webform_id) {
      $webform = Webform::load($webform_id);
      if ($webform) {
        // Get the submission storage for the webform.
        $submission_storage = \Drupal::entityTypeManager()->getStorage('webform_submission');
        
        // Get submissions for the webform.
        $submission_ids = $submission_storage->getQuery()
          ->condition('webform_id', $webform_id)
          ->execute();

        $submission_count = count($submission_ids);
  
        // Delete each submission.
        foreach ($submission_ids as $submission_id) {
          $submission_storage->delete([$submission_id]);
        }
        $message = '<div>' . $this->t('@count Submissions deleted successfully for webform id: @webform', [
          '@count' => $submission_count,
          '@type' => $webform_id]) . '</div><br>';
        // Delete the webform.
        $webform->delete();
        $message['webform'][] = '<div>' . $this->t('Webform @webform_id deleted successfully', [
          '@webform_id' => $webform_id]) . '</div><br>';

        $render_array = [
          '#markup' => $message,
        ];

        return $render_array;

      }
    }
  }

}
