<?php

namespace Drupal\erpw_ussd_api\Plugin\rest\resource;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Represents OrganisationResource records as resources.
 *
 * @RestResource (
 *   id = "ussd_organisation_api",
 *   label = @Translation("USSD Organisation Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/ussd-organisation"
 *   }
 * )
 */
class OrganisationResource extends ResourceBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing the record.
   */
  public function get() {
    $cid = 'ussd_organisation_api';
    $organisations_data = [];
    // Specify cache tags related to organisations.
    $cacheTags = ['node_list:organisation'];
    if ($cache = \Drupal::cache()
      ->get($cid)) {
      $organisations_data = $cache->data;
    }
    else {
      $node_storage = $this->entityTypeManager->getStorage('node');
      $organisation_query = $node_storage->getQuery();
      $organisation_query->condition('type', 'organisation');
      $organisation_query->condition('field_domain_access', 'sl_erefer_org');
      $organisation_query->accessCheck(FALSE);
      $organisation_nids = $organisation_query->execute();

      $organisations = $node_storage->loadMultiple($organisation_nids);
      $index = 0;

      foreach ($organisations as $organisation) {
        $organisations_data[$index] = [
          'id' => $organisation->id(),
          'name' => $organisation->label(),
        ];
        $index++;
      }
      \Drupal::cache()
        ->set($cid, $organisations, Cache::PERMANENT, $cacheTags);
    }
    $this->logger->notice('Exported organisation info to USSD.');
    return new ResourceResponse($organisations_data, 200);
  }

  /**
   * {@inheritdoc}
   */
  public function availableMethods() {
    return [
      'GET',
    ];
  }

}
