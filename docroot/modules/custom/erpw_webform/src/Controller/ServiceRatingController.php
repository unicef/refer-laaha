<?php

namespace Drupal\erpw_webform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\webform\Entity\Webform;
use Drupal\webform\WebformInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles the Service Rating Form actions.
 */
class ServiceRatingController extends ControllerBase {

  /**
   * Publish the service rating webform.
   *
   * @param string $id
   *   The webform entity to be updated.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request entity.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response indicating the status of webform status.
   */
  public function publishServiceRatingForm(string $id, Request $request) {
    // Check if the request is an Ajax request.
    if ($request->isXmlHttpRequest()) {
      $webform = Webform::load($id);
      $form_button_text = "";
      if ($webform) {
        if ($webform->get('status') === "open") {
          $webform->set('status', WebformInterface::STATUS_CLOSED);
          $form_button_text = "Publish";
        }
        else {
          $webform->set('status', WebformInterface::STATUS_OPEN);
          $form_button_text = "Unpublish";
        }
        $webform->save();
        return new JsonResponse([
          'form_button_text' => $form_button_text,
          'message' => $form_button_text === "Publish" ? 'Webform unpublished successfully.' : 'Webform published successfully.',
        ]);
      }
      else {
        return new JsonResponse(['message' => 'Webform not found.'], 404);
      }
    }

    return new JsonResponse(['message' => 'This route does not support non-Ajax requests.'], 400);
  }

  /**
   * Get the status of the service rating webform.
   *
   * @param string $id
   *   The webform entity to be updated.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request entity.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response indicating the status of webform status.
   */
  public function getStatusOfServiceRatingForm(string $id, Request $request) {
    // Check if the request is an Ajax request.
    if ($request->isXmlHttpRequest()) {
      $webform = Webform::load($id);
      if ($webform) {
        return new JsonResponse([
          'form_status' => $webform->get('status') === "open" ? "Unpublish" : "Publish",
          'message' => 'Webform fetching successfully.',
        ]);
      }
      else {
        return new JsonResponse(['message' => 'Webform not found.'], 404);
      }
    }

    return new JsonResponse(['message' => 'This route does not support non-Ajax requests.'], 400);
  }

}
