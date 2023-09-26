<?php

namespace Drupal\erpw_webform\Form;

use Drupal\custom_question_form\Entity\Question;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
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
    $service_type_options = [];
    $current_domain = $this->domainNegotiator->getActiveDomain()->id();
    $current_language = $this->languageManager->getCurrentLanguage()->getId();
    // The values for the Service Type dropdown box.
    $service_types = $this->entityTypeManager->getStorage('node')
      ->loadByProperties(['type' => 'service_type']);
    foreach ($service_types as $id => $service_type) {
      $name = $service_type->get('title')->getValue()[0]['value'];
      $allowed_domains = $service_type->get('field_domain_access')->getValue();
      foreach ($allowed_domains as $domain_id => $allowed_domain) {
        if ($current_domain == $allowed_domain['target_id']) {
          if ($service_type->hasTranslation($current_language)) {
            $translated = $service_type->getTranslation($current_language);
            $service_type_options[$id] = $translated->get('title')->getValue()[0]['value'];
          }
          else {
            $service_type_options[$id] = $name;
          }
        }
      }
    }

    $form['service_type'] = [
      '#title' => t('Select Service Type'),
      '#type' => 'select',
      '#weight' => -10,
      '#required' => TRUE,
      '#description' => '<span class = "service-rating-description"> Select the service type for which the question is being created.</span>',
      "#empty_option" => t('- Select -'),
      '#options' => $service_type_options,
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

      $form['options_fieldset'][$option_no]['option'] = [
        '#type' => 'container',
        // Use a container for custom layout.
        '#attributes' => ['class' => ['option-item']],
      ];

      $form['options_fieldset'][$option_no]['option']['title'] = [
        '#markup' => '<label>' . $option_title . '</label>',
      ];

      $form['options_fieldset'][$option_no]['option']['textfield'] = [
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

  /**
   * Submit handler for the "Remove Option" button.
   */
  public function removeOption(array &$form, FormStateInterface $form_state) {
    $button_clicked = $form_state->getTriggeringElement()['#name'];
    // Set a flag or logic to mark the option for removal.
    $form_state->set("option_" . $button_clicked . "_remove", TRUE);
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Add custom form validation here if needed.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Handle form submission and save the question here.
    // You can access form values using $form_state->getValue().
    // Example:
    $question_type = $form_state->getValue('question_type');
    $question_text = $form_state->getValue('question_text');

    // Create a new question entity and save it.
    // You may need to define your own question entity type.
    // Example:
    $question = Question::create([
      'type' => $question_type,
      'question_text' => $question_text,
      // Add other fields as needed.
    ]);
    $question->save();

    // Display a success message.
    $this->messenger->addMessage('Question saved successfully.');
  }

}
