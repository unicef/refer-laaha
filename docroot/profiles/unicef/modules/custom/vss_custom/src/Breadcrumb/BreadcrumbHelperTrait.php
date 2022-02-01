<?php

namespace Drupal\vss_custom\Breadcrumb;

use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Common trait to provide functions to generate image and breadcrumb link.
 */
trait BreadcrumbHelperTrait {

  /**
   * Generate breadcrumb link.
   */
  public function generateBreadcrumbLink($entity, $type, $route, $parent = FALSE) {
    $image = $this->loadThumbnailImage($entity, $type, $parent);
    $url = Url::fromRoute($route, [$type => $entity->id()]);
    if ($type == 'node') {
      $title = $entity->getTitle();
    }
    else {
      if (isset($entity->get('field_category_short_name')->value) && !empty($entity->get('field_category_short_name')->value)) {
        $title = $entity->get('field_category_short_name')->value;
      }
      else {
        $title = $entity->getName();
      }
    }
    $text = [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#value' => $title,
    ];
    $link_text = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => $image . render($text),
    ];
    $link = Link::fromTextAndUrl($link_text, $url);

    return $link;
  }

  /**
   * Load image.
   */
  public function loadThumbnailImage($entity, $type, $parent = FALSE) {

    switch ($type) {
      case 'taxonomy_term':
        $field = 'field_sub_category_thumbnail';
        if ($parent) {
          $field = 'field_icon';
        }

        if ($entity->hasField($field) && !empty($entity->get($field)->first())) {
          $file = \Drupal::entityTypeManager()->getStorage('file')->load($entity->get($field)->target_id);
          if ($file) {
            $image = [
              '#theme' => 'image_style',
              '#style_name' => 'thumbnail',
              '#uri' => $file->getFileUri(),
            ];
            return \Drupal::service('renderer')->render($image);
          }
        }
        break;

      case 'node':
        if ($entity->hasField('field_thumbnail_image') && !empty($entity->get('field_thumbnail_image')->first())) {
          $file = \Drupal::entityTypeManager()->getStorage('file')->load($entity->get('field_thumbnail_image')->target_id);
          if ($file) {
            $image = [
              '#theme' => 'image_style',
              '#style_name' => 'thumbnail',
              '#uri' => $file->getFileUri(),
            ];
            return \Drupal::service('renderer')->render($image);
          }
        }
        break;
    }
    return '';
  }

}
