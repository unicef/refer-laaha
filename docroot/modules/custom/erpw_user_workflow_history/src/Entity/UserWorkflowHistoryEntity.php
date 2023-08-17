<?php

namespace Drupal\erpw_user_workflow_history\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the User workflow history entity entity.
 *
 * @ingroup erpw_user_workflow_history
 *
 * @ContentEntityType(
 *   id = "user_workflow_history_entity",
 *   label = @Translation("User workflow history entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\erpw_user_workflow_history\UserWorkflowHistoryEntityListBuilder",
 *     "views_data" = "Drupal\erpw_user_workflow_history\Entity\UserWorkflowHistoryEntityViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\erpw_user_workflow_history\Form\UserWorkflowHistoryEntityForm",
 *       "add" = "Drupal\erpw_user_workflow_history\Form\UserWorkflowHistoryEntityForm",
 *       "edit" = "Drupal\erpw_user_workflow_history\Form\UserWorkflowHistoryEntityForm",
 *       "delete" = "Drupal\erpw_user_workflow_history\Form\UserWorkflowHistoryEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\erpw_user_workflow_history\UserWorkflowHistoryEntityHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\erpw_user_workflow_history\UserWorkflowHistoryEntityAccessControlHandler",
 *   },
 *   base_table = "user_workflow_history_entity",
 *   translatable = FALSE,
 *   admin_permission = "administer user workflow history entity entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/user_workflow_history_entity/{user_workflow_history_entity}",
 *     "add-form" = "/admin/structure/user_workflow_history_entity/add",
 *     "edit-form" = "/admin/structure/user_workflow_history_entity/{user_workflow_history_entity}/edit",
 *     "delete-form" = "/admin/structure/user_workflow_history_entity/{user_workflow_history_entity}/delete",
 *     "collection" = "/admin/structure/user_workflow_history_entity",
 *   },
 *   field_ui_base_route = "user_workflow_history_entity.settings"
 * )
 */
class UserWorkflowHistoryEntity extends ContentEntityBase implements UserWorkflowHistoryEntityInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Reviewed or Actioned by'))
      ->setDescription(t('The user ID of author of the User workflow history entity entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 99,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 99,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the User workflow history entity entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -100,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -100,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the User workflow history entity is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 100,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
