<?php

namespace Drupal\erpw_webform\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom form example form.
 */
class ServiceRatingOrganisationFilterForm extends FormBase {

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
   * The State API service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->domainNegotiator = $container->get('domain.negotiator');
    $instance->languageManager = $container->get('language_manager');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->messenger = $container->get('messenger');
    $instance->routeMatch = $container->get('current_route_match');
    $instance->serviceRating = $container->get('erpw_webform.service_rating_service');
    $instance->state = $container->get('state');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'service_rating_organisation_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getAverageWebformRatingsOfOrg(string $org_id) {
    // Get all webforms.
    $webforms = \Drupal::entityTypeManager()->getStorage('webform')->loadMultiple();

    // Initialize an array to store webform names and their average ratings.
    $webform_ratings = [];

    // Iterate through all webforms.
    foreach ($webforms as $webform) {

      $webform_id = $webform->id();
      // Check if the webform's machine name matches the pattern.
      if ($webform->isOpen() && preg_match('/^webform_service_rating_\d+$/', $webform_id)) {
        $element = [
          'key' => 'service_organisation',
          'value' => $org_id,
        ];
        // Get all submissions for the webform.
        $submission_ids = $this->serviceRating->getSubmissionIdsForSingleElement($webform_id, $element);

        // Initialize an array to store normalized element values.
        $normalized_element_values = [];

        // Serialize and hash the sorted submission IDs.
        $submission_ids_hash = hash('sha256', Json::encode($submission_ids));
        $active_domain_id = $this->domainNegotiator->getActiveDomain()->id();
        $cache_tags = ['webform_submission:' . $submission_ids_hash];
        $cache_id = 'service_rating_location_normalized_values_' . $active_domain_id . $webform_id . $org_id;
        $cache_data = \Drupal::cache()->get($cache_id);

        if (!$cache_data) {
          // Iterate through each submission.
          foreach ($submission_ids as $submission_id) {
            $submission = WebformSubmission::load($submission_id);

            // Get all elements of the webform.
            $elements = $webform->getElementsDecodedAndFlattened();

            // Initialize an array to store normalized values for this submission.
            $normalized_values = [];

            // Iterate through each element of the webform.
            foreach ($elements as $element_key => $element) {
              if ($element['#type'] == 'radios') {
                $element_value = $submission->getElementData($element_key);

                // Check if the element value is numeric.
                if (!empty($element_value) && is_numeric($element_value)) {
                  // Normalize the rating to the range of 1 to 5.
                  $normalized_rating = $this->serviceRating->normalizeRating($element_value, $element['#options']);
                  $normalized_values[] = $normalized_rating;
                }
              }
            }

            // Store the normalized values for this submission.
            $normalized_element_values[] = $normalized_values;
          }
          // Cache the computed value.
          \Drupal::cache()->set($cache_id, $normalized_element_values, Cache::PERMANENT, $cache_tags);
        }
        else {
          // Retrieve the data from the cache.
          $normalized_element_values = $cache_data->data;
        }

        // Calculate the average rating for this webform and round to the nearest whole number.
        $average_rating = round($this->serviceRating->calculateAverageRating($normalized_element_values));
        $webform_ratings[$webform_id] = $average_rating;
      }
    }

    // Create an HTML list for displaying the webform ratings.
    $node = Node::load($org_id);
    if ($node) {
      $organisation_name = $node->getTitle();
    }
    else {
      $organisation_name = $this->t('Invalid Organisation');
    }
    $organisational_average = $this->serviceRating->calculateTotalAverageRating($webform_ratings);

    $totalReviewsCount = 0;
    $servicesCount = 0;
    $organisation_services_list = [];
    foreach ($webform_ratings as $webform_id => $average_rating) {

      // Omit the webform if it does not belong to current domain.
      $service_type_domain = $this->serviceRating->fetchServiceTypeDomains($webform_id);
      if (in_array($active_domain_id, $service_type_domain)) {
        $webform = Webform::load($webform_id);
        // Review Count:
        $element = [
          'key' => 'service_organisation',
          'value' => $org_id,
        ];
        $submission_ids = $this->serviceRating->getSubmissionIdsForSingleElement($webform->id(), $element);
        $serviceReviewsCount = count($submission_ids) > 1 ? count($submission_ids) . ' Reviews' : count($submission_ids) . ' Review';
        $totalReviewsCount += count($submission_ids);
        $webform_name = str_replace('Service Rating ', '', $webform->label());
        $url = Url::fromRoute('erpw_webform.questions.calculate_average_location_rating', ['webform_id' => $webform_id]);
        $href = $url->toString();
        $servicesCount += 1;

        $organisation_services_list[] = [
          'service_name' => $webform_name,
          'service_link' => $href,
          'service_rating' => $average_rating,
          'service_review_count' => $serviceReviewsCount,
        ];
      }
      else {
        continue;
      }
    }

    $output = [
      '#theme' => 'service_rating_page',
      '#title' => $this->t('Service Ratings'),
      '#organisation_name' => $organisation_name,
      '#organisation_average' => round($organisational_average),
      '#organisation_total_services_count' => $servicesCount,
      '#organisation_total_reviews' => $totalReviewsCount > 1 ? $totalReviewsCount . ' Reviews' : $totalReviewsCount . ' Review',
      '#service_list' => $organisation_services_list,
    ];

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function getDomainOrganisations() {
    $active_domain = $this->domainNegotiator->getActiveDomain();
    $activeDomainID = $active_domain->id();
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->condition('type', 'organisation');
    $query->condition('field_domain_access', $activeDomainID);
    $query->accessCheck(FALSE);
    $entity_ids = $query->execute();
    $organisations = $this->entityTypeManager->getStorage('node')->loadMultiple($entity_ids);

    $organisation_values = [];
    foreach ($organisations as $organisation) {
      $org_avg_rating = $this->getAverageWebformRatingsOfOrg($organisation->id());
      $organisation_values[$organisation->id()] = $this->t(
        '@org_title (Rating - @org_avg_rating/5)',
        [
          '@org_title' => $organisation->label(),
          '@org_avg_rating' => (int) $org_avg_rating['#organisation_average'],
        ]
      );
    }

    return $organisation_values;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $org_values = $this->getDomainOrganisations();

    $form['organisation_select_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Organisation'),
      '#options' => $org_values,
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Apply'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $formStateValues = $form_state->getValues();
    $org_id = $formStateValues['organisation_select_field'];
    $org_average_ratings = $this->getAverageWebformRatingsOfOrg($org_id);
    $this->state->set('service_rating.org_average_rating', $org_average_ratings);

    $org_title = $this->entityTypeManager->getStorage('node')->load($org_id)->label();
    $this->messenger()->addMessage($this->t('Organisation updated to @org_title', ['@org_title' => $org_title]));
  }

}
