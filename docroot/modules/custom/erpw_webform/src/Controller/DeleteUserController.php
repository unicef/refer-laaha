<?php

namespace Drupal\erpw_webform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;

/**
 * Deletes user.
 */
class DeleteUserController extends ControllerBase {

  /**
   * Delete user and display delete success message.
   *
   * @param \Drupal\user\Entity\User $user
   *   The user entity to be deleted.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response indicating the status of the delete operation.
   */
  public function deleteUser($id) {
    // Load current user role.
    $currentUserRole = User::load(\Drupal::currentUser()->id())->getRoles();

    // Load role of the user to be deleted.
    $user = User::load($id);
    $userRole = $user->getRoles();

    if (in_array('anonymous', $currentUserRole) || in_array('service_provider_staff', $currentUserRole)) {
      return new JsonResponse([
        'status' => 'error',
        'message' => $this->t('You dont have the permission to delete user.'),
      ]);
    }
    elseif ((in_array('country_admin', $currentUserRole) || in_array('interagency_gbv_coordinator', $currentUserRole)) && !(in_array('interagency_gbv_coordinator', $userRole) || in_array('country_admin', $userRole) || in_array('administrator', $userRole) || in_array('super_admin', $userRole))) {
      // Perform user deletion.
      $this->custom_remove_user($id);
      // Return a JSON response with a success message.
      return new JsonResponse([
        'status' => 'success',
        'message' => $this->t('The user has been deleted successfully.'),
      ]);
    }
    elseif ((in_array('country_admin', $currentUserRole) || in_array('interagency_gbv_coordinator', $currentUserRole)) && (in_array('interagency_gbv_coordinator', $userRole) || in_array('country_admin', $userRole) || in_array('administrator', $userRole) || in_array('super_admin', $userRole))) {
      return new JsonResponse([
        'status' => 'error',
        'message' => $this->t('You dont have the permission to delete user.'),
      ]);
    }
    elseif (in_array('country_admin', $currentUserRole) || in_array('administrator', $currentUserRole) || in_array('super_admin', $currentUserRole)) {
      // Perform user deletion.
      $this->custom_remove_user($id);
      // Return a JSON response with a success message.
      return new JsonResponse([
        'status' => 'success',
        'message' => $this->t('The user has been deleted successfully.'),
      ]);
    }
    elseif (in_array('service_provider_focal_point', $currentUserRole) && in_array('service_provider_staff', $userRole)) {
      // Perform user deletion.
      $this->custom_remove_user($id);
      // Return a JSON response with a success message.
      return new JsonResponse([
        'status' => 'success',
        'message' => $this->t('The user has been deleted successfully.'),
      ]);
    }
    else {
      return new JsonResponse([
        'status' => 'error',
        'message' => $this->t('You dont have the permission to delete user.'),
      ]);
    }
  }

  /**
   * Define the function to safely remove a user.
   */
  public function custom_remove_user($uid) {

    $user = User::load($uid);
    $admin_user = User::load(1);

    // Check if the user exists and is not anonymous.
    if ($user && !$user->isAnonymous()) {
      // Check if the current user has permission to cancel users.
      if (\Drupal::currentUser()->hasPermission('administer users')) {
        // Fetch all nodes of the user.
        $query = \Drupal::entityQuery('node')
          ->condition('uid', $uid);
        $nids = $query->accessCheck(FALSE)->execute();

        // Reassign the nodes to the anonymous user.
        if (!empty($nids)) {
          $node_storage = \Drupal::entityTypeManager()->getStorage('node');
          foreach ($nids as $nid) {
            /** @var \Drupal\node\Entity\Node $node */
            $node = $node_storage->load($nid);
            $node->setOwnerId($admin_user->id());
            $node->save();
          }
        }

        // Load all webforms created by the user being deleted.
        $query = \Drupal::entityQuery('webform')
          ->condition('uid', $uid);
        $webform_ids = $query->accessCheck(FALSE)->execute();

        // Reassign the webforms to the anonymous user.
        if (!empty($webform_ids)) {
          foreach ($webform_ids as $webform_id) {
            /** @var \Drupal\webform\Entity\Webform $webform */
            $webform = Webform::load($webform_id);
            $webform->setOwnerId($admin_user->id());
            $webform->save();
          }
        }

        // Load all webform submissions created by the user being deleted.
        $query = \Drupal::entityQuery('webform_submission')
          ->condition('uid', $uid);
        $submission_ids = $query->accessCheck(FALSE)->execute();

        // Reassign the webform submissions to the anonymous user.
        if (!empty($submission_ids)) {
          foreach ($submission_ids as $submission_id) {
            /** @var \Drupal\webform\Entity\WebformSubmission $submission */
            $submission = WebformSubmission::load($submission_id);
            $submission->setOwnerId($admin_user->id());
            $submission->save();
          }
        }

        $user->delete();

        // Log the action.
        \Drupal::logger('user')->notice('User %name (%uid) has been removed.', [
          '%name' => $user->getDisplayName(),
          '%uid' => $user->id(),
        ]);

      }
      else {
        // If the current user doesn't have permission, throw an exception or handle it appropriately.
        \Drupal::messenger()->addError('You do not have permission to remove users.');
      }
    }
    else {
      // If the user doesn't exist or is anonymous, throw an exception or handle it appropriately.
      \Drupal::messenger()->addError('User not found or cannot remove anonymous users.');
    }
  }

}
