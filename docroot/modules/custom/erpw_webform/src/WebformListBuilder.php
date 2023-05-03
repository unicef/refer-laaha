<?php

namespace Drupal\erpw_webform;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Url;
use Drupal\webform\EntityStorage\WebformEntityStorageTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\webform\WebformEntityListBuilder;

/**
 * Defines a class to build a listing of webform entities.
 *
 * @see \Drupal\webform\Entity\Webform
 */
class WebformListBuilder extends WebformEntityListBuilder {

  use WebformEntityStorageTrait;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    $instance = parent::createInstance($container, $entity_type);
    return $instance;
  }

  /**
   * Initialize WebformEntityListBuilder object.
   */
  protected function initialize() {
    parent::initialize();
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    return parent::render();
  }

  /**
   * Build the filter form.
   *
   * @return array
   *   A render array representing the filter form.
   */
  protected function buildFilterForm() {
    return parent::buildFilterForm();
  }

  /**
   * Build information summary.
   *
   * @return array
   *   A render array representing the information summary.
   */
  protected function buildInfo() {
    return parent::buildInfo();
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    return parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    $user_roles = $user->get('roles')->getValue();
    $tpa = $entity->getThirdPartySetting('erpw_webform', 'webform_service_type_map');
    $domainAccess = $user->get('field_domain_access')->getValue();
    foreach ($user_roles as $key_r => $role) {
      if ($role['target_id'] == "country_admin") {
        foreach ($domainAccess as $key => $domain) {
          if (array_key_exists($domain['target_id'], $tpa)) {
            return parent::buildRow($entity);
          }
        }
      }
      if ($role['target_id'] == "super_admin" || $role['target_id'] == "administrator") {
        return parent::buildRow($entity);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildOperations(EntityInterface $entity) {
    return parent::buildOperations($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity, $type = 'edit') {
    return parent::getDefaultOperations($entity, $type);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    return parent::getEntityIds();
  }

  /**
   * Get the total number of submissions.
   *
   * @param string $keys
   *   (optional) Search key.
   * @param string $category
   *   (optional) Category.
   * @param string $state
   *   (optional) Webform state. Can be 'open' or 'closed'.
   *
   * @return int
   *   The total number of submissions.
   */
  protected function getTotal($keys = '', $category = '', $state = '') {
    return parent::getTotal($keys, $category, $state);
  }

  /**
   * Get the base entity query filtered by webform and search.
   *
   * @param string $keys
   *   (optional) Search key.
   * @param string $category
   *   (optional) Category.
   * @param string $state
   *   (optional) Webform state. Can be 'open' or 'closed'.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   An entity query.
   */
  protected function getQuery($keys = '', $category = '', $state = '') {
    return parent::getQuery($keys, $category, $state);
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    return parent::load();
  }

  /**
   * Get number of entities to list per page.
   *
   * @return int|false
   *   The number of entities to list per page, or FALSE to list all entities.
   */
  protected function getLimit() {
    return parent::getLimit();
  }

  /**
   * Is the current user a webform administrator.
   *
   * @return bool
   *   TRUE if the current user has 'administer webform' or 'edit any webform'
   *   permission.
   */
  protected function isAdmin() {
    return parent::isAdmin();
  }

  /**
   * {@inheritdoc}
   */
  protected function ensureDestination(Url $url) {
    // Never add a destination to operation URLs.
    return parent::ensureDestination($url);
  }

}
