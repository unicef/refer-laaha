<?php

namespace Drupal\vss_content;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\hal\LinkManager\LinkManagerInterface;
use Drupal\user\EntityOwnerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Serializer;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList;

/**
 * A service for handling import of default content.
 *
 * @todo throw useful exceptions
 */
class Importer implements ImporterInterface {

  /**
   * The serializer service.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * A list of vertex objects keyed by their link.
   *
   * @var array
   */
  protected $vertexes = [];

  /**
   * The graph entries.
   *
   * @var array
   */
  protected $graph = [];

  /**
   * The link manager service.
   *
   * @var \Drupal\hal\LinkManager\LinkManagerInterface
   */
  protected $linkManager;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The account switcher.
   *
   * @var \Drupal\Core\Session\AccountSwitcherInterface
   */
  protected $accountSwitcher;

  /**
   * Constructs the default content manager.
   *
   * @param \Symfony\Component\Serializer\Serializer $serializer
   *   The serializer service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\hal\LinkManager\LinkManagerInterface $link_manager
   *   The link manager service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Session\AccountSwitcherInterface $account_switcher
   *   The account switcher.
   */
  public function __construct(Serializer $serializer, EntityTypeManagerInterface $entity_type_manager, LinkManagerInterface $link_manager, EventDispatcherInterface $event_dispatcher, AccountSwitcherInterface $account_switcher) {
    $this->serializer = $serializer;
    $this->entityTypeManager = $entity_type_manager;
    $this->linkManager = $link_manager;
    $this->eventDispatcher = $event_dispatcher;
    $this->accountSwitcher = $account_switcher;
  }

  /**
   * {@inheritdoc}
   */
  public function importContent($files, $update_existing = FALSE) {
    drupal_flush_all_caches();
    $created = [];
    $updated = [];
    $revision_links = [];

    $folder = "profiles/pfmonoferricca_profile/content";

    $root_user = $this->entityTypeManager->getStorage('user')->load(1);
    $this->accountSwitcher->switchTo($root_user);
    $file_map = [];

    // Default content uses drupal.org as domain.
    $this->linkManager->setLinkDomain('http://drupal.org');
    // Parse all of the files and sort them in order of dependency.
    foreach ($files as $file_entity_type => $file_entity) {
      foreach ($file_entity as $item) {
        $file = [];
        $file['file_uri'] = $folder . '/' . $file_entity_type . '/' . $item;
        $contents = $this->parseFile($file);
        // Decode the file contents.
        $decoded = $this->serializer->decode($contents, 'hal_json');
        // print_r($decoded);
        // Get the link to this entity.
        $item_uuid = $decoded['uuid'][0]['value'];

        // Store the entity type with the file.
        $file['entity_type_id'] = $file_entity_type;
        // Store the file in the file map.
        $file_map[$item_uuid] = $file;
        // Create a vertex for the graph.
        $vertex = $this->getVertex($item_uuid);
        $this->graph[$vertex->id]['edges'] = [];
        if (empty($decoded['_embedded'])) {
          // No dependencies to resolve.
          continue;
        }
        // Here we need to resolve our dependencies:
        foreach ($decoded['_embedded'] as $embedded) {
          foreach ($embedded as $item) {
            $uuid = $item['uuid'][0]['value'];
            $edge = $this->getVertex($uuid);
            $this->graph[$vertex->id]['edges'][$edge->id] = TRUE;
          }
        }
      }
    }

    $sorted = $this->graph;
    $definitions = $this->entityTypeManager->getDefinitions();
    foreach ($sorted as $link => $details) {
      if (!empty($file_map[$link])) {
        $file = $file_map[$link];
        $entity_type_id = $file['entity_type_id'];
        /** @var \Drupal\Core\Entity\EntityTypeInterface $entity_type */
        $entity_type = $definitions[$entity_type_id];
        $contents = $this->parseFile($file);

        /** @var \Drupal\Core\Entity\EntityInterface $entity */
        $entity = $this->serializer->deserialize($contents, $entity_type->getClass(), 'hal_json', ['request_method' => 'POST']);

        // Ensure we use the proper target_revision_id for edges.
        if (!empty($details['edges']) && !empty($revision_links)) {
          foreach ($details['edges'] as $uuid => $bool) {
            foreach ($entity as $data) {
              if ($data instanceof EntityReferenceRevisionsFieldItemList) {
                foreach ($data as $item) {
                  if ($target_entity = $item->getProperties(TRUE)['entity']) {
                    $target_entity_type_id = $target_entity->getTargetDefinition()->getEntityTypeId();
                    $target_id = $target_entity->getTargetIdentifier();
                    if (isset($revision_links[$target_entity_type_id][$target_id])) {
                      $target_entity->setValue([
                        'target_id' => $target_id,
                        'target_revision_id' => $revision_links[$target_entity_type_id][$target_id],
                      ]);
                    }
                  }
                }
              }
            }
          }
        }

        $is_new = TRUE;

        $old_entity = $this->lookupEntity($entity, $entity_type);

        if ($old_entity && $update_existing) {
          // All unique keys need to match the old entity.
          $entity->{$entity_type->getKey('uuid')} = $old_entity->uuid();
          $entity->{$entity_type->getKey('id')} = $old_entity->id();
          $is_new = FALSE;
          if ($this->isRevisionableEntity($entity)) {
            $entity->{$entity_type->getKey('revision')} = $old_entity->getRevisionId();
          }
        }
        elseif (!$old_entity) {
          // Don't import site level IDs if they are used.
          if ($this->existEntityId($entity, $entity_type)) {
            $entity->{$entity_type->getKey('id')} = NULL;
          }
          $entity->{$entity_type->getKey('revision')} = NULL;
        }

        !$is_new && $old_entity ? $entity->setOriginalId($old_entity->id()) : $entity->enforceIsNew($is_new);
        if ($this->isRevisionableEntity($entity)) {
          $entity->setNewRevision($is_new);
        }

        // Ensure that the entity is not owned by the anonymous user.
        if ($entity instanceof EntityOwnerInterface && empty($entity->getOwnerId())) {
          $entity->setOwner($root_user);
        }

        if ($old_entity && $update_existing) {
          $updated[$entity->uuid()] = $entity;
          $entity->save();
          if ($this->isRevisionableEntity($entity)) {
            $revision_links[$entity->getEntityTypeId()][$entity->id()] = $entity->{$entity_type->getKey('revision')}->value;
          }
        }
        elseif (!$old_entity) {
          $created[$entity->uuid()] = $entity;
          $entity->save();
          if ($this->isRevisionableEntity($entity)) {
            $revision_links[$entity->getEntityTypeId()][$entity->id()] = $entity->{$entity_type->getKey('revision')}->value;
          }
        }
      }
    }
    $this->accountSwitcher->switchBack();

    // Reset the tree.
    $this->resetTree();
    // Reset link domain.
    $this->linkManager->setLinkDomain(FALSE);
    return $created;
  }

  /**
   * Lookup whether an entity already exists.
   *
   * For most typical entities this is done by uuid.
   * For core user 1 this is done by id.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that will be imported.
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type for this entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The old entity, or NULL if no entity.
   */
  public function lookupEntity(EntityInterface $entity, EntityTypeInterface $entity_type) {
    $entity_storage = $this->entityTypeManager->getStorage($entity_type->id());

    $lookup_properties = [$entity_type->getKey('uuid') => $entity->uuid()];
    // Alter the lookup properties for known core irregularities.
    if ($entity_type->id() === 'user' && $entity->id() == 1) {
      $lookup_properties = [$entity_type->getKey('id') => $entity->id()];
    }

    $entity_query = $entity_storage->getQuery()->accessCheck(FALSE);
    foreach ($lookup_properties as $key => $value) {
      // Cast scalars to array so we can consistently use an IN condition.
      $entity_query->condition($key, (array) $value, 'IN');
    }
    $result = $entity_query->execute();

    $old_entity = $result ? $entity_storage->load(current($result)) : [];

    return $old_entity;
  }

  /**
   * Check if an imported entity id already exists.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that will be imported.
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type of this entity.
   *
   * @return bool
   *   TRUE if current entity's id exists.
   */
  public function existEntityId(EntityInterface $entity, EntityTypeInterface $entity_type) {
    if ($entity->id()) {
      $entity_storage = $this->entityTypeManager->getStorage($entity_type->id());
      $entity_query = $entity_storage->getQuery()->accessCheck(FALSE);
      $entity_query->condition($entity_type->getKey('id'), (array) $entity->id(), 'IN');
      $result = $entity_query->execute();
      return !empty($result);
    }
  }

  /**
   * Checks a given entity for revision support.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   A typical drupal entity object.
   *
   * @return bool
   *   Whether this entity supports revisions.
   */
  protected function isRevisionableEntity(EntityInterface $entity) {
    return $entity instanceof RevisionableInterface && $entity->getEntityType()->isRevisionable();
  }

  /**
   * Parses content files.
   *
   * @param object $file
   *   The scanned file.
   *
   * @return string
   *   Contents of the file.
   */
  protected function parseFile($file) {
    return file_get_contents($file['file_uri']);
  }

  /**
   * Resets tree properties.
   */
  protected function resetTree() {
    $this->graph = [];
    $this->vertexes = [];
  }

  /**
   * Returns a vertex object for a given item link.
   *
   * Ensures that the same object is returned for the same item link.
   *
   * @param string $item_link
   *   The item link as a string.
   *
   * @return object
   *   The vertex object.
   */
  protected function getVertex($item_link) {
    if (!isset($this->vertexes[$item_link])) {
      $this->vertexes[$item_link] = (object) ['id' => $item_link];
    }
    return $this->vertexes[$item_link];
  }

}
