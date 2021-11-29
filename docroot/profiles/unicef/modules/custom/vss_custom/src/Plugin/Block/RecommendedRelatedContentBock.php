<?php

namespace Drupal\vss_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'RecommendedRelatedContentBock' block.
 *
 * @Block(
 *  id = "recommended_related_content_bock",
 *  admin_label = @Translation("Recommended related content bock"),
 * )
 */
class RecommendedRelatedContentBock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * Drupal\Core\Language\LanguageManagerInterface.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->requestStack = $container->get('request_stack')->getCurrentRequest();
    $instance->domainNegotiator = $container->get('domain.negotiator');
    $instance->languageManager = $container->get('language_manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    if ($this->requestStack->attributes->has('node')) {
      $node = $this->requestStack->attributes->get('node');
      if ($node->hasField('field_sub_category') && !empty($node->get('field_sub_category')->first())) {
        $target_id = $node->get('field_sub_category')->target_id;
        $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($target_id);
        if ($term->parent->target_id) {
        $parent = $this->entityTypeManager->getStorage('taxonomy_term')->load($term->parent->target_id);
          if ($parent->hasField('field_tags') && !empty($parent->get('field_tags')->first())) {
            foreach ($parent->get('field_tags')->getValue() as $value) {
              $ids[] = $value['target_id'];
            }
          }
        }
      }
    }
    $build = [];
    $build['#theme'] = 'recommended_related_content_block';
    if (empty($ids)) {
      return $build;
    }
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->condition('field_tags', $ids, 'IN');
    $query->condition('type', ['article', 'video'], 'IN');
    $query->condition('status', 1);
    $query->range(0, 3);
    $query->condition('langcode', $this->languageManager->getCurrentLanguage()->getId());
    $query->condition('field_domain_access', $this->domainNegotiator->getActiveDomain()->id());
    $query->addTag('sort_by_random');
    $result = $query->execute();
    if ($result) {
      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($result);
      $data = [];
      foreach ($nodes as $viewNode) {
        $data[$viewNode->id()]['title'] = $viewNode->getTitle();
        if ($viewNode->hasField('field_thumbnail_image') && !empty($viewNode->get('field_thumbnail_image')->first())) {
          $fid = $viewNode->get('field_thumbnail_image')->target_id;
          $file = $this->entityTypeManager->getStorage('file')->load($fid);
          if ($file) {
            $data[$viewNode->id()]['uri'] = $file->get('uri')->value;
          }
          $data[$viewNode->id()]['alt'] = $viewNode->get('field_thumbnail_image')->alt;
        }
        $output = $data;
      }
    }
    $build['#content'] = $output;
    return $build;
  }

}
