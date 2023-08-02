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
  public function acceptUser(AccountInterface $user, Request $request) {

    $url = Url::fromRoute('view.user_lists.page_2')->toString();
    $response = new RedirectResponse($url);
    $response->send();
    return $response;
  }

  /**
   * Reject User.
   */
  public function rejectUser(AccountInterface $user, Request $request) {

    $url = Url::fromRoute('view.user_lists.page_2')->toString();
    $response = new RedirectResponse($url);
    $response->send();
    return $response;
  }

}
