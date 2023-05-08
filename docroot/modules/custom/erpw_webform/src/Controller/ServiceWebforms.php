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
    $links = [];
    $currentDomain = $this->domainNegotiator->getActiveDomain()->id();
    $webforms = $this->entityTypeManager->getStorage('webform')->loadMultiple();
    // Loop through $webform.
    foreach ($webforms as $webform) {
      $tpa = $webform->getThirdPartySetting('erpw_webform', 'webform_service_type_map');
      if (!is_null($tpa)) {

        if (array_key_exists($currentDomain, $tpa)) {
          $url = Url::fromRoute('entity.webform.canonical', ['webform' => $webform->id()]);
          $links[] = $this->linkGenerator->generate($webform->label(), $url);
        }
      }
    }

    $build = [
      '#theme' => 'item_list',
      '#items' => $links,
    ];
    return $build;
  }

}
