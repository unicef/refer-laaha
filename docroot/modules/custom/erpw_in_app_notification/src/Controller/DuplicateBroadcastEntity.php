<?php

namespace Drupal\erpw_in_app_notification\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Returns responses for eRPW In-App Notification routes.
 */
class DuplicateBroadcastEntity extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The controller constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RequestStack $request_stack) {
    $this->entityTypeManager = $entity_type_manager;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('request_stack')
    );
  }

  /**
   * Builds the response.
   */
  public function duplicate($id) {
    // Duplicate the entity.
    $duplicatedEntity = $id->createDuplicate();
    // Save the duplicated entity.
    $duplicatedEntity->save();
    // Redirect to the edit page of the duplicated entity.
    $url = Url::fromRoute('entity.broadcast_notification_entity.edit_form', ['broadcast_notification_entity' => $duplicatedEntity->id()], ['query' => ['duplicate' => 'TRUE']]);
    $response = new RedirectResponse($url->toString());
    $response->send();
    return;
  }

}
