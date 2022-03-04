<?php

namespace Drupal\vss_custom\Controller;

use Drupal\node\NodeInterface;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RelatedContentAjaxController.
 *
 * Ajax calls to get related content.
 */
class RelatedContentAjaxController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Drupal\domain\DomainNegotiatorInterface definition.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * Drupal\language\ConfigurableLanguageManagerInterface definition.
   *
   * @var \Drupal\language\ConfigurableLanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->requestStack = $container->get('request_stack');
    $instance->domainNegotiator = $container->get('domain.negotiator');
    $instance->languageManager = $container->get('language_manager');
    return $instance;
  }

  /**
   * Getdata.
   *
   * @return string
   *   Return Hello string.
   */
  public function getData(NodeInterface $node) {
    if ($node->hasField('field_sub_category')) {
      foreach ($node->get('field_sub_category')->getValue() as $value) {
        $target_id = $value['target_id'];
        $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($target_id);
        if ($term->parent->target_id) {
          $parent = $this->entityTypeManager->getStorage('taxonomy_term')->load($term->parent->target_id);
          if ($parent->hasField('field_tags')) {
            foreach ($parent->get('field_tags')->getValue() as $val) {
              if (in_array($val, $node->get('field_tags')->getValue())) {
                $ids[] = $val['target_id'];
              }
            }
          }
        }
      }
    }
    $build = [];
    if (empty($ids)) {
      return $build;
    }
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->condition('field_tags', $ids, 'IN');
    $query->condition('type', ['article', 'video', 'podcast', 'scorm'], 'IN');
    $query->condition('status', 1);
    $query->range(0, 3);
    $query->condition('langcode', $this->languageManager->getCurrentLanguage()->getId());
    $query->condition('field_domain_access', $this->domainNegotiator->getActiveDomain()->id());
    $query->addTag('sort_by_random');
    $result = $query->execute();
    $output = [];
    if ($result) {
      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($result);
      $data = [];
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
      foreach ($nodes as $view_node) {
        if ($view_node->hasTranslation($langcode)) {
          $view_node = $view_node->getTranslation($langcode);
        }
        $data[$view_node->id()]['title'] = $view_node->getTitle();
        if ($view_node->hasField('field_thumbnail_image') && !empty($view_node->get('field_thumbnail_image')->first())) {
          $fid = $view_node->get('field_thumbnail_image')->target_id;
          $file = $this->entityTypeManager->getStorage('file')->load($fid);
          if ($file) {
            $data[$view_node->id()]['uri'] = $file->get('uri')->value;
          }
          $data[$view_node->id()]['alt'] = $view_node->get('field_thumbnail_image')->alt;
        }
        if ($view_node->hasField('field_read_time') && !empty($view_node->get('field_read_time')->first())) {
          $data[$view_node->id()]['read_time'] = $view_node->get('field_read_time')->value;
        }
        $paragraph_video_time = NULL;
        if ($view_node->bundle() == 'video') {
          if ($view_node->hasField('field_content') && !empty($view_node->get('field_content')->first())) {
            $paragraph_id = $view_node->get('field_content')->getValue();
            foreach ($paragraph_id as $content_id) {
              $paragraph_obj = Paragraph::load($content_id['target_id']);
              $paragraph_type = $paragraph_obj->get('type')->getValue()['0']['target_id'];
              if ($paragraph_type == "video") {
                if (!$paragraph_obj->get('field_video_time')->isEmpty()) {
                  $paragraph_video_time = $paragraph_obj->get('field_video_time')->getValue()['0']['value'];
                  break;
                }
              }
            }
          }
        }

        $paragraph_podcast_time = NULL;
        if ($view_node->bundle() == 'podcast') {
          if ($view_node->hasField('field_content') && !empty($view_node->get('field_content')->first())) {
            $paragraph_podcast = $view_node->get('field_content')->getValue();
            foreach ($paragraph_podcast as $content_pod_id) {
              $paragraph_pod_obj = Paragraph::load($content_pod_id['target_id']);
              $paragraph_pod_type = $paragraph_pod_obj->get('type')->getValue()['0']['target_id'];
              if ($paragraph_pod_type == "podcast_audio") {
                if (!$paragraph_pod_obj->get('field_podcast_time')->isEmpty()) {
                  $paragraph_podcast_time = $paragraph_pod_obj->get('field_podcast_time')->getValue()['0']['value'];
                  break;
                }
              }
            }
          }
        }

        $data[$view_node->id()]['link'] = $view_node->toUrl()->toString();
        $data[$view_node->id()]['type'] = $view_node->bundle();
        $data[$view_node->id()]['video_time'] = $paragraph_video_time;
        $data[$view_node->id()]['audio_time'] = $paragraph_podcast_time;
        $output = $data;
      }
    }
    $build['#theme'] = 'recommended_related_content_block';
    $build['#content'] = $output;
    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#recommended-related', render($build)));
    return $response;
  }

}
