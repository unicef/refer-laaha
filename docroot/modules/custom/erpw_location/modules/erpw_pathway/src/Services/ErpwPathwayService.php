<?php

namespace Drupal\erpw_pathway\Services;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\erpw_location\LocationService;

/**
 * ErpwPathway Service Class.
 */
class ErpwPathwayService {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected static $entityTypeManager;

  /**
   * Array of levels.
   *
   * @var levelLabel
   */
  protected static $levelLabel = [];

  /**
   * A LocationService instance.
   *
   * @var Drupal\erpw_location\LocationService
   */
  protected static $locationService;

  /**
   * Max level.
   *
   * @var MAX_LEVEL
   */
  const MAX_LEVEL = 4;

  /**
   * Language of the site.
   *
   * @var language
   */
  protected static $language = 'en';

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
    LocationService $location_service,
    LanguageManagerInterface $language) {
    self::$entityTypeManager = $entity_type_manager;
    self::$locationService = $location_service;
    self::$language = $language->getCurrentLanguage()->getId();
  }

  /**
   * Location Form Callback.
   *
   * @param array $form
   *   Form Array.
   * @param mixed $form_state
   *   Form State.
   * @param array $parent_list
   *   Array Form parent list.
   * @param array $ptids
   *   Array parent list.
   *
   * @return array
   *   Array of form element.
   */
  public function getLocationForm(array $form, $form_state, array $parent_list = [], array $ptids = []) {
    $form['#cache']['max-age'] = 0;
    $form['#attached']['library'][] = 'erpw_pathway/erpw_pathway';
    // Build location Form.
    $location_entities = self::$entityTypeManager->getStorage('location')->loadByProperties(
      ['type' => 'country', 'status' => 1]);
    if (empty($location_entities)) {
      return $form;
    }
    self::$levelLabel = $this->getLables($location_entities);
    $location_options = ['' => $this->t("Select country")];
    $form_state->setRebuild(TRUE);
    $child_location = [];
    foreach ($location_entities as $location) {
      $id = (!$location->get('field_location_taxonomy_term')->isEmpty()) ?
      $location->get('field_location_taxonomy_term')->getValue()[0]['target_id'] : '';
      $location_options[$id] = $location->get('name')->getValue()[0]['value'];
      if (isset($parent_list[0])) {
        $id = $parent_list[0];
      }
      $child = $this->getChildLocation($id, $location, $form, $form_state, $parent_list, $ptids);
      if (count($child) > count($child_location)) {
        $child_location = $child;
      }
    }
    if ($country_id = $form_state->getValue('level_0')) {
      $level_count = count(self::$levelLabel[$country_id]);
      // Not required to unset the ajax callback.
      // unset($child_location['level_' . $level_count]['#ajax']);.
      $child_location['level_' . $level_count]['#attributes']['class'][] = 'add_multiple';
    }
    $default_value = $parent_list[0] ?? '';
    $is_disabled = '';
    if ($ptids) {
      $is_disabled = in_array($default_value, $ptids) ? 'disabled' : '';
    }
    $label = $this->t('Select Country');
    $form['location']['level_0'] = $this->childLocationForm('0', $label, $location_options, $default_value, '', '');
    $route_name = \Drupal::routeMatch()->getRouteName();
    if ($route_name == 'erpw_custom.sign_up_form') {
      $form['location']['level_0']['#attributes']['class'][] = 'disable-country-select-default';
    }
    $form['location'] += $child_location;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getDetails($form, $form_state, $level) {
    $country_id = $form_state->getValue('level_0');
    $form['location']['level_' . $level]['#title'] = self::$levelLabel[$country_id][$level];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getLocationDetail(&$form, $form_state) {
    $level = $form_state->getTriggeringElement()['#level'];
    if ($form_state->getTriggeringElement()['#value']) {
      $form = $this->getDetails($form, $form_state, $level);
    }

    return $form['location']['level_' . $level];
  }

  /**
   * Get child location array.
   *
   * @param int $id
   *   Country Id.
   * @param object $location
   *   Location Array.
   * @param array $form
   *   Array of form element.
   * @param mixed $form_state
   *   Form state.
   * @param array $parent_list
   *   List of parents.
   * @param array $ptids
   *   Array of user location parents.
   *
   * @return array
   *   Return Child location.
   */
  protected function getChildLocation($id, $location, array $form, $form_state, array $parent_list = [], array $ptids = []) {

    for ($i = 1; $i <= self::MAX_LEVEL; $i++) {
      $level_id = 'level_' . $i;
      $childs = ['0' => $this->t("Select") . ' ' . $i . ' ' . $this->t("Label")];
      $class = '';
      if (!isset($location->get($level_id)->getValue()[0])) {
        continue;
      }
      $location_tid = !empty($parent_list[$i - 1]) ? $parent_list[$i - 1] : $form_state->getValue('level_' . ($i - 1));
      // Not sure why this would be needed but see #1367
      // An alternative should be to use $ptids (which is all numeric) instead
      // of $parent_list which can have a strange array in array for the fourth item.
      if (is_array($location_tid)) {
        $location_tid = current($location_tid);
      }
      if ($location_tid) {
        $childs += self::$locationService->getChildrenByTid($location_tid);
      }
      $class = (count($childs) <= 1) ? 'hidden' : '';
      $default_value = $parent_list[$i] ?? '';
      $is_disabled = '';
      if ($ptids) {
        $is_disabled = in_array($default_value, $ptids) ? 'disabled' : '';
      }
      if ($i == self::MAX_LEVEL) {
        $is_disabled = 'readonly';
        unset($childs[0]);
      }
      $child_location['level_' . $i] = $this->childLocationForm($i, self::$levelLabel[$id][$i], $childs, $default_value, $class, $is_disabled);
    }

    return $child_location;
  }

  /**
   * Create child location form.
   */
  protected function childLocationForm($counter, $label, array $options, $default_value, $class = '', $is_disabled = '') {
    return [
      '#prefix' => '<div id="location-level-' . $counter . '" class="' . $class . '">',
      '#suffix' => '</div>',
      '#options' => $options,
      '#type' => 'select',
      '#required' => ($counter == 0) ? TRUE : FALSE,
      '#validated' => TRUE,
      '#title' => $label,
      '#attributes' => [
        'class' => ['loc-dropdown'],
        'data-level' => ($counter + 1),
        $is_disabled => $is_disabled,
      ],
      '#multiple' => ($counter == self::MAX_LEVEL) ? TRUE : FALSE,
      '#level' => ($counter + 1),
      '#default_value' => $default_value,
      '#ajax' => [
        'callback' => [$this, 'getLocationDetail'],
        'event' => 'change',
        'method' => 'replace',
        'wrapper' => 'location-level-' . ($counter + 1),
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Please wait...'),
        ],
      ],
      '#attached' => [
        'drupalSettings' => [
          'leveldetails' => [
            'level' => ($counter + 1),
            'levelcount' => self::MAX_LEVEL,
          ],
        ],
      ],
    ];
  }

  /**
   * Get Lables.
   */
  protected function getLables($location_entities) {
    if (empty($location_entities)) {
      return [];
    }
    foreach ($location_entities as $location) {
      $id = (!$location->get('field_location_taxonomy_term')->isEmpty()) ?
      $location->get('field_location_taxonomy_term')->getValue()[0]['target_id'] : '';
      if ($id) {
        for ($i = 1; $i <= self::MAX_LEVEL; $i++) {
          $level_id = 'level_' . $i;
          if ($location->isTranslatable() && $location->hasTranslation(self::$language)) {
            $location = $location->getTranslation(self::$language);
          }
          $labels[$id][$i] = $location->get($level_id)->getValue()[0]['value'] ?? '';
        }
      }
    }

    return $labels;
  }

}
