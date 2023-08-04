<?php

namespace Drupal\erpw_custom\Controller;

use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
   * A entityTypeManager instance.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Accept User.
   */
  public function acceptUser(UserInterface $user) {
    // Get current user role.
    $roles = \Drupal::currentUser()->getRoles();
    // For SPFP.
    if (in_array('service_provider_focal_point', $roles)) {
      // Make sure this is the right user take action.
      if ($user->hasRole('service_provider_staff') && $user->get('field_transitions')->getString() == 'self-register-sp-staff') {
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
      else {
        $url = Url::fromRoute('entity.user.canonical', ['user' => $user->id()])->toString();
        \Drupal::messenger()->addMessage($this->t('You are not autherzied to perform this action.'));
        $response = new RedirectResponse($url);
        $response->send();
        return $response;
      }
    }

    // For GBV Coordination.
    if (in_array('interagency_gbv_coordinator', $roles) || in_array('country_admin', $roles)) {
      // Make sure this is the right user take action.
      if ($user->hasRole('service_provider_staff') && $user->get('field_transitions')->getString() == 'spfp-accept') {
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
      else {
        $url = Url::fromRoute('entity.user.canonical', ['user' => $user->id()])->toString();
        \Drupal::messenger()->addMessage($this->t('You are not autherzied to perform this action.'));
        $response = new RedirectResponse($url);
        $response->send();
        return $response;
      }
      if ($user->hasRole('service_provider_focal_point') && $user->get('field_transitions')->getString() == 'self-register-spfp') {
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
      else {
        $url = Url::fromRoute('entity.user.canonical', ['user' => $user->id()])->toString();
        \Drupal::messenger()->addMessage($this->t('You are not autherzied to perform this action.'));
        $response = new RedirectResponse($url);
        $response->send();
        return $response;
      }
    }

    $url = Url::fromRoute('view.user_lists.page_2')->toString();
    \Drupal::messenger()->addMessage($this->t('Successfully accepted the user.'));
    $response = new RedirectResponse($url);
    $response->send();
    return $response;
  }

  /**
   * Reject User.
   */
  public function rejectUser(UserInterface $user) {

    // Get current user role.
    $roles = \Drupal::currentUser()->getRoles();
    // For SPFP.
    if (in_array('service_provider_focal_point', $roles)) {
      // Make sure this is the right user take action.
      if ($user->hasRole('service_provider_staff') && $user->get('field_transitions')->getString() == 'self-register-sp-staff') {
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
      else {
        $url = Url::fromRoute('entity.user.canonical', ['user' => $user->id()])->toString();
        \Drupal::messenger()->addMessage($this->t('You are not autherzied to perform this action.'));
        $response = new RedirectResponse($url);
        $response->send();
        return $response;
      }
    }

    // For GBV Coordination.
    if (in_array('interagency_gbv_coordinator', $roles) || in_array('country_admin', $roles)) {
      // Make sure this is the right user take action.
      if ($user->hasRole('service_provider_staff') && $user->get('field_transitions')->getString() == 'spfp-accept') {
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
      else {
        $url = Url::fromRoute('entity.user.canonical', ['user' => $user->id()])->toString();
        \Drupal::messenger()->addMessage($this->t('You are not autherzied to perform this action.'));
        $response = new RedirectResponse($url);
        $response->send();
        return $response;
      }
      if ($user->hasRole('service_provider_focal_point') && $user->get('field_transitions')->getString() == 'self-register-spfp') {
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
      else {
        $url = Url::fromRoute('entity.user.canonical', ['user' => $user->id()])->toString();
        \Drupal::messenger()->addMessage($this->t('You are not autherzied to perform this action.'));
        $response = new RedirectResponse($url);
        $response->send();
        return $response;
      }
    }

    $url = Url::fromRoute('view.user_lists.page_2')->toString();
    \Drupal::messenger()->addMessage($this->t('Successfully rejected the user.'));
    $response = new RedirectResponse($url);
    $response->send();
    return $response;
  }

}
