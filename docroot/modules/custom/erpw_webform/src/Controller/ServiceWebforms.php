<?php

namespace Drupal\erpw_webform\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\domain\DomainNegotiatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    $cid = 'service_webforms_list' . $this->domainNegotiator->getActiveDomain()->id();
    $markup = '';
    // Specify cache tags related to webforms.
    $cacheTags = ['webform'];
    if ($cache = \Drupal::cache()->get($cid)) {
      $markup = $cache->data;
    }
    else {
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
            if ($serviceType && $serviceType->hasField('field_service_type_color')) {
              $bgcolor = $serviceType->get('field_service_type_color')?->getValue()[0]['color'];
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
              $markup = $markup . '
          <div class="service-providers-submission-row select-service-type-webform">
            <a href="' . $url . '">
              <div class="row-header">
                <div class="service-type-color-logo-container">
                  <div class="service-type-color ' . $bgclass . '"></div>
                  <div class="service-type-logo">
                    <i class="' . $serviceType->get('field_service_type_icon')->getValue()[0]['value'] . '"></i>
                  </div>
                  <div class="service-type-org">
                    ' . $serviceType->get('title')->getValue()[0]['value'] . '
                  </div>
                </div>
              </div>
            </a>
          </div>';
            }
            else {
              \Drupal::logger('erpw_webform')->error(
              'Service Type does not exist or has been deleted for Webform id %webform_id.',
              ['%webform_id' => $webform->id()]
                      );
            }
          }
        }

        // Add cache tag for each webform.
        $cacheTags[] = 'webform:' . $webform->id();
      }
      \Drupal::cache()->set($cid, $markup, Cache::PERMANENT, $cacheTags);
    }

    // @todo Cache computed value.
    return [
      '#type' => 'markup',
      '#markup' => $markup,
    ];
  }

}
