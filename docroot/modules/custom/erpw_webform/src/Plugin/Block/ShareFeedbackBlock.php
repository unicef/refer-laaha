<?php

namespace Drupal\erpw_webform\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a custom feedback block.
 *
 * @Block(
 *   id = "share_feedback_block",
 *   admin_label = @Translation("Share Feedback Block"),
 * )
 */
class ShareFeedbackBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The RouteMatch service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The Request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a Drupalist object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user service.
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        RouteMatchInterface $routeMatch,
        RequestStack $requestStack,
        AccountInterface $current_user
    ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $routeMatch;
    $this->requestStack = $requestStack;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
          $configuration,
          $plugin_id,
          $plugin_definition,
          $container->get('current_route_match'),
          $container->get('request_stack'),
          $container->get('current_user')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $user_role = $this->currentUser->getRoles();
    // Feedback block should only be visible to the authenticated users.
    if ($this->currentUser->isAuthenticated() && !in_array('txb_service_viewer', $user_role)) {
      $requestUri = $this->requestStack->getCurrentRequest()->getUri();
      $blockTitle = str_contains($requestUri, 'ratings-by-service-type') ? t('Improvise your Service Information') : t('Feedback Reminder');
      $blockDescription = str_contains($requestUri, 'ratings-by-service-type') ? t('Help other service providers reach you without any hassle by updating information as per feedback.') : t('Help Service Providers to improvise their Service Provision information with your valuable feedback.');
      $languageCode = \Drupal::languageManager()->getCurrentLanguage()->getId();

      // @todo enable Block cache and move the markup to twig template. - Done
      return [
        '#theme' => 'share_feedback_block',
        '#blockTitle' => $blockTitle,
        '#blockDescription' => $blockDescription,
        '#langCode' => $languageCode,
        '#cache' => [
          'contexts' => ['user.roles:authenticated'],
        ],
      ];
    }
    else {
      return [];
    }
  }

}
