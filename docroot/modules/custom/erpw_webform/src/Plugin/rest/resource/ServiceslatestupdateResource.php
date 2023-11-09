<?php

namespace Drupal\erpw_webform\Plugin\rest\resource;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Represents servicesLatestUpdate records as resources.
 *
 * @RestResource (
 *   id = "erpw_webform_serviceslatestupdate",
 *   label = @Translation("servicesLatestUpdate"),
 *   uri_paths = {
 *     "canonical" = "/api/erpw-webform-serviceslatestupdate/{id}"
 *   }
 * )
 *
 * @DCG
 * The plugin exposes key-value records as REST resources. In order to enable it
 * import the resource configuration into active configuration storage. An
 * example of such configuration can be located in the following file:
 * core/modules/rest/config/optional/rest.resource.entity.node.yml.
 * Alternatively you can enable it through admin interface provider by REST UI
 * module.
 * @see https://www.drupal.org/project/restui
 *
 * @DCG
 * Notice that this plugin does not provide any validation for the data.
 * Consider creating custom normalizer to validate and normalize the incoming
 * data. It can be enabled in the plugin definition as follows.
 * @code
 *   serialization_class = "Drupal\foo\MyDataStructure",
 * @endcode
 *
 * @DCG
 * For entities, it is recommended to use REST resource plugin provided by
 * Drupal core.
 * @see \Drupal\rest\Plugin\rest\resource\EntityResource
 */
class ServiceslatestupdateResource extends ResourceBase {

  /**
   * The key-value storage.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected $storage;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    KeyValueFactoryInterface $keyValueFactory,
    EntityTypeManagerInterface $entityTypeManager,
    Connection $database,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger, $keyValueFactory);
    $this->storage = $keyValueFactory->get('erpw_webform_serviceslatestupdate');
    $this->entityTypeManager = $entityTypeManager;
    $this->database = $database;
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
      $container->get('keyvalue'),
      $container->get('entity_type.manager'),
      $container->get('database'),
    );
  }

  /**
   * Responds to GET requests.
   *
   * @param int $id
   *   The ID of the record.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The response containing the record.
   */
  public function get($id) {
    $results = [];
    if ($id == 'service') {
      $query = $this->database->select('webform_submission', 'ws');
      $query->join('webform_submission_data', 'wsd', 'wsd.sid = ws.sid');
      $query->fields('ws', ['sid', 'changed']);
      $query->orderBy('ws.changed', 'DESC');
      $query->condition('wsd.value', 'approve');
      $query->range(0, 1);
      $results = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
    }

    return new ModifiedResourceResponse($results, 200);
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
