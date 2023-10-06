<?php

namespace Drupal\erpw_webform\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class ServiceRatingAddNewQuestionForm extends FormBase {

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
   * The RouteMatch service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

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
    return 'service_rating_new_question_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $service_type_id = $this->routeMatch->getParameter('service_type__id');
    $node = Node::load($service_type_id);
    if ($node) {
      $service_type = $node->getTitle();
    }
    $feedback_area_options = $this->serviceRating->loadAndProcessEntities('node', 'feedback_area', 'field_domain_access');

    // Create the select field with the service type fixed and disabled.
    $form['service_type'] = [
      '#title' => t('Service Type'),
      '#type' => 'select',
      '#default_value' => $service_type,
      '#options' => [$service_type => $service_type],
      '#disabled' => TRUE,
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
    $service_type = $form_state->getValue('service_type');
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
          } while (!$form_state->getValue(['textfield' . $rating_option_count - 1]));
          $valid_option_count = $rating_option_count;
          // @todo check again if it works with multiple last empty values.
        }
      }
    }

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'service_type')
      ->condition('title', $service_type)
      ->accessCheck(FALSE);

    $node_id = $query->execute();
    $service_type_id = reset($node_id);

    $feedback_node = Node::load($feedback_area_id);
    if ($feedback_node) {
      $feedback_area = $feedback_node->getTitle();
    }
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
            } while (!$form_state->getValue(['textfield' . $rating_option_count - 1]));
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
          } while (!$form_state->getValue(['textfield' . $rating_option_count - 1]));
          $valid_option_count = $rating_option_count;
          // @todo check again if it works with multiple last empty values.
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
    $url = Url::fromRoute('entity.webform.canonical', ['webform' => $webform->id()]);
    $form_state->setRedirectUrl($url);
  }

}
