<?php

namespace Drupal\vss_common_config\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Cache\CacheTagsInvalidator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class VssCommonConfigForm.
 *
 * Common configuration form for vss site.
 */
class VssCommonConfigForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidator
   */
  protected $cacheTagsInvalidator;

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * Construct function.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Cache\CacheTagsInvalidator $cache_tags_invalidator
   *   The cache tags invalidator.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request stack.
   * @param \Drupal\Core\Database\Driver\mysql\Connection $database
   *   The database connection.
   */
  public function __construct(
    EntityTypeManager $entity_type_manager,
    CacheTagsInvalidator $cache_tags_invalidator,
    RequestStack $request,
    Connection $database
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
    $this->request = $request;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('cache_tags.invalidator'),
      $container->get('request_stack'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'vss_common_config.vsscommonconfig',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vss_common_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('vss_common_config.vsscommonconfig');
    $common_config = $config->get('vss_common_config');
    $form['vsscommonconfig'] = [
      '#type' => 'vertical_tabs',
    ];

    $form['footer_details'] = [
      '#type' => 'details',
      '#title' => 'Footer Contact Information',
      '#group' => 'vsscommonconfig',
    ];

    $form['footer_details']['phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone'),
      '#description' => $this->t('Enter Phone Number'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => !empty($common_config['phone']) ? $common_config['phone'] : '',
    ];
    $form['footer_details']['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#description' => $this->t('Enter Email'),
      '#default_value' => !empty($common_config['email']) ? $common_config['email'] : '',
    ];
    $form['footer_details']['address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Address'),
      '#description' => $this->t('Address for Domain'),
      '#default_value' => !empty($common_config['address']) ? $common_config['address'] : '',
      '#maxlength' => 255,
      '#size' => 64,
    ];

    $form['disclaimer'] = [
      '#type' => 'details',
      '#title' => $this->t('Disclaimer'),
      '#group' => 'vsscommonconfig',
    ];

    $form['disclaimer']['disclaimer_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ttile'),
      '#default_value' => !empty($common_config['disclaimer_title']) ? $common_config['disclaimer_title'] : '',
    ];

    $form['disclaimer']['disclaimer_description'] = [
      '#type' => 'text_format',
      '#format' => 'full_html',
      '#title' => $this->t('Message'),
      '#default_value' => !empty($common_config['disclaimer_description']) ? $common_config['disclaimer_description']['value'] : '',
    ];
    $form['disclaimer']['disclaimer_image'] = [
      '#type'                 => 'managed_file',
      '#upload_location'      => 'public://disclaimer/',
      '#multiple'             => FALSE,
      '#description'          => $this->t('Allowed extensions: gif png jpg jpeg'),
      '#upload_validators'    => [
        'file_validate_is_image'      => [],
        'file_validate_extensions'    => ['gif png jpg jpeg'],
        'file_validate_size'          => [25600000],
      ],
      '#title'                => $this->t('Upload an image file.'),
      '#default_value' => !empty($common_config['disclaimer_image']) ? [$common_config['disclaimer_image'][0]] : '',
    ];

    $form['header_phone'] = [
      '#type' => 'details',
      '#title' => 'Header Phone Information',
      '#group' => 'vsscommonconfig',
    ];
    $form['header_phone']['header_country_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Country Code'),
      '#description' => $this->t('Enter Country Code'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => !empty($common_config['header_country_code']) ? $common_config['header_country_code'] : '',
    ];
    $form['header_phone']['header_phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header Phone'),
      '#description' => $this->t('Enter Header Phone Number'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => !empty($common_config['header_phone']) ? $common_config['header_phone'] : '',
    ];

    $form['social_links'] = [
      '#type' => 'details',
      '#title' => 'Footer Social Link information',
      '#group' => 'vsscommonconfig',
    ];
    $form['social_links']['social_twitter'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add twitter'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => !empty($common_config['social_twitter']) ? $common_config['social_twitter'] : '',
    ];

    $form['social_links']['social_link_twitter'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Add twitter link'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => !empty($common_config['social_link_twitter']) ? $common_config['social_link_twitter'] : '',
    ];
    $form['social_links']['social_youtube'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add youtube'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => !empty($common_config['social_youtube']) ? $common_config['social_youtube'] : '',
    ];

    $form['social_links']['social_link_youtube'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Add youtube link'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => !empty($common_config['social_link_youtube']) ? $common_config['social_link_youtube'] : '',
    ];
    $form['social_links']['social_insta'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add insta'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => !empty($common_config['social_insta']) ? $common_config['social_insta'] : '',
    ];

    $form['social_links']['social_link_insta'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Add insta link'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => !empty($common_config['social_link_insta']) ? $common_config['social_link_insta'] : '',
    ];

    $form['categories'] = [
      '#type' => 'details',
      '#title' => 'Categories',
      '#group' => 'vsscommonconfig',
    ];

    $domain = $this->request->getCurrentRequest()->query->get('domain_config_ui_domain');
    $langcode = $this->request->getCurrentRequest()->query->get('domain_config_ui_language');
    $tax_opt = $this->getCategoriesforTaxonomy($domain, $langcode);

    $form['categories']['get_help'] = [
      '#type' => 'select',
      '#title' => $this->t('Get help category'),
      '#options' => ['' => 'Select'] + $tax_opt,
      '#default_value' => !empty($common_config['get_help']) ? $common_config['get_help'] : '',
    ];

    $form['categories']['homepage_hero'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Hompage hero Categories'),
      '#options' => $tax_opt,
      '#default_value' => !empty($common_config['homepage_hero']) ? $common_config['homepage_hero'] : [],
    ];

    $form['location_selection'] = [
      '#type' => 'details',
      '#title' => $this->t('Location Selection Page'),
      '#group' => 'vsscommonconfig',
    ];

    $form['location_selection']['location_selection_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => !empty($common_config['location_selection_title']) ? $common_config['location_selection_title'] : '',
    ];

    $form['location_selection']['location_selection_description'] = [
      '#type' => 'text_format',
      '#format' => 'full_html',
      '#title' => $this->t('Description'),
      '#default_value' => !empty($common_config['location_selection_description']) ?
      $common_config['location_selection_description']['value'] : '',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Validate phone number.
   */
  public function validatePhoneNumber($phone) {
    if (preg_match("/[a-z]/i", $phone) || preg_match('/[\'^£$%&*()}{@#~?><>,|=_¬]/', $phone) || !filter_var($phone, FILTER_SANITIZE_NUMBER_INT)) {
      return FALSE;
    }
    // Allow +, - and . in phone number.
    $filtered_phone_number = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
    // Remove "-" from number.
    $phone_to_check = str_replace("-", "", $filtered_phone_number);
    // Check the lenght of number
    // This can be customized if you want phone number from a specific country.
    if (strlen($phone_to_check) < 10 || strlen($phone_to_check) > 14) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Get categories taxonomy.
   */
  public function getCategoriesforTaxonomy($domain, $langcode) {
    $query = $this->database->select('taxonomy_term_field_data', 't');
    $query->join('taxonomy_term__field_domain', 'fd', 'fd.entity_id = t.tid');
    $query->leftjoin('taxonomy_term__field_sub_category', 'sc', 'sc.entity_id = t.tid');
    if ($langcode) {
      $query->condition('t.langcode', $langcode);
    }
    $query->condition('fd.bundle', 'categories');
    if ($domain) {
      $query->condition('field_domain_target_id', $domain);
    }
    $query->condition('sc.field_sub_category_value', 1, '!=');
    $query->addExpression('t.tid', 'tid');
    $query->addExpression('t.name', 'name');
    $terms = $query->execute()->fetchAllKeyed(0, 1);
    return $terms;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $phone = $form_state->getValue('phone');
    if (!empty($phone) && !$this->validatePhoneNumber($phone)) {
      $form_state->setErrorByName("phone",
       $this->t('Please enter valid phone number.'));
    }
    $email = $form_state->getValue('email');
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $form_state->setErrorByName(
        "email",
        $this->t('Please enter valid email id.')
      );
    }
    $homepage_hero = $form_state->getValue('homepage_hero');
    if (!empty($homepage_hero) && count(array_filter($homepage_hero)) > 6) {
      $form_state->setErrorByName(
        "homepage_hero",
        $this->t('Please select only 6 categories in Hompage hero Categories')
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config('vss_common_config.vsscommonconfig');
    $config->set('vss_common_config', $form_state->getValues());
    $config->save();
    if ($image = $form_state->getValue('disclaimer_image')) {
      $file = $this->entityTypeManager->getStorage('file')->load($image[0]);
      $file->setPermanent();
      $file->save();
    }
    // Invalidate vss_common_config.
    $this->cacheTagsInvalidator->invalidateTags(['vss_common_config']);
  }

}
