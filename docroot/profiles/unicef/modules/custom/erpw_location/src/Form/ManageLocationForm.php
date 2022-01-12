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
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\views\Views;

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
    $this->entityManager = $entityManager->getStorage('location');
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
    $location_entities = $this->entityManager->loadByProperties(
      ['type' => 'country', 'status' => 1]);

      // kint($location_entities);exit;
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
            'type' => 'throbber'
          ],
        ],
      ];
    }

    $form['location_list'] = [ 
      '#prefix' => '<div id="edit-location-details">',
      '#suffix' => '</div>',
    ];
    // $location_values = '';
    $last_level_value = '';
    $location_labels = '';
    if ($form_state->getValue('location_options') != FALSE) {
      $location_entity_id = !empty($form_state->getValue('location_options')) ? $form_state->getValue('location_options') : '';
      $location_levels = \Drupal::service('erpw_location.location_services')->getLocationLevels($location_entity_id);
      $location_levels_count = count($location_levels);
      $location_entity = \Drupal::entityTypeManager()->getStorage('location')->load($location_entity_id);
      $location_tid = $location_entity->get('field_location_taxonomy_term')->getValue()[0]['target_id'];
      $manager = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
      $tree = $manager->loadTree('country', $location_tid, $location_levels_count, TRUE);
      $result = [];
      $location_term_name = [];
      foreach ($tree as $term) {
        if ($term->depth == $location_levels_count - 1) {
          $tid = $term->id();
          $term_name = Term::load($tid)->get('name')->value;
          $location_term_name[$term_name] = $term_name;
          
        }
      }
      foreach ($location_levels as $key => $location_levels_values){
        $location_labels .= "<div class='level-labels'>$location_levels_values";
        $location_labels .= "</div>";
      }
      // $ancestors = \Drupal::service('entity_type.manager')->getStorage("taxonomy_term")->loadAllParents($tid);
      // print_r($location_labels);
      // die;
      $location_term_name_count = count($location_term_name);
      
      // $string = "<div class='location-list'>";
      foreach($location_term_name as $key => $value){
        $last_level_value .= "<div class='location-list'>$value";
        $last_level_value .= "</div>";
        // $string .= "</div>";
      }
    }
    
    if (!empty($form_state->getValue('location_options'))) {
      $form['location_list']['location_label'] = [
        '#type' => 'markup',
        '#markup' => t('update locations'),
        '#prefix' => '<div id="location-title"></div>'
      ];
      $form['location_list']['location_count'] = [
        '#type' => 'markup',
        '#markup' => $location_term_name_count . t(' Locations '),
        '#prefix' => '<div id="location-count"></div>'
      ];
      $form['location_list']['location'] = [
        '#type' => 'markup',
        '#markup' => $last_level_value,
        '#prefix' => '<div id="location-details"></div>'
      ];
      $form['location_list']['location_labels'] = [
        '#type' => 'markup',
        '#markup' => $location_labels,
        '#prefix' => '<div id="location-labels"></div>'
      ];
    }
    // \Drupal::logger('hello lokesh')->notice('<pre><code>' . print_r($form['location_list']['location'], TRUE) . '</code></pre>' );
    $form['#cache']['max-age'] = 0; 
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#theme'] = 'manage_location_form';
    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function getLocationDetail(array &$form, FormStateInterface $form_state) {

    // Just return redendered location list.
    // return $form['location_list'];
    $location_entity_id = !empty($form_state->getValue('location_options')) ? $form_state->getValue('location_options') : '';
      $location_levels = \Drupal::service('erpw_location.location_services')->getLocationLevels($location_entity_id);
      $location_levels_count = count($location_levels);
      $location_entity = \Drupal::entityTypeManager()->getStorage('location')->load($location_entity_id);
      $location_tid = $location_entity->get('field_location_taxonomy_term')->getValue()[0]['target_id'];
      $manager = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
      $tree = $manager->loadTree('country', $location_tid, $location_levels_count, TRUE);
      $result = [];
      $location_term_name = [];
      foreach ($tree as $term) {
        if ($term->depth == $location_levels_count - 1) {
          $tid = $term->id();
          $term_name = Term::load($tid)->get('name')->value;
          $location_term_name[$term_name] = $term_name;
          
        }
      }

    $depth = [$location_levels_count + 1] ;
    $view_result = [];
    $view = Views::getView('location_list');
    if (is_object($view)) {
      $view->setDisplay('page_1');
      $view->setExposedInput(['depth_level' => $depth]);
      $view->preExecute();
      $view->execute();
      // $view->setArguments($depth);
      
      
      // $exposed_filters = ['depth_level' => 5];
      // $filters = $view->getDisplay()->getOption('filters');
      // $filters["depth_level"]["value"] = 5;
      // $view->setExposedInput(['depth_level' => $depth]);
      // $view->exposed_input['depth_level'] = $category;
      // $view->display_handler->overrideOption('filters', $filters);
      // $view->exposed_input = array_merge($exposed_filters, (array)$view->exposed_input);
      // $view->exposed_raw_input = array_merge($exposed_filters, (array)$view->exposed_raw_input);
      // $view->exposed_data = array_merge($exposed_filters, (array)$view->exposed_data);
      // $view->display_handler->display->display_options['filters']['depth_level']['default_value'] = '5';
      // $view->display_handler->handlers['filter']['depth_level']->validated_exposed_input = '5';
      // $view->setExposedInput($exposed_filters);
      // $view->execute();
      // $view_result = !empty($view->result) ? $view->result : '';
      $rendered = $view->render();
      $output = \Drupal::service('renderer')->render($rendered);
    }
    print_r($output);
    die;
    // $location_term_name_count = count($location_term_name);
    // $response = new AjaxResponse();
    // // $messages = \Drupal::service('renderer')->render($location_term_name);
    // // print_r($messages);
    // // die;
    // $response->addCommand(new HtmlCommand('#edit-location-details', $view_result));
    // return $response;
    
    // return new Response(render($location_term_name));
    
    // Get depth of a given country.

    // Get the taxonomy term reference target id . from location entity.

    // Get the locations values at thte given depth of the given taxonomy.
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
      $location = $this->entityManager->load($location_id);
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
