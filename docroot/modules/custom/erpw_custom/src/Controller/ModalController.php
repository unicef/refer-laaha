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
class ModalController extends ControllerBase {

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
   * Callback for opening the modal form.
   */
  public function deleteServiceTypeNode($id) {
    $response = new AjaxResponse();
    $node = $this->entityManager->getStorage('node')->load($id);
    if (!empty($node)) {
      $node->delete();
    }
    // Get the modal form using the form builder.
    $modal_form = $this->formBuilder->getForm('Drupal\erpw_custom\Form\ModalPopup');

    // Add an AJAX command to open a modal dialog with the form as the content.
    $response->addCommand(new OpenModalDialogCommand('', $modal_form, ['width' => '400']));

    return $response;
  }

  /**
   * Dashboard page.
   */
  public function dashboardContent() {
    return [
      '#markup' => '',
    ];
  }

  /**
   * Active user.
   */
  public function activeUser(AccountInterface $user, Request $request) {
    $user->activate();
    $user->save();
    $url = Url::fromRoute('view.user_lists.page_2')->toString();
    $response = new RedirectResponse($url);
    $response->send();
    return $response;
  }

}
