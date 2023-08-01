<?php

namespace Drupal\erpw_webform\Controller;

use Drupal\domain\DomainNegotiatorInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Utility\LinkGeneratorInterface;

/**
 * Class to generate webform links.
 */
class ServiceWebforms extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;


  /**
   * The domain negotiator interface object.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * The link generator service.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected $linkGenerator;

  /**
   * Constructs a new ServiceWebforms object.
   */
  public function __construct(LinkGeneratorInterface $linkGenerator, EntityTypeManagerInterface $entityTypeManager, DomainNegotiatorInterface $domainNegotiator) {
    $this->linkGenerator = $linkGenerator;
    $this->entityTypeManager = $entityTypeManager;
    $this->domainNegotiator = $domainNegotiator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
    $container->get('link_generator'),
    $container->get('entity_type.manager'),
    $container->get('domain.negotiator'),
    );
  }

  /**
   * Generate and return list of links of current domain's webform.
   */
  public function listForms() {
    // Build an array of links of all the webforms of the current domain.
    $currentDomain = $this->domainNegotiator->getActiveDomain()->id();
    $webforms = $this->entityTypeManager->getStorage('webform')->loadMultiple();
    $markup = '<h4 class="details-heading">' . t('Select the service type.') . '</h4>';
    // Loop through $webform.
    foreach ($webforms as $webform) {
      $tpa = $webform->getThirdPartySetting('erpw_webform', 'webform_service_type_map');
      if (!is_null($tpa)) {
        if (array_key_exists($currentDomain, $tpa)) {
          $url = Url::fromRoute('entity.webform.canonical', ['webform' => $webform->id()])->toString();
          $serviceType = $this->entityTypeManager->getStorage('node')->load($tpa[$currentDomain][0]);
          if ($serviceType) {
            $bgcolor = $serviceType->get('field_service_type_color')->getValue()[0]['color'];
            $serviceTitle = $serviceType->get('title')->getValue()[0]['value'];
            if ($bgcolor == '#B2A0D9') {
              $bgclass = 'apply-lavender';
            }
            elseif ($bgcolor == '#F4CBCA') {
              $bgclass = 'apply-peach';
            }
            elseif ($bgcolor == '#7FBC72') {
              $bgclass = 'apply-green';
            }
            elseif ($bgcolor == '#F9D14A') {
              $bgclass = 'apply-mustard-yellow';
            }
            else {
              $bgclass = '';
            }
          }
          else {
            $bgclass = '';
            $serviceTitle = '';
            \Drupal::logger('erpw_webform')->error('Service Type does not exist or has been deleted for Webform id %webform_id.',
            ['%webform_id' => $webform->id()]);
          }
          $markup = $markup . '
          <div class="service-providers-submission-row select-service-type-webform">
            <a href="' . $url . '">
              <div class="row-header">
                <div class="service-type-color-logo-container">
                  <div class="service-type-color ' . $bgclass . '"></div>
                  <div class="service-type-logo">
                    <i class="' . $bgcolor . '"></i>
                  </div>
                  <div class="service-type-org">
                    ' . $serviceTitle . '
                  </div>
                </div>
              </div>
            </a>
          </div>';
        }
      }
    }

    return [
      '#type' => 'markup',
      '#markup' => $markup,
    ];
  }

}
