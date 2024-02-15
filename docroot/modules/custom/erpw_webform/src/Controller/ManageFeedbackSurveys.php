<?php

namespace Drupal\erpw_webform\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Manage feedback surveys page controller.
 */
class ManageFeedbackSurveys extends ControllerBase {

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
   * The Location Service.
   *
   * @var \Drupal\erpw_location\LocationService
   */
  protected $location;

  /**
   * The Entity type manager Service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\domain\DomainNegotiatorInterface definition.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->routeMatch = $container->get('current_route_match');
    $instance->serviceRating = $container->get('erpw_webform.service_rating_service');
    $instance->location = $container->get('erpw_location.location_services');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->domainNegotiator = $container->get('domain.negotiator');
    return $instance;
  }

  /**
   * Controller conntent.
   */
  public function content() {
    $webforms = $this->entityTypeManager()->getStorage('webform')->loadMultiple();
    $service_rating_webforms = $this->getServiceRatingForms($webforms);

    return [
      '#theme' => 'page__manage_feedback_surveys',
      '#title' => $this->t('Manage Feedback Surveys'),
      'data' => $service_rating_webforms,
    ];
  }

  /**
   * Gives the service rating webforms from the list of multiple webforms.
   */
  public function getServiceRatingForms(array $webforms) {
    $active_domain_id = $this->domainNegotiator->getActiveDomain()->id();
    // $cache_tags = ['webform'];
    // $cache_id = 'service_rating_forms_query_' . $active_domain_id;
    // $cache_data = \Drupal::cache()->get($cache_id);
    // Check if data is not in cache.
    // if (!$cache_data) {
    //   // If data is not in cache, execute the build logic.
    //   $service_rating_webforms = [];

    //   foreach ($webforms as $webform => $webform_data) {
    //     if (str_contains($webform, 'webform_service_rating_')) {
    //       // Omit the webform if it does not belong to current domain.
    //       $service_type_domain = $this->serviceRating->fetchServiceTypeDomains($webform_data->id());
    //       if (in_array($active_domain_id, $service_type_domain)) {
    //         $form_questions_count = $this->getServiceRatingFormQuestionCount($webform_data->getElementsDecoded());
    //         $service_rating_webforms[] = [
    //           'webform_data' => $webform_data,
    //           'webform_id' => $webform_data->id(),
    //           'webform_title' => $webform_data->label(),
    //           'webform_questions_count' => $form_questions_count,
    //           'webform_status' => $webform_data->isOpen() ? 'Published' : 'Draft',
    //         ];
    //       }
    //       else {
    //         continue;
    //       }
    //     }
    //   }

    //   // Store the result in cache.
    //   \Drupal::cache()->set($cache_id, $service_rating_webforms, Cache::PERMANENT, $cache_tags);
    // }
    // else {
    //   // If data is in cache, use the cached result.
    //   $service_rating_webforms = $cache_data->data;
    // }

    // If data is not in cache, execute the build logic.
    $service_rating_webforms = [];
    foreach ($webforms as $webform => $webform_data) {
      if (str_contains($webform, 'webform_service_rating_')) {
        // Omit the webform if it does not belong to current domain.
        $service_type_domain = $this->serviceRating->fetchServiceTypeDomains($webform_data->id());
        if (in_array($active_domain_id, $service_type_domain)) {
          $form_questions_count = $this->getServiceRatingFormQuestionCount($webform_data->getElementsDecoded());
          $service_rating_webforms[] = [
            'webform_data' => $webform_data,
            'webform_id' => $webform_data->id(),
            'webform_title' => $webform_data->label(),
            'webform_questions_count' => $form_questions_count,
            'webform_status' => $webform_data->isOpen() ? 'Published' : 'Draft',
          ];
        }
        else {
          continue;
        }
      }
    }

    // @todo Cache computed value - Done
    return $service_rating_webforms;
  }

  /**
   * Fetch number of questions for a particular webform.
   */
  public function getServiceRatingFormQuestionCount(array $webform_elements) {
    $count = 0;
    foreach ($webform_elements as $element_key => $element) {
      if ($element['#type'] == 'radios') {
        $count++;
      }
    }
    return $count;
  }

}
