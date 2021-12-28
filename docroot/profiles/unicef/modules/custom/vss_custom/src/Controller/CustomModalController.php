<?php

namespace Drupal\vss_custom\Controller;

use Drupal\block\Entity\Block;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom Modal Controller.
 */
class CustomModalController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function modal() {
    $options = [
      'dialogClass' => 'popup-dialog-class',
      'width' => '50%',
    ];

    $bid = ['viewsexposedfilterblock', 'views_block__top_viewed_nodes_block',
      'categoriespopupblock',
    ];
    foreach ($bid as $val) {
      $block = Block::load($val);
      $build[] = $this->entityTypeManager()
        ->getViewBuilder('block')
        ->view($block);
    }

    $response = new AjaxResponse();
    $response->addCommand(new OpenModalDialogCommand('', $build, $options));
    return $response;
  }

}
