<?php

namespace Drupal\erpw_webform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Component\Serialization\Json;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Cache\Cache;
use Drupal\user\Entity\User;

/**
 * Controller for custom PWA export.
 */
class ServicesPwaExportController extends ControllerBase {

  /**
   * Add a private property to store the EntityTypeManager.
   */
  protected $entityTypeManager;

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new ServiceWebforms object.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, AccountInterface $currentUser) {
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
  }

  /**
   *
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * Download CSV file containing entities and their fields/values.
   *
   * @param string $node
   *   The value of the contextual filter.
   *
   *   The CSV file download response.
   */
  public function getServiceProviderList($node) {

    $activeDomain = \Drupal::service('domain.negotiator')->getActiveDomain()->id();
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $current_user = User::load($this->currentUser->id());
    $roles = $current_user->getRoles();
    // Short roles.
    $shortrolestr = '';
    foreach ($roles as $role) {
      $words = explode('_', $role);
      foreach ($words as $word) {
        $shortrolestr .= substr($word, 0, 1);
      }
    }
    $user_org_id = $current_user->get('field_organisation')->getValue()[0]['target_id'] ?? '';

    // Processing the location ids and generating a uniq string.
    $tidsstring = '';
    if (!$current_user->get('field_location')->isEmpty()) {
      $location_ids = $current_user->get('field_location')->getValue();
      $tids = array_column($location_ids, 'target_id');
      sort($tids);
      $tidsstring = implode('', $tids);
    }

    if ($this->currentUser->isAuthenticated()) {
      $cacheId = $activeDomain . $language . $shortrolestr .  $user_org_id . $tidsstring;
    } else {
      $cacheId = $activeDomain . '_' . $language . '_' . $shortrolestr ;
    }

    $jsondecode = [];
    if ($cache = \Drupal::cache()->get($cacheId)) {
      $jsondecode = $cache->data;
    }
    else {
      // Fetch the entities from your Views listing using the contextual filter value.
      $view_id = 'service_based_service_providers_listing';
      $view = Views::getView($view_id);
      $view->setArguments([$node]);
      $view->setDisplay('rest_export_1');
      $view->execute();
      // Check if the result is empty
      if (!empty($view->result)) {
        $jsondecode = Json::decode($view->render()['#markup']);
        \Drupal::cache()->set($cacheId, $jsondecode, Cache::PERMANENT);
      }
    }

    return new JsonResponse($jsondecode);
  }

}
