<?php

namespace Drupal\erpw_webform;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
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
   * ServiceRatingService constructor.
   *
   * @param \Drupal\domain\DomainNegotiatorInterface $domain_negotiator
   *   DomainNegotiator object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   Entity Manager Object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Location Manager object.
   */
  public function __construct(
    DomainNegotiatorInterface $domain_negotiator,
    EntityTypeManagerInterface $entity_manager,
    LanguageManagerInterface $language_manager,
    ) {
    $this->domainNegotiator = $domain_negotiator;
    $this->entityTypeManager = $entity_manager;
    $this->languageManager = $language_manager;
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
      'title' => 'Service Rating ' . $service_type,
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
          'class' => ['service-submission-id-rating'],
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
   * @param array $form_state
   *   The form state array containing the options.
   *
   * @return array
   *   An array of options for the radios element.
   */
  public function getMultipleChoiceOptions($form_state) {
    $options = [];
    $option_count = $form_state->get('option_count');

    for ($option_no = 0; $option_no < $option_count; $option_no++) {
      // Get the option title from the form state.
      $option_title = $form_state->getValue(['textfield' . $option_no]);
      if (!empty($option_title)) {
        $options[$option_no] = $option_title;
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
  public function getRatingOptions($form_state, $lastOptionCountWithValue) {
    $options = [];

    for ($option_no = 0; $option_no < $lastOptionCountWithValue; $option_no++) {
      // Get the option title from the form state.
      $option_title = $form_state->getValue(['textfield' . $option_no]);

      if (!empty($option_title)) {
        // Set the description for the option if the title is not empty.
        $options[$option_no] = $option_no + 1 . ' -- ' . $option_title;
      }
      else {
        $options[$option_no] = $option_no + 1;
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
  public function validOptionCount($form_state) {
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

}
