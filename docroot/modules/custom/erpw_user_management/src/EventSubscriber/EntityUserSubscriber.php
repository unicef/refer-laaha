<?php

namespace Drupal\erpw_user_management\EventSubscriber;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Drupal\erpw_location\LocationService;
use Drupal\erpw_pathway\Services\ErpwPathwayService;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class EntityLocationSubscriber.
 *
 * To alter the node forms.
 */
class EntityUserSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  const MAX_LEVEL = 4;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected static $entityTypeManager;

  /**
   * Array of levels.
   *
   * @var levelLabel
   */
  protected $levelLabel = [];

  /**
   * A LocationService instance.
   *
   * @var Drupal\erpw_location\LocationService
   */
  protected static $locationService;

  /**
   * ERP PATHWAY service.
   *
   * @var \Drupal\erpw_pathway\Services\ErpwPathwayService
   */
  protected $erpwPathwayService;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The Current user service.
   *
   * @var Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Request Stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
    LocationService $location_service,
    RouteMatchInterface $route_match,
    ErpwPathwayService $erpw_pathway_service,
    AccountProxyInterface $current_user,
    RequestStack $requestStack) {
    self::$entityTypeManager = $entity_type_manager;
    self::$locationService = $location_service;
    $this->erpwPathwayService = $erpw_pathway_service;
    $this->routeMatch = $route_match;
    $this->currentUser = $current_user;
    $this->requestStack = $requestStack;
  }

  /**
   * Alter form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormAlterEvent $event
   *   The event.
   */
  public function alterForm(FormAlterEvent $event): void {
    $form = &$event->getForm();
    $form_id = $event->getFormId();
    $form_state = $event->getFormState();

    if ($form_id == 'user_form') {
      $access = erpw_user_management_user_form_access();
      $user = $this->routeMatch->getParameter('user');
      if ($access) {
        $permission = 'edit users of their own location and organisation';
        if ($user->hasPermission($permission)) {
          $form['field_organisation']['widget'][0]['target_id']['#attributes']['disabled'] = 'disabled';
        }
        $form['account']['name']['#access'] = FALSE;
        $form['account']['roles']['#access'] = FALSE;
        $form['field_location']['#access'] = TRUE;
        $form['account']['status']['#access'] = FALSE;
        $form['account']['notify']['#access'] = FALSE;
        $form['account']['mail']['#description'] = '';
        $form['account']['mail']['#type'] = 'textfield';
        $parent_list = [];
        $form['#validate'][] = [$this, 'userValidateForm'];
        $ptids = [];
        if (empty($form_state->getTriggeringElement()['#level']) && $user instanceof UserInterface) {
          $locid = $user->get('field_location')->target_id;
          $parent_list = self::getTermParents($locid);
          $parent_list = empty($parent_list) ? array_values($ptids) : $parent_list;
        }
        $permission1 = 'edit users of their own location';
        $permission2 = 'edit users of their own location and organisation';
        $current_user = self::$entityTypeManager->getStorage('user')->load($this->currentUser->id());
        $ptids = [];
        $form['account']['mail']['#weight'] = "-5";
        if ($user->get('uid')->value != $current_user->get('uid')->value) {
          $form['account']['current_pass']['#access'] = FALSE;
          $form['account']['pass']['#access'] = FALSE;
        }
        if ($current_user->get('uid')->value != 1 && !$current_user->hasRole('administrator') &&
          ($current_user->hasPermission($permission1) || $current_user->hasPermission($permission2))) {
          $location_id = '';
          if ($current_user->hasField('field_location') && !$current_user->get('field_location')->isEmpty()) {
            $location_id = $current_user->get('field_location')->getValue()[0]['target_id'];
          }
          $ptids = self::$locationService->getAllAncestors($location_id);
          $parent_list = empty($parent_list) ? array_values($ptids) : $parent_list;
        }
        if (!$this->requestStack->getCurrentRequest()->query->get('pass-reset-token')) {
          if ($this->routeMatch->getRouteName() == 'entity.user.edit_form') {
            $current_user_id = $this->currentUser->id();
            $roles = $this->currentUser->getRoles();
            $form_user_id = $this->requestStack->getCurrentRequest()->attributes->get('user')->id();
            foreach ($roles as $role) {
              if ($role != 'administrator' && $role != 'country_admin') {
                if (!$current_user_id == $form_user_id) {
                  $form = $this->erpwPathwayService->getLocationForm($form, $form_state, $parent_list, $ptids);
                }
              }
            }
          }
        }

        if ($form_id == 'user_form') {
          $form['actions']['back'] = [
            '#type' => 'submit',
            '#value' => $this->t('Cancel'),
            '#attributes' => [
              'class' => [
                'button-border signup-next',
              ],
              'onClick' => 'window.location.href = document.referrer; return false;',
            ],
          ];
          $form['actions']['submit']['#value'] = $this->t('Update');
          $form['actions']['submit']['#attributes']['class'][] = 'signup-next';
          $form['actions']['delete']['#access'] = FALSE;
          $form['actions']['submit']['#submit'][] = [
            $this,
            'eprwUserSubmitHandler',
          ];
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function userValidateForm(array &$form, $form_state) {
    $password = $form_state->getValue('pass');
    if ($password && !preg_match("/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,64}$/", $password)) {
      $form_state->setErrorByName('pass', $this->t('Password must contain at least one number, one symbol, one lowercase and uppercase letter. Minimum length must be 8 characters.'));
    }
  }

  /**
   * Get the list of parents of the child.
   *
   * @param mixed $referenced_entities
   *   Referenced_entities instance.
   *
   * @return array
   *   Return the list of parents.
   */
  public static function getTermParents($referenced_entities) {
    if (empty($referenced_entities)) {
      return [''];
    }
    $parents = $loc_tid = [];
    $referenced_entities = is_array($referenced_entities) ?
    $referenced_entities : [$referenced_entities];
    foreach ($referenced_entities as $loc) {
      $loc_tid[] = $loc;
      $parents = self::$locationService->getAllAncestors($loc);
    }
    $location_entities = self::$entityTypeManager->getStorage('location')->loadByProperties([
      'type' => 'country',
      'status' => 1,
      'field_location_taxonomy_term' => $parents[0],
    ]);

    $location_entities = array_values($location_entities);
    for ($i = self::MAX_LEVEL; $i >= 0; $i--) {
      $level_id = 'level_' . $i;
      if (isset($location_entities[0]->get($level_id)->getValue()[0])) {
        break;
      }
    }
    if (count($parents) == ($i + 1)) {
      $index = count($parents) - 1;
      $parents[$index] = $loc_tid;
    }

    return $parents;
  }

  /**
   * {@inheritdoc}
   */
  public function eprwUserSubmitHandler(&$form, $form_state) {

    if (!\Drupal::request()->get('pass-reset-token')) {
      // Bring location data to save even though it is not displayed.
      $field_location = $form_state->getValue('field_location');
      $location_level = [];
      if (is_array($field_location)) {
        $i = 0;
        foreach ($field_location as $location) {
          if (isset($location['target_id'])) {
            $location_level[] = $field_location[$i]['target_id'];
            $i++;
          }
        }
      }
      // Saving the location data.
      $form_object = $form_state->getFormObject();
      if ($form_object instanceof EntityForm) {
        $entity = $form_object->getEntity();
        $this->saveLocationFieldUser($entity, $location_level);
      }
      if ($entity instanceof UserInterface) {
        $role = $form_state->getValue('field_system_role')[0]['target_id'];
        $entity->roles = [$role];
        $entity->save();
      }
      $current_user_id = $this->currentUser->id();
      $form_user_id = $this->requestStack->getCurrentRequest()->attributes->get('user')->id();
      $access = User::load($form_user_id)->get('access')->value;
      $status = User::load($form_user_id)->get('status')->value;
      if ($current_user_id == $form_user_id) {
        return _erpw_custom_redirect('user.page');
      }
      elseif ($access == 0 && $status == 0) {
        $path = '/user/' . $form_user_id;
        $response = new RedirectResponse($path);
        $response->send();
        return;
      }
      else {
        return _erpw_custom_redirect('view.user_lists.page_1');
      }
    }
    else {
      // After reset password the page will redirect to dashboard.
      return _erpw_custom_redirect('erpw_custom.dashboard');
    }
  }

  /**
   * Save eRPW location field.
   *
   * @param object $entity
   *   Entity instance.
   * @param array|string $location
   *   Location data.
   */
  protected function saveLocationFieldUser($entity, $location) {
    $user = $this->routeMatch->getParameter('user');
    if ($user instanceof UserInterface) {
      $entity->set('field_location', []);
    }
    if (is_array($location)) {
      foreach ($location as $value) {
        $entity->field_location[] = ['target_id' => $value];
      }
    }
    else {
      $entity->field_location[] = ['target_id' => $location];
    }
    $entity->save();
  }

  /**
   * {@inheritdoc}
   */
  public function eprwCancelHandler(&$form, $form_state) {
    return _erpw_custom_redirect('view.user_lists.page_1', 'rpw_listing');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      FormHookEvents::FORM_ALTER => 'alterForm',
    ];
  }

}
