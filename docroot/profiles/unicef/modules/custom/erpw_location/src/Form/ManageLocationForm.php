<?php

namespace Drupal\erpw_location\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Url;

/**
 * Class ManageLocationForm.
 */
class ManageLocationForm extends FormBase {

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerChannelFactory
   */
  protected $logger;

  /**
   * Database Connection instance.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * A entityManager instance.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * ManageLocation constructor.
   *
   * @param \Psr\Log\LoggerChannelFactory $logger
   *   Logger object.
   * @param \Drupal\Core\Database\Connection $connection
   *   Connection Object.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entityManager
   *   EntityManager object.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form_builder service.
   */
  public function __construct(LoggerChannelFactory $logger, Connection $connection, EntityTypeManagerInterface $entityManager, MessengerInterface $messenger, FormBuilderInterface $form_builder) {
    $this->logger = $logger;
    $this->connection = $connection;
    $this->entityManager = $entityManager;
    $this->messenger = $messenger;
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory'),
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('messenger'),
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'manage_location_form_csv';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['enctype'] = "multipart/form-data";
    $form['open_modal'] = [
      '#type' => 'link',
      '#title' => $this->t('Import'),
      '#url' => Url::fromRoute('erpw_location.open_import_modal'),
      '#attributes' => [
        'class' => [
          'use-ajax',
          'button',
        ],
      ],
    ];
    $form['export_csv'] = [
      '#type' => 'submit',
      '#value' => $this->t('Export'),
      '#submit' => ['::exportLocationCsv'],
    ];
    $location_entities = $this->entityManager->getStorage('location')->loadByProperties(
      ['type' => 'country', 'status' => 1]);

    $location_options = [];
    foreach ($location_entities as $location) {
      $location_options[$location->id()] = $location->get('name')->getValue()[0]['value'];

    }
    if (!empty($location_entities)) {
      $form['location_options'] = [
        '#type' => 'select',
        '#options' => $location_options,
        '#empty_option' => t('Select Country'),
        '#title' => $this->t('Country'),
        '#ajax' => [
          'callback' => '::getLocationDetail',
          'event' => 'change',
          'wrapper' => 'edit-location-details',
          'progress' => [
            'type' => 'throbber',
          ],
        ],
      ];
    }

    $form['location_list'] = [
      '#prefix' => '<div id="edit-location-details">',
      '#suffix' => '</div>',
    ];
    if (!empty($form_state->getValue('location_options'))) {
      $location_entity_id = $form_state->getValue('location_options');
      $location_levels = \Drupal::service('erpw_location.location_services')->getLocationLevels($location_entity_id);
      $location_levels_count = count($location_levels);
      $location_entity = $this->entityManager->getStorage('location')->load($location_entity_id);
      $location_tid = $location_entity->get('field_location_taxonomy_term')->getValue()[0]['target_id'];
      $manager = $this->entityManager->getStorage('taxonomy_term');
      $tree = $manager->loadTree('country', $location_tid, $location_levels_count, TRUE);
      $locations = [];
      $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
      foreach ($tree as $term) {
        if ($term->depth == $location_levels_count - 1) {
          $tid = $term->id();
          $term_name = $term->hasTranslation($langcode) ? $term->getTranslation($langcode)->get('name')->value : $term->get('name')->value;
          $locations[$tid] = $term_name;
        }
      }
      natcasesort($locations);
      $form['location_list']['location_count'] = [
        '#type' => 'markup',
        '#markup' => '<div class="location-count edit-delete-links margin-space">' . count($locations) . ' ' . $this->t('Locations') . '</div>',
      ];
      foreach ($locations as $tid => $location) {
        $ancestors = $this->entityManager->getStorage('taxonomy_term')->loadAllParents($tid);
        $ancestors = array_reverse(array_keys($ancestors));
        $location_details = '';
        foreach ($location_levels as $key => $level) {
          $level_term = $this->entityManager->getStorage('taxonomy_term')->load($ancestors[$key + 1]);
          $level_data_name = $level_term->get('name')->value;
          if ($key !== array_key_last($location_levels)) {
            $location_details .= '<div class="level">' . $level . " : " . $level_data_name . '</div>';
          }
        }
        // @todo url routes to be updated.
        $clone_url = Url::fromRoute('erpw_location.manage_location')->toString();
        $delete_url = Url::fromRoute('erpw_location.manage_location')->toString();
        $edit_url = Url::fromRoute('erpw_location.manage_location')->toString();

        $location_operations = '<div class="edit-delete-links margin-space"> 
        <span class="clone-service-type"><a href="' . $clone_url . '">' . $this->t('Clone') . '</a></span>
        <span class="delete-link"><a href="' . $delete_url . '">' . $this->t('Delete') . '</a></span>
        <span class="edit-link"><a href="' . $edit_url . '">' . $this->t('Edit') . '</a></span>
        </div>';

        $form['location_list']['location_' . $tid] = [
          '#type' => 'markup',
          '#markup' => '<div class="location-card"><div class="title-with-icons"><div id="location-title" class="location-title">' . $location . '</div>
          <div class="location-operations">' . $location_operations . '</div></div><div class="location-details>' . $location_details . '</div></div> ',
        ];
      }
    }
    $form['#cache']['max-age'] = 0;
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#theme'] = 'manage_location_form';
    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function getLocationDetail(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild();
    return $form['location_list'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function exportLocationCsv(array &$form, FormStateInterface $form_state) {
    $location_id = $form_state->getValue('location_options');
    // Get Country Name.
    if ($location_id) {
      $country_name = $form['location_options']['#options'][$location_id];
    }
    else {
      $country_name = 'Country Name';
    }
    if (!$location_id) {
      $active_languages = \Drupal::languageManager()->getLanguages();
      $active_languages_list = array_keys($active_languages);
      $location_lang_count = 0;
      $location_lang = [];
      foreach ($active_languages_list as $langcode) {
        $location_lang_count++;
        $location_lang[$location_lang_count] = $langcode;
      }
      $csv_export = [];
      // Get First Row.
      $i = 0;
      for ($l = 1; $l <= 4; $l++) {
        foreach ($location_lang as $lang) {
          $column_name = 'level_' . $l . '_' . $lang;
          $csv_export[0][$i] = $column_name;
          $i++;
        }
      }
      $this->arrayCsvDownload($csv_export, $country_name);
    }
    else {
      $location = $this->entityManager->getStorage('location')->load($location_id);
      $active_languages = \Drupal::languageManager()->getLanguages();
      $active_languages_list = array_keys($active_languages);
      $location_lang_count = 0;
      $location_lang = [];
      foreach ($active_languages_list as $langcode) {
        if ($location->hasTranslation($langcode)) {
          $location_lang_count++;
          $location_lang[$location_lang_count] = $langcode;
        }
      }
      $csv_export = [];
      // Get First Row.
      $i = 0;
      for ($l = 1; $l <= 4; $l++) {
        foreach ($location_lang as $lang) {
          $column_name = 'level_' . $l . '_' . $lang;
          $csv_export[0][$i] = $column_name;
          $i++;
        }
      }
      // Get Header.
      $i = 0;
      foreach ($csv_export[0] as $column) {
        $level_name = explode("_", $column)[0] . '_' . explode("_", $column)[1];
        $langcode = explode("_", $column)[2];
        if ($location->getTranslation($langcode)->get($level_name)->getValue()) {
          $field_value = $location->getTranslation($langcode)->get($level_name)->getValue()[0]['value'];
        }
        else {
          $field_value = '';
        }
        $csv_export[1][$i] = $field_value;
        $i++;
      }
      $this->arrayCsvDownload($csv_export, $country_name);
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function arrayCsvDownload($array, $filename, $delimiter = ",") {

    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv";');

    // Clean output buffer.
    ob_end_clean();

    $handle = fopen('php://output', 'w');
    $headings = $array[0];
    $counter = 0;
    // Use keys as column titles.
    fputcsv($handle, $headings, $delimiter);
    foreach ($array as $value) {
      if ($counter++ == 0) {
        continue;
      }
      fputcsv($handle, $value, $delimiter);
    }

    fclose($handle);

    // Use exit to get rid of unexpected output afterward.
    exit();
  }

}
