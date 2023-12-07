<?php

namespace Drupal\erpw_custom\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Url;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
        \Drupal::messenger()->addMessage($this->t('You are not authorized to perform this action.'));
        $response = new RedirectResponse($url);
        $response->send();
        return $response;
      }
    }

    // For GBVFP.
    if (in_array('gbv_focal_point', $roles)) {
      // Make sure this is the right user take action.
      if ($user->hasRole('service_provider_staff') && $user->get('field_transitions')->getString() == 'self-register-sp-staff') {
        // Update the user with new transition.
        $user->set('field_transitions', 'gbvfp-accept');
        $user->save();

        // Update user workflow history entity.
        $current_time = \Drupal::time()->getCurrentTime('d');
        $euwh = $this->entityTypeManager->getStorage('user_workflow_history_entity')->create([
          'name' => \Drupal::service('date.formatter')->format($current_time, 'custom', 'd/m/Y H:i:s'),
          'status' => 1,
          'field_user' => $user->id(),
          'field_workflow_status_before' => 'self-register-sp-staff',
          'field_workflow_status_after' => 'gbvfp-accept',
        ]);
        $euwh->save();
      }
      else {
        $url = Url::fromRoute('entity.user.canonical', ['user' => $user->id()])->toString();
        \Drupal::messenger()->addMessage($this->t('You are not authorized to perform this action.'));
        $response = new RedirectResponse($url);
        $response->send();
        return $response;
      }
    }

    // For GBV Coordination.
    if (in_array('interagency_gbv_coordinator', $roles) || in_array('country_admin', $roles)) {
      // Make sure this is the right user take action.
      if ($user->hasRole('service_provider_staff')) {
        if ($user->get('field_transitions')->getString() == 'spfp-accept') {
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
        if ($user->get('field_transitions')->getString() == 'gbvfp-accept') {
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
            'field_workflow_status_before' => 'gbvfp-accept',
            'field_workflow_status_after' => 'gbv-coordination-accept',
          ]);
          $euwh->save();
        }
        if ($user->get('field_transitions')->getString() == 'spfp-register-sp-staff') {
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
            'field_workflow_status_before' => 'spfp-register-sp-staff',
            'field_workflow_status_after' => 'gbv-coordination-accept',
          ]);
          $euwh->save();
        }
        if ($user->get('field_transitions')->getString() == 'gbvfp-register-sp-staff') {
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
            'field_workflow_status_before' => 'gbvfp-register-sp-staff',
            'field_workflow_status_after' => 'gbv-coordination-accept',
          ]);
          $euwh->save();
        }
      }

      // For SP FP users.
      elseif ($user->hasRole('service_provider_focal_point')) {
        if ($user->get('field_transitions')->getString() == 'self-register-spfp') {
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
        if ($user->get('field_transitions')->getString() == 'spfp-register-spfp') {
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
            'field_workflow_status_before' => 'spfp-register-spfp',
            'field_workflow_status_after' => 'gbv-coordination-accept',
          ]);
          $euwh->save();
        }
        if ($user->get('field_transitions')->getString() == 'gbvfp-register-spfp') {
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
            'field_workflow_status_before' => 'gbvfp-register-spfp',
            'field_workflow_status_after' => 'gbv-coordination-accept',
          ]);
          $euwh->save();
        }
      }

      // For GBV FP Users.
      elseif ($user->hasRole('gbv_focal_point')) {
        if ($user->get('field_transitions')->getString() == 'self-register-gbvfp') {
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
            'field_workflow_status_before' => 'self-register-gbvfp',
            'field_workflow_status_after' => 'gbv-coordination-accept',
          ]);
          $euwh->save();
        }
        if ($user->get('field_transitions')->getString() == 'gbvfp-register-gbvfp') {
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
            'field_workflow_status_before' => 'gbvfp-register-gbvfp',
            'field_workflow_status_after' => 'gbv-coordination-accept',
          ]);
          $euwh->save();
        }
      }
      else {
        $url = Url::fromRoute('entity.user.canonical', ['user' => $user->id()])->toString();
        \Drupal::messenger()->addMessage($this->t('You are not authorized to perform this action.'));
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
        $time = time();
        // Email update.
        $current_email = $user->getEmail();
        $new_email = $current_email . '_' . $time;
        $user->setEmail($new_email);
        // Username update.
        $current_username = $user->getAccountName();
        $new_username = $current_username . '_' . $time;
        $user->setUsername($new_username);
        // Softdelete flag.
        $user->set('field_soft_delete', 1);
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
        \Drupal::messenger()->addMessage($this->t('You are not authorized to perform this action.'));
        $response = new RedirectResponse($url);
        $response->send();
        return $response;
      }
    }

    // For GBVFP.
    if (in_array('gbv_focal_point', $roles)) {
      // Make sure this is the right user take action.
      if ($user->hasRole('service_provider_staff') && $user->get('field_transitions')->getString() == 'self-register-sp-staff') {
        // Update the user with new transition.
        $user->set('field_transitions', 'gbvfp-reject');
        $time = time();
        // Email update.
        $current_email = $user->getEmail();
        $new_email = $current_email . '_' . $time;
        $user->setEmail($new_email);
        // Username update.
        $current_username = $user->getAccountName();
        $new_username = $current_username . '_' . $time;
        $user->setUsername($new_username);
        // Softdelete flag.
        $user->set('field_soft_delete', 1);
        $user->save();

        // Update user workflow history entity.
        $current_time = \Drupal::time()->getCurrentTime('d');
        $euwh = $this->entityTypeManager->getStorage('user_workflow_history_entity')->create([
          'name' => \Drupal::service('date.formatter')->format($current_time, 'custom', 'd/m/Y H:i:s'),
          'status' => 1,
          'field_user' => $user->id(),
          'field_workflow_status_before' => 'self-register-sp-staff',
          'field_workflow_status_after' => 'gbvfp-reject',
        ]);
        $euwh->save();
      }
      else {
        $url = Url::fromRoute('entity.user.canonical', ['user' => $user->id()])->toString();
        \Drupal::messenger()->addMessage($this->t('You are not authorized to perform this action.'));
        $response = new RedirectResponse($url);
        $response->send();
        return $response;
      }
    }

    // For GBV Coordination.
    if (in_array('interagency_gbv_coordinator', $roles) || in_array('country_admin', $roles)) {
      // Make sure this is the right user take action.
      if ($user->hasRole('service_provider_staff')) {
        if ($user->get('field_transitions')->getString() == 'spfp-accept') {
          // Update the user with new transition.
          $user->set('field_transitions', 'gbv-coordination-reject');
          $time = time();
          // Email update.
          $current_email = $user->getEmail();
          $new_email = $current_email . '_' . $time;
          $user->setEmail($new_email);
          // Username update.
          $current_username = $user->getAccountName();
          $new_username = $current_username . '_' . $time;
          $user->setUsername($new_username);
          // Softdelete flag.
          $user->set('field_soft_delete', 1);
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
        if ($user->get('field_transitions')->getString() == 'gbvfp-accept') {
          // Update the user with new transition.
          $user->set('field_transitions', 'gbv-coordination-reject');
          $time = time();
          // Email update.
          $current_email = $user->getEmail();
          $new_email = $current_email . '_' . $time;
          $user->setEmail($new_email);
          // Username update.
          $current_username = $user->getAccountName();
          $new_username = $current_username . '_' . $time;
          $user->setUsername($new_username);
          // Softdelete flag.
          $user->set('field_soft_delete', 1);
          $user->save();

          // Update user workflow history entity.
          $current_time = \Drupal::time()->getCurrentTime('d');
          $euwh = $this->entityTypeManager->getStorage('user_workflow_history_entity')->create([
            'name' => \Drupal::service('date.formatter')->format($current_time, 'custom', 'd/m/Y H:i:s'),
            'status' => 1,
            'field_user' => $user->id(),
            'field_workflow_status_before' => 'gbvfp-accept',
            'field_workflow_status_after' => 'gbv-coordination-reject',
          ]);
          $euwh->save();
        }
        if ($user->get('field_transitions')->getString() == 'spfp-register-sp-staff') {
          // Update the user with new transition.
          $user->set('field_transitions', 'gbv-coordination-reject');
          $time = time();
          // Email update.
          $current_email = $user->getEmail();
          $new_email = $current_email . '_' . $time;
          $user->setEmail($new_email);
          // Username update.
          $current_username = $user->getAccountName();
          $new_username = $current_username . '_' . $time;
          $user->setUsername($new_username);
          // Softdelete flag.
          $user->set('field_soft_delete', 1);
          $user->save();

          // Update user workflow history entity.
          $current_time = \Drupal::time()->getCurrentTime('d');
          $euwh = $this->entityTypeManager->getStorage('user_workflow_history_entity')->create([
            'name' => \Drupal::service('date.formatter')->format($current_time, 'custom', 'd/m/Y H:i:s'),
            'status' => 1,
            'field_user' => $user->id(),
            'field_workflow_status_before' => 'spfp-register-sp-staff',
            'field_workflow_status_after' => 'gbv-coordination-reject',
          ]);
          $euwh->save();
        }
        if ($user->get('field_transitions')->getString() == 'gbvfp-register-sp-staff') {
          // Update the user with new transition.
          $user->set('field_transitions', 'gbv-coordination-reject');
          $time = time();
          // Email update.
          $current_email = $user->getEmail();
          $new_email = $current_email . '_' . $time;
          $user->setEmail($new_email);
          // Username update.
          $current_username = $user->getAccountName();
          $new_username = $current_username . '_' . $time;
          $user->setUsername($new_username);
          // Softdelete flag.
          $user->set('field_soft_delete', 1);
          $user->save();

          // Update user workflow history entity.
          $current_time = \Drupal::time()->getCurrentTime('d');
          $euwh = $this->entityTypeManager->getStorage('user_workflow_history_entity')->create([
            'name' => \Drupal::service('date.formatter')->format($current_time, 'custom', 'd/m/Y H:i:s'),
            'status' => 1,
            'field_user' => $user->id(),
            'field_workflow_status_before' => 'gbvfp-register-sp-staff',
            'field_workflow_status_after' => 'gbv-coordination-reject',
          ]);
          $euwh->save();
        }
      }
      elseif ($user->hasRole('service_provider_focal_point')) {
        if ($user->get('field_transitions')->getString() == 'self-register-spfp') {
          // Update the user with new transition.
          $user->set('field_transitions', 'gbv-coordination-reject');
          $time = time();
          // Email update.
          $current_email = $user->getEmail();
          $new_email = $current_email . '_' . $time;
          $user->setEmail($new_email);
          // Username update.
          $current_username = $user->getAccountName();
          $new_username = $current_username . '_' . $time;
          $user->setUsername($new_username);
          // Softdelete flag.
          $user->set('field_soft_delete', 1);
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
        if ($user->get('field_transitions')->getString() == 'spfp-register-spfp') {
          // Update the user with new transition.
          $user->set('field_transitions', 'gbv-coordination-reject');
          $time = time();
          // Email update.
          $current_email = $user->getEmail();
          $new_email = $current_email . '_' . $time;
          $user->setEmail($new_email);
          // Username update.
          $current_username = $user->getAccountName();
          $new_username = $current_username . '_' . $time;
          $user->setUsername($new_username);
          // Softdelete flag.
          $user->set('field_soft_delete', 1);
          $user->save();

          // Update user workflow history entity.
          $current_time = \Drupal::time()->getCurrentTime('d');
          $euwh = $this->entityTypeManager->getStorage('user_workflow_history_entity')->create([
            'name' => \Drupal::service('date.formatter')->format($current_time, 'custom', 'd/m/Y H:i:s'),
            'status' => 1,
            'field_user' => $user->id(),
            'field_workflow_status_before' => 'spfp-register-spfp',
            'field_workflow_status_after' => 'gbv-coordination-reject',
          ]);
          $euwh->save();
        }
        if ($user->get('field_transitions')->getString() == 'gbvfp-register-spfp') {
          // Update the user with new transition.
          $user->set('field_transitions', 'gbv-coordination-reject');
          $time = time();
          // Email update.
          $current_email = $user->getEmail();
          $new_email = $current_email . '_' . $time;
          $user->setEmail($new_email);
          // Username update.
          $current_username = $user->getAccountName();
          $new_username = $current_username . '_' . $time;
          $user->setUsername($new_username);
          // Softdelete flag.
          $user->set('field_soft_delete', 1);
          $user->save();

          // Update user workflow history entity.
          $current_time = \Drupal::time()->getCurrentTime('d');
          $euwh = $this->entityTypeManager->getStorage('user_workflow_history_entity')->create([
            'name' => \Drupal::service('date.formatter')->format($current_time, 'custom', 'd/m/Y H:i:s'),
            'status' => 1,
            'field_user' => $user->id(),
            'field_workflow_status_before' => 'gbvfp-register-spfp',
            'field_workflow_status_after' => 'gbv-coordination-reject',
          ]);
          $euwh->save();
        }
      }

      elseif ($user->hasRole('service_provider_focal_point')) {
        if ($user->get('field_transitions')->getString() == 'self-register-gbvfp') {
          // Update the user with new transition.
          $user->set('field_transitions', 'gbv-coordination-reject');
          $time = time();
          // Email update.
          $current_email = $user->getEmail();
          $new_email = $current_email . '_' . $time;
          $user->setEmail($new_email);
          // Username update.
          $current_username = $user->getAccountName();
          $new_username = $current_username . '_' . $time;
          $user->setUsername($new_username);
          // Softdelete flag.
          $user->set('field_soft_delete', 1);
          $user->save();

          // Update user workflow history entity.
          $current_time = \Drupal::time()->getCurrentTime('d');
          $euwh = $this->entityTypeManager->getStorage('user_workflow_history_entity')->create([
            'name' => \Drupal::service('date.formatter')->format($current_time, 'custom', 'd/m/Y H:i:s'),
            'status' => 1,
            'field_user' => $user->id(),
            'field_workflow_status_before' => 'self-register-gbvfp',
            'field_workflow_status_after' => 'gbv-coordination-reject',
          ]);
          $euwh->save();
        }
        if ($user->get('field_transitions')->getString() == 'gbvfp-register-gbvfp') {
          // Update the user with new transition.
          $user->set('field_transitions', 'gbv-coordination-reject');
          $time = time();
          // Email update.
          $current_email = $user->getEmail();
          $new_email = $current_email . '_' . $time;
          $user->setEmail($new_email);
          // Username update.
          $current_username = $user->getAccountName();
          $new_username = $current_username . '_' . $time;
          $user->setUsername($new_username);
          // Softdelete flag.
          $user->set('field_soft_delete', 1);
          $user->save();

          // Update user workflow history entity.
          $current_time = \Drupal::time()->getCurrentTime('d');
          $euwh = $this->entityTypeManager->getStorage('user_workflow_history_entity')->create([
            'name' => \Drupal::service('date.formatter')->format($current_time, 'custom', 'd/m/Y H:i:s'),
            'status' => 1,
            'field_user' => $user->id(),
            'field_workflow_status_before' => 'gbvfp-register-gbvfp',
            'field_workflow_status_after' => 'gbv-coordination-reject',
          ]);
          $euwh->save();
        }
      }

      else {
        $url = Url::fromRoute('entity.user.canonical', ['user' => $user->id()])->toString();
        \Drupal::messenger()->addMessage($this->t('You are not authorized to perform this action.'));
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
