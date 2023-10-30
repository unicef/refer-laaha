<?php

namespace Drupal\erpw_webform\Controller;

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->routeMatch = $container->get('current_route_match');
    $instance->serviceRating = $container->get('erpw_webform.service_rating_service');
    $instance->location = $container->get('erpw_location.location_services');
    $instance->entityTypeManager = $container->get('entity_type.manager');
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
    $service_rating_webforms = [];
    foreach ($webforms as $webform => $webform_data) {
      if (str_contains($webform, 'webform_service_rating_')) {
        $form_questions_count = $this->getServiceRatingFormQuestionCount($webform_data->getElementsDecoded());
        $service_rating_webforms[] = [
          'webform_data' => $webform_data,
          'webform_id' => $webform_data->id(),
          'webform_title' => $webform_data->label(),
          'webform_questions_count' => $form_questions_count,
          'webform_status' => $webform_data->isOpen() ? 'Published' : 'Draft',
        ];
      }
    }
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
