<?php

namespace Drupal\erpw_custom\Services;

use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * ErpwCustomService Service Class.
 */
class ErpwCustomService {

  /**
   * The name of the cookie.
   *
   * @var string
   */
  protected $cookieName = 'userLanguage';

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  public function __construct(LanguageManagerInterface $language_manager,
    RequestStack $request_stack) {
    $this->languageManager = $language_manager;
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * Get current language.
   *
   * @return string
   *   Return language id.
   */
  public function getCurrentLanguage() {
    return $this->languageManager->getCurrentLanguage()->getId();
  }

  /**
   * Get User language.
   *
   * @return string
   *   Return User language id.
   */
  public function getUserLanguage() {
    return $this->request->cookies->get($this->getCookieName()) ?? 'en';
  }

  /**
   * Get this cookie's name.
   *
   * @return string
   *   Return cookie name.
   */
  public function getCookieName() {
    return $this->cookieName;
  }

}
