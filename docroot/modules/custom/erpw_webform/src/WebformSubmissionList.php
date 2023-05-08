<?php

namespace Drupal\erpw_webform;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\webform\WebformSubmissionListBuilder;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Provides a list controller for webform submission entity.
 *
 * @ingroup webform
 */
class WebformSubmissionList extends WebformSubmissionListBuilder {

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    /** @var \Drupal\webform\WebformSubmissionListBuilder $instance */
    $instance = parent::createInstance($container, $entity_type);
    $instance->initialize();
    return $instance;
  }

  /**
   * Initialize WebformSubmissionListBuilder object.
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
   * Build the webform submission view.
   *
   * @return array
   *   A renderable array containing a submission view.
   */
  protected function buildSubmissionViews() {
    return parent::buildSubmissionViews();
  }

  /**
   * Build the webform submission entity list.
   *
   * @return array
   *   A renderable array containing the entity list.
   */
  protected function buildEntityList() {
    return parent::buildEntityList();
  }

  /**
   * Build the submission views menu.
   *
   * @return array
   *   A render array representing the submission views menu.
   */
  protected function buildSubmissionViewsMenu() {
    return parent::buildSubmissionViewsMenu();
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
   * Build the customize button.
   *
   * @return array
   *   A render array representing the customize button.
   */
  protected function buildCustomizeButton() {
    return parent::buildCustomizeButton();
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

  /* ************************************************************************ */
  // Header functions.
  /* ************************************************************************ */

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    return parent::buildHeader();
  }

  /**
   * Build table header column.
   *
   * @param array $column
   *   The column.
   *
   * @return array
   *   A renderable array containing a table header column.
   *
   * @throws \Exception
   *   Throw exception if table header column is not found.
   */
  protected function buildHeaderColumn(array $column) {
    return parent::buildHeaderColumn($column);
  }

  /* ************************************************************************ */
  // Row functions.
  /* ************************************************************************ */

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    $user_roles = $user->get('roles')->getValue();
    $webformID = $entity->get('webform_id')->getValue()[0]['target_id'];
    $webform = $this->entityTypeManager->getStorage('webform')->load($webformID);
    $tpa = $webform->getThirdPartySetting('erpw_webform', 'webform_service_type_map');
    $domainAccess = $user->get('field_domain_access')->getValue();
    foreach ($user_roles as $key_r => $role) {
      if ($role == "country_admin" || $role == "interagency_gbv_coordinator" || $role == "service_provider_staff" || $role == "service_provider_focal_point") {
        foreach ($domainAccess as $key => $domain) {
          if (array_key_exists($domain['target_id'], $tpa)) {
            dump($domain['target_id']);
            dump($tpa);
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
   * Build row column.
   *
   * @param array $column
   *   Column settings.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   A webform submission.
   *
   * @return array|mixed
   *   The row column value or renderable array.
   *
   * @throws \Exception
   *   Throw exception if table row column is not found.
   */
  public function buildRowColumn(array $column, EntityInterface $entity) {
    return parent::buildRowColumn($column, $entity);
  }

  /* ************************************************************************ */
  // Operations.
  /* ************************************************************************ */

  /**
   * {@inheritdoc}
   */
  public function buildOperations(EntityInterface $entity) {
    return parent::buildOperations($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    return parent::getDefaultOperations($entity);
  }

  /* ************************************************************************ */
  // Route functions.
  /* ************************************************************************ */

  /**
   * Get submission route name based on the current route.
   *
   * @return string
   *   The submission route name which can be either 'webform.user.submission'
   *   or 'webform_submission.canonical.
   */
  protected function getSubmissionRouteName() {
    return parent::getSubmissionRouteName();
  }

  /**
   * Get base route name for the webform or webform source entity.
   *
   * @return string
   *   The base route name for webform or webform source entity.
   */
  protected function getBaseRouteName() {
    return parent::getBaseRouteName();
  }

  /**
   * Get route parameters for the webform or webform source entity.
   *
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   *   A webform submission.
   *
   * @return array
   *   Route parameters for the webform or webform source entity.
   */
  protected function getRouteParameters(WebformSubmissionInterface $webform_submission) {
    return parent::getRouteParameters($webform_submission);
  }

  /* ************************************************************************ */
  // Submission views functions.
  /* ************************************************************************ */

  /**
   * Get the submission view type for the current route.
   *
   * @return string
   *   The submission view type for the current route.
   */
  protected function getSubmissionViewType() {
    return parent::getSubmissionViewType();
  }

  /**
   * Determine if the submission view(s) replaced the default results table.
   *
   * @return bool
   *   TRUE if the submission view(s) replaced the default results table.
   */
  protected function isSubmissionViewResultsReplaced() {
    return parent::isSubmissionViewResultsReplaced();
  }

  /**
   * Get all submission views applicable.
   *
   * @return array
   *   An associative array of all submission views.
   */
  protected function getSubmissionViewsConfig() {
    return parent::getSubmissionViewsConfig();
  }

  /**
   * Get submission views applicable for the current route and user.
   *
   * @return array
   *   An associative array of submission views applicable for the
   *   current route and user.
   */
  protected function getSubmissionViews() {
    return parent::getSubmissionViews();
  }

  /* ************************************************************************ */
  // Query functions.
  /* ************************************************************************ */

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
   * @param string $state
   *   (optional) Submission state.
   * @param string $source_entity
   *   (optional) Source entity (type:id).
   *
   * @return int
   *   The total number of submissions.
   */
  protected function getTotal($keys = '', $state = '', $source_entity = '') {
    return parent::getTotal($keys, $state, $source_entity);
  }

  /**
   * Get the base entity query filtered by webform and search.
   *
   * @param string $keys
   *   (optional) Search key.
   * @param string $state
   *   (optional) Submission state.
   * @param string $source_entity
   *   (optional) Source entity (type:id).
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   An entity query.
   */
  protected function getQuery($keys = '', $state = '', $source_entity = '') {
    return parent::getQuery($keys, $state, $source_entity);
  }

  /**
   * Get custom setting.
   *
   * @param string $name
   *   The custom setting name.
   * @param mixed $default
   *   Default custom setting value.
   *
   * @return mixed
   *   The custom setting value.
   */
  protected function getCustomSetting($name, $default = NULL) {
    return parent::getCustomSetting($name, $default);
  }

}
