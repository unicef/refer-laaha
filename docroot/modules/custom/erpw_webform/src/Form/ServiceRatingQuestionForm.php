<?php

namespace Drupal\erpw_webform\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This is the form which creates the webform and adds the question for a particular service type.
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
   * The Service Rating Service.
   *
   * @var \Drupal\erpw_webform\ServiceRatingService
   */
  protected $serviceRating;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->domainNegotiator = $container->get('domain.negotiator');
    $instance->languageManager = $container->get('language_manager');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->messenger = $container->get('messenger');
    $instance->serviceRating = $container->get('erpw_webform.service_rating_service');
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
    $service_type_options = $this->serviceRating->loadAndProcessEntities('node', 'service_type', 'field_domain_access');
    $feedback_area_options = $this->serviceRating->loadAndProcessEntities('node', 'feedback_area', 'field_domain_access');

    foreach ($service_type_options as $id => $title) {
      $has_webform = $this->serviceRating->loadWebformByServiceType($id);
      if ($has_webform) {
        unset($service_type_options[$id]);
      }
    }

    if (empty($service_type_options)) {
      $disable = TRUE;
      $prefix = '<div class="empty-service-type-notice">* Forms for all the service types have been created. Create a new service type inorder to create its corresponding form.</div>';
    }
    else {
      $disable = FALSE;
      $prefix = '';
    }

    $form['service_type'] = [
      '#title' => t('Select Service Type'),
      '#type' => 'select',
      '#weight' => -10,
      '#required' => TRUE,
      '#description' => '<span class = "service-rating-description"> Select the service type for which the question is being created.</span>',
      "#empty_option" => t('- Select -'),
      '#options' => $service_type_options,
      '#disabled' => $disable,
      '#prefix' => $prefix,

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
      '#markup' => '<p class = "service-rating-description">' . $this->t("First response is  least satisfactory (lowest rating), last response most satisfactory (highest rating)") . '</p>',
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
      '#value' => $this->t('Add Option'),
      '#submit' => ['::addOneOption'],
      '#ajax' => [
        'callback' => '::addmoreOptionCallback',
        'wrapper' => 'options-fieldset-wrapper',
      ],
    ];

    $form['options_fieldset']['add_option']['description'] = [
      '#markup' => '<p class = "service-rating-description">' . $this->t("Minimum 3 options for Rating Question and 2 options for Multiple choice question are required. Maximum of 5 options are allowed in both.") . '</p>',
    ];

    $form['is_required'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Make question mandatory to answer'),
      '#default_value' => $form_state->getValue('single_checkbox', FALSE),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Save Question',
    ];

    $form['add_new_question'] = [
      '#type' => 'submit',
      '#value' => 'Add New Question',
      '#submit' => ['::addNewQuestion'],
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
   * Callback for "Add New Question" button.
   */
  public function addNewQuestion(array &$form, FormStateInterface $form_state) {
    $service_type_id = $form_state->getValue('service_type');
    $valid_option_count = $this->serviceRating->validOptionCount($form_state);
    $webform = $this->serviceRating->loadWebformByServiceType($service_type_id);

    if (!$webform) {
      // Create a new webform.
      $webform = $this->serviceRating->createWebform($form_state, $valid_option_count);
    }
    else {
      $webform = $this->serviceRating->updateWebform($webform, $form_state, $valid_option_count);
    }

    $webform->save();

    // Redirect to Add New Question form.
    $form_state->setRedirect('erpw_webform.add_new_rating_question', ['service_type__id' => $service_type_id]);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    if ($form_state->getValue('op') != 'Add Option') {
      $question_type = $form_state->getValue('question_type');

      $valid_option_count = $this->serviceRating->validOptionCount($form_state);
      for ($option_no = 0; $option_no < $valid_option_count; $option_no++) {
        $option_value = $form_state->getValue(['textfield' . $option_no]);
        if ($question_type == 'multiple_choice') {
          // Check that no value is empty in the middle.
          if (empty($option_value)) {
            $form_state->setErrorByName('textfield' . $option_no, $this->t("Please don't leave any value in between options in case of Multiple Choice question type."));
          }
        }
      }

      // Define the minimum required options based on the question type.
      $min_options = ($question_type == 'rating') ? 3 : 2;

      // Check if the number of valid options is less than the minimum required.
      if ($valid_option_count < $min_options) {
        $form_state->setErrorByName('options_fieldset', $this->t('At least @count options are required for the selected question type.', ['@count' => $min_options]));
      }
      elseif ($valid_option_count > 5) {
        $form_state->setErrorByName('options_fieldset', $this->t('Maximum 5 options are allowed.'));
      }
    }

  }

  /**
   * Implements the submitForm() method.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $service_type_id = $form_state->getValue('service_type');
    $valid_option_count = $this->serviceRating->validOptionCount($form_state);

    $webform = $this->serviceRating->loadWebformByServiceType($service_type_id);

    if (!$webform) {
      // Create a new webform.
      $webform = $this->serviceRating->createWebform($form_state, $valid_option_count);
    }
    else {
      $webform = $this->serviceRating->updateWebform($webform, $form_state, $valid_option_count);
    }

    // Save the webform.
    $webform->save();

    // Redirect to the newly created or existing webform.
    $url = Url::fromRoute('entity.webform.edit_form', ['webform' => $webform->id()]);
    $form_state->setRedirectUrl($url);
  }

}
