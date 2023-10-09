<?php

namespace Drupal\erpw_webform\Plugin\rest\resource;

use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rest\ResourceResponse;

/**
 * Represents ServiceFormsExporter records as resources.
 *
 * @RestResource (
 *   id = "erpw_webform_serviceformsexporter",
 *   label = @Translation("ServiceFormsExporter"),
 *   uri_paths = {
 *     "canonical" = "/api/erpw-webform-serviceformsexporter"
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
class ServiceformsexporterResource extends ResourceBase {

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
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger, $keyValueFactory);
    $this->storage = $keyValueFactory->get('erpw_webform_serviceformsexporter');
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
      $container->get('keyvalue'),
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
    $webforms = $this->entityTypeManager->getStorage('webform')->loadMultiple();
    $webformsRevised = [];
    foreach ($webforms as $id => $webform) {
      $webformsRevised[$id] = $webform->toArray();
      $webformsRevised[$id]['elementsFlattened'] = $webform->getElementsInitializedFlattenedAndHasValue();
    }
    $this->logger->notice('Exported service forms.');
    // Return the newly created record in the response body.
    return new ResourceResponse($webformsRevised, 201);
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
