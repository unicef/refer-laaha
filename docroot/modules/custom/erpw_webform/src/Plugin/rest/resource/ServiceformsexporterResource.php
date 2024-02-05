<?php

namespace Drupal\erpw_webform\Plugin\rest\resource;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    $cid = 'service_forms_exporter_resource';
    $webformsRevised = [];
    // Specify cache tags related to webforms.
    $cacheTags = ['webform'];
    if ($cache = \Drupal::cache()
      ->get($cid)) {
      $webformsRevised = $cache->data;
    }
    else {
      $webforms = $this->entityTypeManager->getStorage('webform')->loadMultiple();
      foreach ($webforms as $id => $webform) {
        $webformsRevised[$id] = $webform->toArray();
        $webformsRevised[$id]['elementsFlattened'] = $webform->getElementsInitializedFlattenedAndHasValue();
        $tpa = $webform->getThirdPartySetting('erpw_webform', 'webform_service_type_map');
        foreach ($tpa as $sid) {
          if ($sid[0] != '') {
            $service_type_node = \Drupal::entityTypeManager()->getStorage('node')->load($sid[0]);
            if ($service_type_node != NULL) {
              $webformsRevised[$id]['serviceTypeTitle'] = $service_type_node->getTitle();
            }
          }
        }

        // Add cache tag for each webform.
        $cacheTags[] = 'webform:' . $id;
      }
      \Drupal::cache()
        ->set($cid, $webformsRevised, Cache::PERMANENT, $cacheTags);
    }
    $this->logger->notice('Exported service forms.');
    // Return the newly created record in the response body.
    // @todo Cache computed value.
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
