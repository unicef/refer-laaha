<?php

namespace Drupal\erpw_webform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Deletes user.
 */
class DeleteUserController extends ControllerBase {

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new DeleteUserController object.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(AccountProxyInterface $current_user, EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger, MessengerInterface $messenger) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('logger.factory')->get('user'),
      $container->get('messenger')
    );
  }

  /**
   * Delete user and display delete success message.
   *
   * @param mixed $id
   *   The user entity to be deleted.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response indicating the status of the delete operation.
   */
  public function deleteUser(mixed $id) {
    // Load current user role.
    $currentUser = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    $currentUserRole = $currentUser->getRoles();

    // Load role of the user to be deleted.
    $user = $this->entityTypeManager->getStorage('user')->load($id);
    $userRole = $user->getRoles();

    if (in_array('anonymous', $currentUserRole) || in_array('service_provider_staff', $currentUserRole)) {
      return new JsonResponse([
        'status' => 'error',
        'message' => $this->t("You don't have the permission to delete user."),
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
        'message' => $this->t("You don't have the permission to delete user."),
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
        'message' => $this->t("You don't have the permission to delete user."),
      ]);
    }
  }

  /**
   * Define the function to safely remove a user.
   */
  public function custom_remove_user($uid) {

    $user = $this->entityTypeManager->getStorage('user')->load($uid);
    $admin_user = $this->entityTypeManager->getStorage('user')->load(1);

    // Check if the user exists and is not anonymous.
    if ($user && !$user->isAnonymous()) {
      // Check if the current user has permission to cancel users.
      if ($this->currentUser()->hasPermission('cancel users by role')) {
        // Fetch all nodes of the user.
        $query = $this->entityTypeManager->getStorage('node')->getQuery()
          ->condition('uid', $uid);
        $nids = $query->accessCheck(FALSE)->execute();

        // Reassign the nodes to the anonymous user.
        if (!empty($nids)) {
          $node_storage = $this->entityTypeManager->getStorage('node');
          foreach ($nids as $nid) {
            /** @var \Drupal\node\Entity\Node $node */
            $node = $node_storage->load($nid);
            $node->setOwnerId($admin_user->id());
            $node->save();
          }
        }

        // Load all webforms created by the user being deleted.
        $query = $this->entityTypeManager->getStorage('webform')->getQuery()
          ->condition('uid', $uid);
        $webform_ids = $query->accessCheck(FALSE)->execute();

        // Reassign the webforms to the anonymous user.
        if (!empty($webform_ids)) {
          foreach ($webform_ids as $webform_id) {
            /** @var \Drupal\webform\Entity\Webform $webform */
            $webform = $this->entityTypeManager->getStorage('webform')->load($webform_id);
            $webform->setOwnerId($admin_user->id());
            $webform->save();
          }
        }

        // Load all webform submissions created by the user being deleted.
        $query = $this->entityTypeManager->getStorage('webform_submission')->getQuery()
          ->condition('uid', $uid);
        $submission_ids = $query->accessCheck(FALSE)->execute();

        // Reassign the webform submissions to the anonymous user.
        if (!empty($submission_ids)) {
          foreach ($submission_ids as $submission_id) {
            /** @var \Drupal\webform\Entity\WebformSubmission $submission */
            $submission = $this->entityTypeManager->getStorage('webform_submission')->load($submission_id);
            $submission->setOwnerId($admin_user->id());
            $submission->save();
          }
        }

        $user->delete();

        // Log the action.
        $this->logger->notice('User %name (%uid) has been removed.', [
          '%name' => $user->getDisplayName(),
          '%uid' => $user->id(),
        ]);

      }
      else {
        // If the current user doesn't have permission, throw an exception or handle it appropriately.
        $this->messenger->addError('You do not have permission to remove users.');
      }
    }
    else {
      // If the user doesn't exist or is anonymous, throw an exception or handle it appropriately.
      $this->messenger->addError('User not found or cannot remove anonymous users.');
    }
  }

}
