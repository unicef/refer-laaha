<?php

namespace Drupal\erpw_webform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;

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
      $user->delete();
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
      $user->delete();
      // Return a JSON response with a success message.
      return new JsonResponse([
        'status' => 'success',
        'message' => $this->t('The user has been deleted successfully.'),
      ]);
    }
    elseif (in_array('service_provider_focal_point', $currentUserRole) && in_array('service_provider_staff', $userRole)) {
      // Perform user deletion.
      $user->delete();
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

}
