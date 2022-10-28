<?php

namespace Drupal\erpw_entity_autocomplete\Controller;

use Drupal\system\Controller\EntityAutocompleteController;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\erpw_entity_autocomplete\RpwEntityAutocompleteMatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * {@inheritdoc}
 */
class RpwEntityAutocompleteController extends EntityAutocompleteController {

  /**
   * The autocomplete matcher for entity references.
   *
   * @var matcher
   */
  protected $matcher;

  /**
   * {@inheritdoc}
   */
  public function __construct(RpwEntityAutocompleteMatcher $matcher, KeyValueStoreInterface $key_value) {
    $this->matcher = $matcher;
    $this->keyValue = $key_value;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('erpw_entity_autocomplete.autocomplete_matcher'),
      $container->get('keyvalue')->get('entity_autocomplete')
    );
  }

}
