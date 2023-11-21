<?php

namespace Drupal\erpw_multisite\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller to delete nodes of other domains after Multisite.
 */
class DeleteNodesController extends ControllerBase {

  /**
   * Function to delete referral pathway, services, organisation and service type nodes.
   */
  public function deleteNodes() {
    $content_type = \Drupal::service('current_route_match')->getParameter('content_type');
    // Get the content type and execute corresponding function.
    if ($content_type == 'referral_path_way' || $content_type == 'service_provider') {
      $nids = $this->findNidsOfRpwOldServices($content_type);
    }
    elseif ($content_type == 'organisation' || $content_type == 'service_type') {
      $nids = $this->findNidsOfOrganisationServiceType($content_type);
    }

    $message = '';
    if (!empty($nids)) {
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $nodes = $node_storage->loadMultiple($nids);
      $count = count($nodes);

      foreach ($nodes as $node) {
        $node->delete();
      }

      $message = '<div>' . $count . ' Nodes deleted successfully for content type: ' . $content_type;
    }
    else {
      $message = 'No nodes to delete for content type: ' . $content_type;
    }

    if ($message == '') {
      $message = 'No nodes left to delete for ' . $content_type;
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
  private function findNidsOfOrganisationServiceType($bundle_name) {
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

  /**
   * Retrieves node IDs of a specified bundle for locations not associated with the current domain.
   *
   * @param string $bundle_name
   *   The name of the node bundle for which to find node IDs.
   *
   * @return array
   *   An array of node IDs for the specified bundle and locations not associated with the current domain.
   */
  private function findNidsOfRpwOldServices($bundle_name) {
    // Get the current domain from the request URL.
    $domain_current_url = explode(".", \Drupal::service('request_stack')->getCurrentRequest()->server->get('SERVER_NAME'));

    // List of domains without subdomains where the condition should be applied.
    $domains_without_subdomain = ['refer-laaha', 'stage', 'erefer'];

    // Check if the current domain is in the list of domains without subdomains.
    if (!in_array($domain_current_url[0], $domains_without_subdomain)) {
      // Get the active domain ID.
      $current_domain = \Drupal::service('domain.negotiator')->getActiveDomain()->id();
      $domain_tid = $this->tidByDomain($current_domain);

      // Get the child location term IDs for the current domain.
      $ptids = \Drupal::service('erpw_location.location_services')->getChildrenByParent($domain_tid);

      // Prepend the domain term ID to the list of child location term IDs.
      $ptids = [$domain_tid, ...$ptids];

      // Query nodes in the specified bundle for the locations of the current domain.
      $domain_query = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
        ->condition('type', $bundle_name)
        ->condition('field_location', $ptids, 'IN')
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
      // Return an empty array if the current domain is in the list of domains without subdomains.
      return [];
    }
  }

  /**
   * Retrieves the term ID (tid) based on the given domain name.
   *
   * @param string $domain_name
   *   The domain name for which to retrieve the corresponding term ID.
   *
   * @return int|null
   *   The term ID associated with the provided domain name, or null if no match is found.
   */
  private function tidByDomain(string $domain_name) {
    $tid = NULL;

    switch ($domain_name) {
      case 'zm_erefer_org':
        $tid = 1;
        break;

      case 'bn_erefer_org':
        $tid = 10401;
        break;

      case 'sl_erefer_org':
        $tid = 16821;
        break;

      case 'txb_erefer_org':
        $tid = 10926;
        break;

      default:
        // Return null if the provided domain name does not match any known cases.
        $tid = NULL;
    }

    return $tid;
  }

}
