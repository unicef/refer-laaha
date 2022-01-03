<?php

namespace Drupal\erpw_location\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Location Entity type entity.
 *
 * @ConfigEntityType(
 *   id = "location_type",
 *   label = @Translation("Location Entity type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\erpw_location\LocationEntityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\erpw_location\Form\LocationEntityTypeForm",
 *       "edit" = "Drupal\erpw_location\Form\LocationEntityTypeForm",
 *       "delete" = "Drupal\erpw_location\Form\LocationEntityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\erpw_location\LocationEntityTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "location_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "location",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/location_type/{location_type}",
 *     "add-form" = "/admin/structure/location_type/add",
 *     "edit-form" = "/admin/structure/location_type/{location_type}/edit",
 *     "delete-form" = "/admin/structure/location_type/{location_type}/delete",
 *     "collection" = "/admin/structure/location_type"
 *   }
 * )
 */
class LocationEntityType extends ConfigEntityBundleBase implements LocationEntityTypeInterface {

  /**
   * The Location Entity type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Location Entity type label.
   *
   * @var string
   */
  protected $label;

}
