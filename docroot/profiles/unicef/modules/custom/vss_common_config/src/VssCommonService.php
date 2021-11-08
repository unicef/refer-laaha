<?php

namespace Drupal\vss_common_config;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Drupal\domain_config_ui\Config\ConfigFactory;
use Drupal\domain\DomainNegotiator;
use Symfony\Component\HttpFoundation\RequestStack;

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
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigurableLanguageManagerInterface $language_manager, RequestStack $request, ConfigFactory $config_factory, DomainNegotiator $domainNegotiator) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->request = $request;
    $this->configFactory = $config_factory;
    $this->domainNegotiator = $domainNegotiator;
  }

  /**
   * Get domain specific config.
   */
  public function getVssDomainWithLanguageConfiguration() : array {
    $activeDomain = $this->domainNegotiator->getActiveDomain();
    $langId = $this->languageManager->getCurrentLanguage()->getId();
    $host = $this->request->getCurrentRequest()->getHost();
    $rawData = [];
    if ($activeDomain && $activeDomain->getHostName() === $host) {
      $rawData = $this->configFactory->get('domain.config.' . $activeDomain->id() . '.' . $langId . '.vss_common_config.vsscommonconfig')->getRawData();
    }
    return $rawData;
  }

  /**
   * Get domain specific config.
   */
  public function getVssDomainWithoutLanguageConfiguration() : array {
    $activeDomain = $this->domainNegotiator->getActiveDomain();
    $host = $this->request->getCurrentRequest()->getHost();
    $rawData = [];
    if ($activeDomain && $activeDomain->getHostName() === $host) {
      $rawData = $this->configFactory->get('domain.config.' . $activeDomain->id() . '.vss_common_config.vsscommonconfig')->getRawData();
    }
    return $rawData;
  }

  /**
   * Get domain specific config.
   */
  public function getVssCommonConfiguration(): array {
    $rawData = [];
    $rawData = $this->configFactory->get('vss_common_config.vsscommonconfig')->getRawData();
    return $rawData;
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
    $footerDetails = [];
    if (isset($data['vss_common_config'])) {
      $footerDetails['phone'] = $data['vss_common_config']['phone'] ?? '';
      $footerDetails['email'] = $data['vss_common_config']['email'] ?? '';
      $footerDetails['address'] = $data['vss_common_config']['address'] ?? '';
    }
    return $footerDetails;
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
   * Function to get disclaimer data.
   */
  public function getHeaderPhone(): array {
    $keys = [
      'header_country_code',
      'header_phone',
    ];
    $data = $this->checkConfiguration($keys);
    $headerPhone = [];
    if (isset($data['vss_common_config'])) {
      $headerPhone['header_country_code'] = trim($data['vss_common_config']['header_country_code']) ?? '';
      $headerPhone['header_phone'] = trim($data['vss_common_config']['header_phone']) ?? '';
    }
    return $headerPhone;
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
    $headerPhone = [];
    if (isset($data['vss_common_config'])) {
      $headerPhone['social_twitter'] = trim($data['vss_common_config']['social_twitter']) ?? '';
      $headerPhone['social_link_twitter'] = trim($data['vss_common_config']['social_link_twitter']) ?? '';
      $headerPhone['social_youtube'] = trim($data['vss_common_config']['social_youtube']) ?? '';
      $headerPhone['social_link_youtube'] = trim($data['vss_common_config']['social_link_youtube']) ?? '';
      $headerPhone['social_insta'] = trim($data['vss_common_config']['social_insta']) ?? '';
      $headerPhone['social_link_insta'] = trim($data['vss_common_config']['social_link_insta']) ?? '';
    }
    return $headerPhone;
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
