<?php

namespace Drupal\erpw_webform\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Http\RequestStack;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @var \Drupal\Core\Http\RequestStack
     */
    protected $requestStack;

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
     * @param \Drupal\Core\Http\RequestStack $requestStack
     *   The requets stack service.
     */
    public function __construct(
        array $configuration, 
        $plugin_id, 
        $plugin_definition, 
        RouteMatchInterface $routeMatch,
        RequestStack $requestStack
    ) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->routeMatch = $routeMatch;
        $this->requestStack = $requestStack;
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
            $container->get('request_stack')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function build() {
        $requestUri = $this->requestStack->getCurrentRequest()->getUri();
        $blockTitle = str_contains($requestUri, 'ratings-by-service-type') ? t('Improvise your Service Information') : t('Feedback Reminder');
        $blockDescription = str_contains($requestUri, 'ratings-by-service-type') ? t('Help other service providers reach you without any hassle by updating information as per feedback.') : t('Help Service Providers to improvise their Service Provision information with your valuable feedback.');

        return [
            '#markup' => '<div class="share-feedback-block">
                <div class="feedback-text-block">
                    <div class="feedback-text-header">
                        <p>' . $blockTitle . '</p>
                    </div>
                    <div class="feedback-text">
                        <p>' . $blockDescription . '</p>
                    </div>
                </div>
                <a href="/share-feedback">' . t('Share feedback') . '</a>
            </div>',
        ];
    }
}
