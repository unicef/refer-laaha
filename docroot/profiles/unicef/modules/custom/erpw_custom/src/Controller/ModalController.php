<?php

namespace Drupal\erpw_custom\Controller;

use Drupal\node\Entity\Node;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * The ModalFormController constructor.
   *
   * @param \Drupal\Core\Form\FormBuilder $form_builder
   *   The form builder.
   */
  public function __construct(FormBuilder $form_builder) {
    $this->formBuilder = $form_builder;
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
      $container->get('form_builder')
    );
  }

  /**
   * Callback for opening the modal form.
   */
  public function deleteServiceTypeNode($id) {
    $response = new AjaxResponse();
    $node = Node::load($id);
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

}
