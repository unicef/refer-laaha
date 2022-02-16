<?php

namespace Drupal\vss_common_config;

use Drupal\domain\DomainNegotiator;
use Drupal\domain_config_ui\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\language\ConfigurableLanguageManagerInterface;

/**
 * Class VssCommonService.
 *
 * This class/service is responsible for getting domain, language specific
 * data.
 */
class VssCommonService implements VssCommonInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\language\ConfigurableLanguageManagerInterface definition.
   *
   * @var \Drupal\language\ConfigurableLanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Drupal\domain_config_ui\Config\ConfigFactory definition.
   *
   * @var \Drupal\domain_config_ui\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiator
   */
  protected $domainNegotiator;

  /**
   * Constructs a new VssCommonService object.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    ConfigurableLanguageManagerInterface $language_manager,
    RequestStack $request,
    ConfigFactory $config_factory,
    DomainNegotiator $domain_negotiator) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->request = $request;
    $this->configFactory = $config_factory;
    $this->domainNegotiator = $domain_negotiator;
  }

  /**
   * Get domain specific config.
   */
  public function getVssDomainWithLanguageConfiguration() : array {
    $active_domain = $this->domainNegotiator->getActiveDomain();
    $lang_id = $this->languageManager->getCurrentLanguage()->getId();
    $host = $this->request->getCurrentRequest()->getHost();
    $row_data = [];
    if ($active_domain && $active_domain->getHostName() === $host) {
      $row_data = $this->configFactory
        ->get('domain.config.' . $active_domain->id() . '.' . $lang_id . '.vss_common_config.vsscommonconfig')
        ->getRawData();
    }
    return $row_data;
  }

  /**
   * Get domain specific config.
   */
  public function getVssDomainWithoutLanguageConfiguration() : array {
    $active_domain = $this->domainNegotiator->getActiveDomain();
    $host = $this->request->getCurrentRequest()->getHost();
    $row_data = [];
    if ($active_domain && $active_domain->getHostName() === $host) {
      $row_data = $this->configFactory
        ->get('domain.config.' . $active_domain->id() . '.vss_common_config.vsscommonconfig')
        ->getRawData();
    }
    return $row_data;
  }

  /**
   * Get domain specific config.
   */
  public function getVssCommonConfiguration(): array {
    $row_data = [];
    $row_data = $this->configFactory->get('vss_common_config.vsscommonconfig')->getRawData();
    return $row_data;
  }

  /**
   * Function to get footer details.
   */
  public function getFooterDetails(): array {
    $keys = [
      'phone',
      'email',
      'address',
    ];
    $data = $this->checkConfiguration($keys);
    $footer_details = [];
    if (isset($data['vss_common_config'])) {
      $footer_details['phone'] = $data['vss_common_config']['phone'] ?? '';
      $footer_details['email'] = $data['vss_common_config']['email'] ?? '';
      $footer_details['address'] = $data['vss_common_config']['address'] ?? '';
    }
    return $footer_details;
  }

  /**
   * Function to get disclaimer data.
   */
  public function getDisclaimer(): array {
    $keys = [
      'disclaimer_title',
      'disclaimer_description',
      'disclaimer_image',
    ];
    $data = $this->checkConfiguration($keys);
    $disclaimer = [];
    if (isset($data['vss_common_config'])) {
      $disclaimer['disclaimer_title'] = $data['vss_common_config']['disclaimer_title'];
      $disclaimer['disclaimer_description'] = $data['vss_common_config']['disclaimer_description']['value'];
      if (isset($data['vss_common_config']['disclaimer_image'])) {
        $file = $this->entityTypeManager->getStorage('file')->load($data['vss_common_config']['disclaimer_image'][0]);
        if ($file) {
          $disclaimer['disclaimer_image'] = file_create_url($file->getFileUri());
        }
      }
    }
    return $disclaimer;
  }

  /**
   * Function to get location selection data.
   */
  public function getLocationData(): array {
    $keys = [
      'location_selection_title',
      'location_selection_description',
    ];
    $data = $this->checkConfiguration($keys);
    $location_data = [];
    if (isset($data['vss_common_config'])) {
      $location_data['location_selection_title'] = $data['vss_common_config']['location_selection_title'];
      $location_data['location_selection_description'] = $data['vss_common_config']['location_selection_description']['value'];
    }
    return $location_data;
  }

  /**
   * Function to get disclaimer data.
   */
  public function getHeaderPhone(): array {
    $keys = [
      'header_country_code',
      'header_phone',
    ];
    $data = $this->checkConfiguration($keys);
    $header_phone = [];
    if (isset($data['vss_common_config'])) {
      $header_phone['header_country_code'] = trim($data['vss_common_config']['header_country_code']) ?? '';
      $header_phone['header_phone'] = trim($data['vss_common_config']['header_phone']) ?? '';
    }
    return $header_phone;
  }

  /**
   * Function to get Social Footer Icons.
   */
  public function getFooterSocialIcons(): array {
    $keys = [
      'social_twitter',
      'social_link_twitter',
      'social_youtube',
      'social_link_youtube',
      'social_insta',
      'social_link_insta',
    ];
    $data = $this->checkConfiguration($keys);
    $header_phone = [];
    if (isset($data['vss_common_config'])) {
      $header_phone['social_twitter'] = (int) $data['vss_common_config']['social_twitter'] ?? '';
      $header_phone['social_link_twitter'] = trim($data['vss_common_config']['social_link_twitter']) ?? '';
      $header_phone['social_youtube'] = (int) $data['vss_common_config']['social_youtube'] ?? '';
      $header_phone['social_link_youtube'] = trim($data['vss_common_config']['social_link_youtube']) ?? '';
      $header_phone['social_insta'] = (int) $data['vss_common_config']['social_insta'] ?? '';
      $header_phone['social_link_insta'] = trim($data['vss_common_config']['social_link_insta']) ?? '';
    }
    return $header_phone;
  }

  /**
   * Function to get catgeories.
   */
  public function getCategories(): array {
    $keys = [
      'get_help',
      'homepage_hero',
    ];
    $data = $this->checkConfiguration($keys);
    $categories = [];
    if (isset($data['vss_common_config'])) {
      $categories['get_help'] = !empty($data['vss_common_config']['get_help']) ? trim($data['vss_common_config']['get_help']) : '';
      $categories['homepage_hero'] = $data['vss_common_config']['homepage_hero'] ?? '';
    }
    return $categories;
  }

  /**
   * Get actual configuration based on conditions.
   */
  protected function checkConfiguration($keys = [], $default = TRUE) {
    $data = [];

    $data = $this->getVssDomainWithLanguageConfiguration();
    if (!empty($data) && $default) {
      if ($this->checkValueByKey($keys, $data)) {
        return $data;
      }
    }
    $data = $this->getVssDomainWithoutLanguageConfiguration();
    if (!empty($data) && $default) {
      if ($this->checkValueByKey($keys, $data)) {
        return $data;
      }
    }
    else {
      $data = $this->getVssCommonConfiguration();
    }
    return $data;
  }

  /**
   * Check values in config by keys.
   */
  protected function checkValueByKey($keys, $data = []) {
    if ($keys) {
      foreach ($keys as $key) {
        // Return True if any 1 values exists for key.
        if (isset($data['vss_common_config'][$key])
        && !empty($data['vss_common_config'][$key])) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

}
