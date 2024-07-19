<?php

namespace Drupal\erpw_webform;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\domain\DomainNegotiatorInterface;
use Drupal\node\Entity\Node;
use Drupal\webform\Entity\Webform;
use Drupal\webform\WebformInterface;

/**
 * Class is used for the service rating services.
 */
class ServiceRatingService {

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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * ServiceRatingService constructor.
   *
   * @param \Drupal\domain\DomainNegotiatorInterface $domain_negotiator
   *   DomainNegotiator object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   Entity Manager Object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Location Manager object.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Current user details.
   */
  public function __construct(
    DomainNegotiatorInterface $domain_negotiator,
    EntityTypeManagerInterface $entity_manager,
    LanguageManagerInterface $language_manager,
    AccountProxyInterface $current_user,
    ) {
    $this->domainNegotiator = $domain_negotiator;
    $this->entityTypeManager = $entity_manager;
    $this->languageManager = $language_manager;
    $this->currentUser = $current_user;
  }

  /**
   * Helper function to load and process entities based on type.
   *
   * @param string $entityType
   *   The entity type to load (e.g., 'node').
   * @param string $bundle
   *   The bundle (content type) of the entities to load.
   * @param string $fieldName
   *   The name of the field containing allowed domains.
   *
   * @return array
   *   An array of processed entity options.
   */
  public function loadAndProcessEntities($entityType, $bundle, $fieldName) {
    $options = [];

    // Get the current domain and language.
    $current_domain = $this->domainNegotiator->getActiveDomain()->id();
    $current_language = $this->languageManager->getCurrentLanguage()->getId();

    // Load entities of the specified type and bundle.
    $entities = $this->entityTypeManager->getStorage($entityType)
      ->loadByProperties(['type' => $bundle]);

    foreach ($entities as $id => $entity) {
      $name = $entity->get('title')->getValue()[0]['value'];
      $allowed_domains = $entity->get($fieldName)->getValue();
      foreach ($allowed_domains as $domain_id => $allowed_domain) {
        if ($current_domain == $allowed_domain['target_id']) {
          if ($entity->hasTranslation($current_language)) {
            $translated = $entity->getTranslation($current_language);
            $options[$id] = $translated->get('title')->getValue()[0]['value'];
          }
          else {
            $options[$id] = $name;
          }
        }
      }
    }

    return $options;
  }

  /**
   * Load a webform by service type id.
   *
   * @param int $serviceTypeID
   *   The service type id to search for.
   *
   * @return \Drupal\webform\Entity\Webform|null
   *   The loaded webform entity or null if not found.
   */
  public function loadWebformByServiceType($serviceTypeID) {
    // Search for a webform with the specified service type id.
    $webform_id = 'webform_service_rating_' . $serviceTypeID;

    $query = \Drupal::database()->select('webform', 'w');
    $query->fields('w', ['webform_id'])
      ->condition('w.webform_id', $webform_id);

    $result = $query->execute();

    if (!empty($result)) {
      $webform_id = $result->fetchField();
      // Load the webform entity using the ID.
      $webform_entity = Webform::load($webform_id);

      if ($webform_entity) {
        return $webform_entity;
      }
    }

    return NULL;
  }

  /**
   * Create a new webform with an element based on question type.
   *
   * @param mixed $form_state
   *   The form state of the current form.
   * @param int $lastOptionCountWithValue
   *   The last option count with a value for determining the range.
   *
   * @return \Drupal\webform\Entity\Webform
   *   The created webform entity.
   */
  public function createWebform($form_state, $lastOptionCountWithValue) {
    $service_type = $form_state->getValue('service_type');

    if (is_numeric($service_type)) {
      $service_type_id = $service_type;
      $node = Node::load($service_type_id);
      if ($node) {
        $service_type = $node->getTitle();
      }
      else {
        $service_type = NULL;
      }
    }
    else {
      $query = \Drupal::entityQuery('node')
        ->condition('type', 'service_type')
        ->condition('title', $service_type)
        ->accessCheck(FALSE);

      $node_id = $query->execute();
      $service_type_id = reset($node_id);
    }

    $webform_id = 'webform_service_rating_' . $service_type_id;

    // Create a new webform entity.
    $webform = Webform::create([
      'id' => $webform_id,
      'webform_type' => 'service_rating',
      'title' => $service_type . ' - Feedback',
    ]);

    // By default, the webforms will be closed.
    $webform->setStatus(WebformInterface::STATUS_CLOSED);
    $elements = Yaml::decode($webform->get('elements') ?? '');

    if (empty($elements)) {
      $elements['service_submission_id'] = [
        '#type' => 'textfield',
        '#title' => 'Service Submission ID',
        '#feedback_area' => $form_state->getValue('feedback_area'),
        '#attributes' => [
          'class' => ['service-info-rating'],
        ],
      ];
      $elements['service_location_tid'] = [
        '#type' => 'textfield',
        '#title' => 'Location of Service',
        '#feedback_area' => $form_state->getValue('feedback_area'),
        '#attributes' => [
          'class' => ['service-info-rating'],
        ],
      ];
      $elements['service_organisation'] = [
        '#type' => 'textfield',
        '#title' => 'Organisation of Service',
        '#feedback_area' => $form_state->getValue('feedback_area'),
        '#attributes' => [
          'class' => ['service-info-rating'],
        ],
      ];
    }

    // Define the form elements based on the question type.
    if ($form_state->getValue('question_type') === 'rating') {
      $elements['rating_question_' . uniqid()] = [
        '#type' => 'radios',
        '#feedback_area' => $form_state->getValue('feedback_area'),
        '#title' => $form_state->getValue('question_description'),
        '#required' => $form_state->getValue('is_required'),
        '#options' => $this->getRatingOptions($form_state, $lastOptionCountWithValue),
      ];
    }
    elseif ($form_state->getValue('question_type') === 'multiple_choice') {
      $elements['multiple_choice_question_' . uniqid()] = [
        '#type' => 'radios',
        '#feedback_area' => $form_state->getValue('feedback_area'),
        '#title' => $form_state->getValue('question_description'),
        '#required' => $form_state->getValue('is_required'),
        '#options' => $this->getMultipleChoiceOptions($form_state),
      ];
    }
    $webform->setElements($elements);
    $webform->save();

    return $webform;
  }

  /**
   * Updates an existing webform with new elements.
   *
   * @param \Drupal\webform\Entity\Webform $webform
   *   The webform entity to update.
   * @param mixed $form_state
   *   The form state of the current form.
   * @param int $lastOptionCountWithValue
   *   The last option count with a value for determining the range.
   *
   * @return \Drupal\webform\Entity\Webform
   *   The updated webform entity.
   */
  public function updateWebform(Webform $webform, $form_state, $lastOptionCountWithValue) {
    // Load the existing elements from the webform.
    $elements = Yaml::decode($webform->get('elements') ?? '');

    // Define the form elements based on the question type.
    if ($form_state->getValue('question_type') === 'rating') {
      $elements['rating_question_' . uniqid()] = [
        '#type' => 'radios',
        '#feedback_area' => $form_state->getValue('feedback_area'),
        '#title' => $form_state->getValue('question_description'),
        '#required' => $form_state->getValue('is_required'),
        '#options' => $this->getRatingOptions($form_state, $lastOptionCountWithValue),
      ];
    }
    elseif ($form_state->getValue('question_type') === 'multiple_choice') {
      $elements['multiple_choice_question_' . uniqid()] = [
        '#type' => 'radios',
        '#feedback_area' => $form_state->getValue('feedback_area'),
        '#title' => $form_state->getValue('question_description'),
        '#required' => $form_state->getValue('is_required'),
        '#options' => $this->getMultipleChoiceOptions($form_state),
      ];
    }

    // Update the webform's elements.
    $webform->setElements($elements);
    $webform->save();

    return $webform;
  }

  /**
   * Get options for the radios element for multiple choice questions.
   *
   * @param mixed $form_state
   *   The form state array containing the options.
   *
   * @return array
   *   An array of options for the radios element.
   */
  public function getMultipleChoiceOptions(mixed $form_state) {
    $options = [];
    $option_count = $form_state->get('option_count');

    for ($option_no = 0; $option_no < $option_count; $option_no++) {
      // Get the option title from the form state.
      $option_title = $form_state->getValue(['textfield' . $option_no]);
      if (!empty($option_title)) {
        $options[$option_no + 1] = $option_title;
      }
    }

    return $options;
  }

  /**
   * Retrieves rating options based on form state values.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object containing user-submitted values.
   * @param int $lastOptionCountWithValue
   *   The number of the last option with a non-empty value.
   *
   * @return array
   *   An associative array of rating options, where each option may have a label
   *   and description based on form state values.
   */
  public function getRatingOptions(FormStateInterface $form_state, $lastOptionCountWithValue) {
    $options = [];

    for ($option_no = 0; $option_no < $lastOptionCountWithValue; $option_no++) {
      // Get the option title from the form state.
      $option_title = $form_state->getValue(['textfield' . $option_no]);

      if (!empty($option_title)) {
        // Set the description for the option if the title is not empty.
        $options[$option_no + 1] = $option_no + 1 . ' -- ' . $option_title;
      }
      else {
        $options[$option_no + 1] = $option_no + 1;
      }
    }

    return $options;
  }

  /**
   * Determines the valid count of options based on form state values.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object containing user-submitted values.
   *
   * @return int
   *   The valid count of options based on the form state.
   */
  public function validOptionCount(FormStateInterface $form_state) {
    $valid_option_count = 0;
    $option_count = $form_state->get('option_count');

    for ($option_no = 0; $option_no < $option_count; $option_no++) {
      if ($option_no + 1 == $option_count) {
        // Check the last option which has value.
        $rating_option_count = $option_count + 1;
        do {
          $rating_option_count--;
        } while (!$form_state->getValue(['textfield' . ($rating_option_count - 1)]));
        $valid_option_count = $rating_option_count;
      }
    }

    return $valid_option_count;
  }

  /**
   * Retrieves submission IDs based on webform ID and two element-key-value pairs.
   *
   * @param int $webform_id
   *   The ID of the webform.
   * @param string $element1
   *   The name of the first webform element. (optional)
   * @param array|null $element2
   *   An associative array containing the element key and value for the second element.
   *   - 'key': The name of the second webform element.
   *   - 'value': The value to match for the second element.
   * @param array|null $element3
   *   An associative array containing the element key and value for the third element.
   *   - 'key': The name of the third webform element.
   *   - 'value': The value to match for the third element.
   *
   * @return array
   *   An array of submission IDs that match both element-key-value pairs.
   */
  public function getSubmissionIdsForMultipleElements($webform_id, $element1 = NULL, $element2 = NULL, $element3 = NULL) {
    $database = \Drupal::database();

    $query = $database->select('webform_submission_data', 'wsd1');
    $query->fields('wsd1', ['sid']);
    $query->condition('wsd1.webform_id', $webform_id);

    if ($element1) {
      $query->condition('wsd1.name', $element1, '=');
      $query->condition('wsd1.value', NULL, 'IS NOT NULL');
    }

    if ($element2) {
      $query->join('webform_submission_data', 'wsd2', 'wsd1.sid = wsd2.sid');
      $query->condition('wsd2.name', $element2['key'], '=');
      $query->condition('wsd2.value', $element2['value'], '=');
      $query->condition('wsd2.value', NULL, 'IS NOT NULL');
    }

    if ($element3) {
      $query->join('webform_submission_data', 'wsd3', 'wsd1.sid = wsd3.sid');
      $query->condition('wsd3.name', $element3['key'], '=');
      $query->condition('wsd3.value', $element3['value'], '=');
      $query->condition('wsd3.value', NULL, 'IS NOT NULL');
    }

    $query->groupBy('wsd1.sid');

    $result = $query->execute();
    $submission_ids = [];

    foreach ($result as $row) {
      $submission_ids[] = $row->sid;
    }

    return $submission_ids;
  }

  /**
   * Retrieves submission IDs based on webform ID and two element-key-value pairs.
   *
   * @param int $webform_id
   *   The ID of the webform.
   * @param array $element
   *   An associative array containing the element key and value for the element.
   *   - 'key': The name of the webform element.
   *   - 'value': The value to match for the element.
   *
   * @return array
   *   An array of submission IDs that match both element-key-value pairs.
   */
  public function getSubmissionIdsForSingleElement($webform_id, array $element) {
    $database = \Drupal::database();

    $query = $database->select('webform_submission_data', 'wsd1');
    $query->fields('wsd1', ['sid']);
    $query->condition('wsd1.webform_id', $webform_id);

    if ($element) {
      $query->join('webform_submission_data', 'wsd2', 'wsd1.sid = wsd2.sid');
      $query->condition('wsd2.name', $element['key'], '=');
      $query->condition('wsd2.value', $element['value'], '=');
      $query->condition('wsd2.value', NULL, 'IS NOT NULL');
    }

    $query->groupBy('wsd1.sid');

    $result = $query->execute();
    $submission_ids = [];

    foreach ($result as $row) {
      $submission_ids[] = $row->sid;
    }

    return $submission_ids;
  }

  /**
   * Normalizes a rating value based on the provided options.
   *
   * @param int $value
   *   The rating value to be normalized.
   * @param array $options
   *   An array of rating options.
   *
   * @return float
   *   The normalized rating value.
   */
  public function normalizeRating($value, array $options) {
    $max_value = count($options);
    return (($value - 1) / ($max_value - 1)) * 4 + 1;
  }

  /**
   * Calculates the average rating from normalized element values.
   *
   * @param array $normalized_element_values
   *   An array of normalized rating values.
   *
   * @return int
   *   The average rating, rounded to nearest integer.
   */
  public function calculateAverageRating(array $normalized_element_values) {
    // Flatten the values and filter out 0 values.
    $flattened_values = array_merge(...$normalized_element_values);
    $flattened_values = array_filter($flattened_values, function ($value) {
      return $value != 0;
    });

    $average_rating = 0;

    if (!empty($flattened_values)) {
      $average_rating = round(array_sum($flattened_values) / count($flattened_values), 0);
    }

    return $average_rating;
  }

  /**
   * Calculates the average of numeric values in a multidimensional array.
   *
   * @param array $normalized_element_values
   *   A multidimensional array containing numeric values for which to calculate the average.
   *
   * @return float
   *   The average of the numeric values, rounded to one decimal point.
   */
  public function calculateTotalAverageRating(array $normalized_element_values) {
    $numeric_values = [];

    // Extract numeric values from the array.
    foreach ($normalized_element_values as $key => $value) {
      if (is_numeric($value) && $value != 0) {
        $numeric_values[] = $value;
      }
    }

    $average_rating = 0;

    if (!empty($numeric_values)) {
      $average_rating = round(array_sum($numeric_values) / count($numeric_values), 0);
    }

    return $average_rating;
  }

  /**
   * Returns the organization ID for users.
   *
   * This function retrieves the organization ID for the user depending on
   * the active domain. If the user is admin, then it assigns an organisation id by itself.
   *
   * @return int|null
   *   The organization ID, or null if no matching domain is found for non-admin users.
   */
  public function organisationForFiltering() {
    $current_user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    if (!$current_user->hasRole('administrator')) {
      $organisation_id = $current_user->get('field_organisation')->getValue()[0]['target_id'];
    }
    else {
      $activeDomainID = $this->domainNegotiator->getActiveDomain()->id();
      $organisation_nids = \Drupal::entityQuery('node')
        ->condition('type', 'organisation')
        ->condition('field_domain_access', $activeDomainID)
        ->accessCheck(FALSE)
        ->execute();
      $organisation_id = reset($organisation_nids);
    }

    return $organisation_id;
  }

  /**
   * Fetches domain values associated with a service type based on a webform ID.
   *
   * @param string $webform_id
   *   The webform ID containing the service type information.
   *
   * @return array
   *   An array containing domain values associated with the service type.
   */
  public function fetchServiceTypeDomains($webform_id) {
    $domains = [];

    // Extract service type ID from the webform ID.
    if (preg_match('/webform_service_rating_(\d+)/', $webform_id, $matches)) {
      $service_type_id = $matches[1];
      $service_type_node = Node::load($service_type_id);

      if ($service_type_node && $service_type_node->hasField('field_domain_access')) {
        $domain_values = $service_type_node->get('field_domain_access')->getValue();
        foreach ($domain_values as $item) {
          $target_id = $item['target_id'] ?? NULL;
          if ($target_id !== NULL) {
            $domains[] = $target_id;
          }
        }
        $domains = array_unique($domains);
      }
    }

    return $domains;
  }

}
