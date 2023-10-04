<?php

namespace Drupal\erpw_webform\Form;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\webform\Entity\Webform;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class ServiceRatingQuestionForm extends FormBase {

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
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->domainNegotiator = $container->get('domain.negotiator');
    $instance->languageManager = $container->get('language_manager');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->messenger = $container->get('messenger');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'service_rating_question_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $service_type_options = $this->loadAndProcessEntities('node', 'service_type', 'field_domain_access');
    $feedback_area_options = $this->loadAndProcessEntities('node', 'feedback_area', 'field_domain_access');

    $form['service_type'] = [
      '#title' => t('Select Service Type'),
      '#type' => 'select',
      '#weight' => -10,
      '#required' => TRUE,
      '#description' => '<span class = "service-rating-description"> Select the service type for which the question is being created.</span>',
      "#empty_option" => t('- Select -'),
      '#options' => $service_type_options,
    ];

    $form['feedback_area'] = [
      '#title' => t('Feedback Area'),
      '#type' => 'select',
      '#weight' => -10,
      '#required' => TRUE,
      '#description' => '<span class = "service-rating-description"> Select the feedback area for which the question is being created.</span>',
      "#empty_option" => t('- Select -'),
      '#options' => $feedback_area_options,
    ];

    $form['question_description'] = [
      '#type' => 'textarea',
      '#title' => 'Question Description',
      '#required' => TRUE,
    ];

    $form['question_type'] = [
      '#type' => 'radios',
      '#title' => 'Question Type',
      '#options' => [
        'rating' => 'Rating Question',
        'multiple_choice' => 'Multiple Choice',
      ],
      '#required' => TRUE,
      '#default_value' => 'rating',
      '#ajax' => [
        'callback' => '::questionTypeCallback',
        'wrapper' => 'options-fieldset-wrapper',
      ],
    ];

    $question_type = $form_state->getValue('question_type');

    // Add options fieldset with a "Add more" button.
    $form['options_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Options'),
      '#prefix' => '<div id="options-fieldset-wrapper" class = "service-rating-options">',
      '#suffix' => '</div>',
    ];

    $form['options_fieldset']['description'] = [
      '#markup' => '<p class = "service-rating-description">' . $this->t("First response is most satisfactory (highest rating), last response least satisfactory (lowest rating)") . '</p>',
    ];

    // Gather the number of options in the form already.
    $option_count = $form_state->get('option_count');

    if (empty($question_type)) {
      // Default to Rating Question.
      $question_type = 'rating';
    }

    // Check if the question type changed or if the option count is not set.
    if ($option_count === NULL) {
      $option_count = ($question_type == 'rating') ? 3 : 0;
    }

    $form_state->set('option_count', $option_count);

    for ($option_no = 0; $option_no < $option_count; $option_no++) {
      $option_title = ($question_type == 'rating') ? ($option_no + 1) : 'Option';

      $form['options_fieldset']['options_' . $option_no] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['option-item']],
      ];

      $form['options_fieldset']['title' . $option_no] = [
        '#markup' => '<label>' . $option_title . '</label>',
      ];

      $form['options_fieldset']['textfield' . $option_no] = [
        '#type' => 'textfield',
        '#title' => NULL,
        '#attributes' => ['placeholder' => 'Add Level ' . $option_no + 1 . ' label'],
      ];
    }

    $form['options_fieldset']['actions'] = [
      '#type' => 'actions',
    ];

    $form['options_fieldset']['actions']['add_option'] = [
      '#type' => 'submit',
      '#value' => 'Add Option',
      '#submit' => ['::addOneOption'],
      '#ajax' => [
        'callback' => '::addmoreOptionCallback',
        'wrapper' => 'options-fieldset-wrapper',
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Save Question',
    ];
    $form['#attached']['library'][] = 'erpw_webform/erpw_service_rating';
    return $form;
  }

  /**
   * Ajax callback to update the options based on the selected question type.
   */
  public function questionTypeCallback(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild();
    return $form['options_fieldset'];
  }

  /**
   * Callback for the "Add Option" button.
   */
  public function addmoreOptionCallback(array &$form, FormStateInterface $form_state) {
    return $form['options_fieldset'];
  }

  /**
   * Submit handler for the "Add Option" button.
   */
  public function addOneOption(array &$form, FormStateInterface $form_state) {
    $cur_options = $form_state->get('option_count');
    $options = $cur_options + 1;
    $form_state->set('option_count', $options);
    $form_state->setRebuild();
  }

  // Callback to handle adding new question sections.
  public function addQuestionCallback(array &$form, FormStateInterface $form_state) {
    $question_count = $form_state->get('question_count') ?: 0;
    $question_count++;
    $form_state->set('question_count', $question_count);

    // Trigger an Ajax update to add the new question section.
    $form_state->setRebuild();
  }

  // Ajax callback to update the form with the new question section.
  public function addQuestionAjaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['questions_wrapper'];
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
  protected function loadAndProcessEntities($entityType, $bundle, $fieldName) {
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
          } else {
            $options[$id] = $name;
          }
        }
      }
    }

    return $options;
  }


  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    if ($form_state->getValue('op') != 'Add Option') {
      $question_type = $form_state->getValue('question_type');
    $option_count = $form_state->get('option_count');

    $valid_option_count = 0;
    for ($option_no = 0; $option_no < $option_count; $option_no++) {
      $option_value = $form_state->getValue(['textfield' . $option_no]);
      if ($question_type == 'multiple_choice') {
        // Check that the option value is not empty.
        if (!empty($option_value)) {
          $valid_option_count++;
        }
        // Check that no value is empty in the middle.
        elseif ($option_no + 1 != $option_count) {
          $form_state->setErrorByName('textfield' . $option_no, $this->t("Please don't leave any value in between options in case of Multiple Choice question type."));
        }
      }
      else {
        if ($option_no + 1 == $option_count) {
          // Check the last option which has value.
          $rating_option_count = $option_count + 1;
          do {
            $rating_option_count--;
          }
          while (!$form_state->getValue(['textfield' . $rating_option_count - 1]));
          $valid_option_count = $rating_option_count;
        }
      }
    }

    // Define the minimum required options based on the question type.
    $min_options = ($question_type == 'rating') ? 3 : 2;

    // Check if the number of valid options is less than the minimum required.
    if ($valid_option_count < $min_options) {
      $form_state->setErrorByName('options_fieldset', $this->t('At least @count options are required for the selected question type.', ['@count' => $min_options]));
    }
    }
    
  }

  /**
 * Implements the submitForm() method.
 */
public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get the selected service type and feedback area.
    $service_type_id = $form_state->getValue('service_type');
    $feedback_area_id = $form_state->getValue('feedback_area');
    $question_description = $form_state->getValue('question_description');
    $question_type = $form_state->getValue('question_type');
    $option_count = $form_state->get('option_count');
    $valid_option_count = 0;
    for ($option_no = 0; $option_no < $option_count; $option_no++) {
      $option_value = $form_state->getValue(['textfield' . $option_no]);
      if ($question_type == 'multiple_choice') {
        // Check that the option value is not empty.
        if (!empty($option_value)) {
          $valid_option_count++;
        }
      }
      else {
        if ($option_no + 1 == $option_count) {
          // Check the last option which has value.
          $rating_option_count = $option_count + 1;
          do {
            $rating_option_count--;
          }
          while (!$form_state->getValue(['textfield' . $rating_option_count - 1]));
          $valid_option_count = $rating_option_count;
        }
      }
    }

    $node = Node::load($service_type_id);
    if ($node) {
      $service_type = $node->getTitle();
    }
    $feedback_node = Node::load($feedback_area_id);
    if ($feedback_node) {
      $feedback_area = $feedback_node->getTitle();
    }
    $webform = $this->loadWebformByServiceType($service_type);

    if (!$webform) {
      // Create a new webform.
      $webform = $this->createWebform($service_type, $feedback_area, $question_description, $question_type, $form_state, $valid_option_count);
    }

    // Save the webform.
    $webform->save();

    // Redirect to the newly created or existing webform.
    $url = Url::fromRoute('entity.webform.canonical', ['webform' => $webform->id()]);
    $form_state->setRedirectUrl($url);
  }

/**
 * Load a webform by service type.
 *
 * @param string $serviceType
 *   The service type to search for.
 *
 * @return \Drupal\webform\Entity\Webform|null
 *   The loaded webform entity or null if not found.
 */
protected function loadWebformByServiceType($serviceType) {
  // Search for a webform with the specified service type.
  $query = \Drupal::entityQuery('webform')
    ->condition('webform_type', 'service_rating')
    ->condition('title', 'Service Rating ' . $serviceType)
    ->accessCheck(False);

  $webform_ids = $query->execute();
  if (!empty($webform_ids)) {
    $webform_id = reset($webform_ids);
    return \Drupal::entityTypeManager()->getStorage('webform')->load($webform_id);
  }

  return NULL;
}

/**
 * Create a new webform with an element based on question type.
 *
 * @param string $serviceType
 *   The service type for the webform.
 * @param string $feedback_area
 *   The feedback area for the question.
 * @param string $questionDescription
 *   The description of the question.
 * @param string $questionType
 *   The type of the question ('rating' or 'multiple_choice').
 * @param mixed $form_state
 *   The form state of the current form.
 * @param int $lastOptionCountWithValue
 *   The last option count with a value for determining the range.
 *
 * @return \Drupal\webform\Entity\Webform
 *   The created webform entity.
 */
protected function createWebform($serviceType, $feedback_area, $questionDescription, $questionType, $form_state, $lastOptionCountWithValue) {

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'service_type')
      ->condition('title', $serviceType)
      ->accessCheck(False);

    // Execute the query to get matching node ID of the service type.
    $node_id = $query->execute();
    $service_type_id = reset($node_id);
    $webform_id = 'webform_service_rating_' . $service_type_id;

    // Create a new webform entity.
  $webform = Webform::create([
    'id' => $webform_id,
    'webform_type' => 'service_rating',
    'title' => 'Service Rating ' . $serviceType,
  ]);

  $elements = Yaml::decode($webform->get('elements') ?? '');

  // Define the form elements based on the question type.
  if ($questionType === 'rating') {
      $elements[$feedback_area . '_' . uniqid()] = [
        '#type' => 'webform_rating',
        '#title' => $questionDescription,
        '#min' => 0,
        '#max' => $lastOptionCountWithValue,
        '#precision' => 1,
        '#stars' => $lastOptionCountWithValue + 1,
        '#description' => 'Rate from 0 to @max', ['@max' => $lastOptionCountWithValue],
      ];
    }
  elseif ($questionType === 'multiple_choice') {
      $elements[$feedback_area . '_' . uniqid()] = [
        '#type' => 'radios',
        '#title' => $questionDescription,
        '#options' => $this->getMultipleChoiceOptions($form_state),
      ];
    }
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
  protected function getMultipleChoiceOptions($form_state) {
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

}
