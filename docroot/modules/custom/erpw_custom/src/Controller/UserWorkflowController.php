<?php

namespace Drupal\erpw_custom\Controller;

use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\UserInterface;

/**
 * ModalFormController class.
 */
class UserWorkflowController extends ControllerBase {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * A entityManager instance.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * The ModalFormController constructor.
   *
   * @param \Drupal\Core\Form\FormBuilder $form_builder
   *   The form builder.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityManager object.
   */
  public function __construct(FormBuilder $form_builder, EntityTypeManagerInterface $entity_type_manager) {
    $this->formBuilder = $form_builder;
    $this->entityManager = $entity_type_manager;
  }

  /**
   * Accept User.
   */
  public function acceptUser(UserInterface $user, Request $request) {
    // Get current user role.
    $roles = \Drupal::currentUser()->getRoles();
    // For SPFP.
    if (in_array('service_provider_focal_point', $roles)) {
      // Make sure this is the right user take action.
      if ($user->hasRole('service_provider_staff')) {
        // Update the user with new transition.
        $user->set('field_transitions', 'spfp-accept');
        $user->save();

        // Update user workflow history entity.
        $current_time = \Drupal::time()->getCurrentTime('d');    
        $euwh = $this->entityTypeManager->getStorage('user_workflow_history_entity')->create([
          'name' => \Drupal::service('date.formatter')->format($current_time, 'custom', 'd/m/Y H:i:s'),
          'status' => 1,
          'field_user' => $user->id(),
          'field_workflow_status_before' => 'self-register-sp-staff',
          'field_workflow_status_after' => 'spfp-accept',
        ]);
        $euwh->save();
      }
    }

    // For GBV Coordination.
    if (in_array('interagency_gbv_coordinator', $roles) || in_array('country_admin', $roles)) {
      // Make sure this is the right user take action.
      if ($user->hasRole('service_provider_staff')) {
        // Update the user with new transition.
        $user->activate();
        $user->set('field_transitions', 'gbv-coordination-accept');
        $user->save();

        // Update user workflow history entity.
        $current_time = \Drupal::time()->getCurrentTime('d');    
        $euwh = $this->entityTypeManager->getStorage('user_workflow_history_entity')->create([
          'name' => \Drupal::service('date.formatter')->format($current_time, 'custom', 'd/m/Y H:i:s'),
          'status' => 1,
          'field_user' => $user->id(),
          'field_workflow_status_before' => 'spfp-accept',
          'field_workflow_status_after' => 'gbv-coordination-accept',
        ]);
        $euwh->save();
      }
      if ($user->hasRole('service_provider_focal_point')) {
        // Update the user with new transition.
        $user->activate();
        $user->set('field_transitions', 'gbv-coordination-accept');
        $user->save();

        // Update user workflow history entity.
        $current_time = \Drupal::time()->getCurrentTime('d');    
        $euwh = $this->entityTypeManager->getStorage('user_workflow_history_entity')->create([
          'name' => \Drupal::service('date.formatter')->format($current_time, 'custom', 'd/m/Y H:i:s'),
          'status' => 1,
          'field_user' => $user->id(),
          'field_workflow_status_before' => 'self-register-spfp',
          'field_workflow_status_after' => 'gbv-coordination-accept',
        ]);
        $euwh->save();
      }
    }

    $url = Url::fromRoute('view.user_lists.page_2')->toString();
    $response = new RedirectResponse($url);
    $response->send();
    return $response;
  }

  /**
   * Reject User.
   */
  public function rejectUser(UserInterface $user, Request $request) {

    // Get current user role.
    $roles = \Drupal::currentUser()->getRoles();
    // For SPFP.
    if (in_array('service_provider_focal_point', $roles)) {
      // Make sure this is the right user take action.
      if ($user->hasRole('service_provider_staff')) {
        // Update the user with new transition.
        $user->set('field_transitions', 'spfp-reject');
        $user->save();

        // Update user workflow history entity.
        $current_time = \Drupal::time()->getCurrentTime('d');    
        $euwh = $this->entityTypeManager->getStorage('user_workflow_history_entity')->create([
          'name' => \Drupal::service('date.formatter')->format($current_time, 'custom', 'd/m/Y H:i:s'),
          'status' => 1,
          'field_user' => $user->id(),
          'field_workflow_status_before' => 'self-register-sp-staff',
          'field_workflow_status_after' => 'spfp-reject',
        ]);
        $euwh->save();
      }
    }

    // For GBV Coordination.
    if (in_array('interagency_gbv_coordinator', $roles) || in_array('country_admin', $roles)) {
      // Make sure this is the right user take action.
      if ($user->hasRole('service_provider_staff')) {
        // Update the user with new transition.
        $user->set('field_transitions', 'gbv-coordination-reject');
        $user->save();

        // Update user workflow history entity.
        $current_time = \Drupal::time()->getCurrentTime('d');    
        $euwh = $this->entityTypeManager->getStorage('user_workflow_history_entity')->create([
          'name' => \Drupal::service('date.formatter')->format($current_time, 'custom', 'd/m/Y H:i:s'),
          'status' => 1,
          'field_user' => $user->id(),
          'field_workflow_status_before' => 'spfp-accept',
          'field_workflow_status_after' => 'gbv-coordination-reject',
        ]);
        $euwh->save();
      }
      if ($user->hasRole('service_provider_focal_point')) {
        // Update the user with new transition.
        $user->set('field_transitions', 'gbv-coordination-reject');
        $user->save();

        // Update user workflow history entity.
        $current_time = \Drupal::time()->getCurrentTime('d');    
        $euwh = $this->entityTypeManager->getStorage('user_workflow_history_entity')->create([
          'name' => \Drupal::service('date.formatter')->format($current_time, 'custom', 'd/m/Y H:i:s'),
          'status' => 1,
          'field_user' => $user->id(),
          'field_workflow_status_before' => 'self-register-spfp',
          'field_workflow_status_after' => 'gbv-coordination-reject',
        ]);
        $euwh->save();
      }
    }

    $url = Url::fromRoute('view.user_lists.page_2')->toString();
    $response = new RedirectResponse($url);
    $response->send();
    return $response;
  }

}
