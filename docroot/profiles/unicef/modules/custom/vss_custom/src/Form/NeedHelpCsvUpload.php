<?php

namespace Drupal\vss_custom\Form;

use Drupal\file\Entity\File;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures forms module settings.
 */
class NeedHelpCsvUpload extends FormBase {

  /**
   * Constructs a new ExitWebsite form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    global $base_url;
    $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
    $languages = \Drupal::languageManager()->getLanguages();
    foreach ($languages as $langcode => $language) {
      if (array_key_exists($langcode, $prefixes)) {
        $lang[$langcode] = $language->getName();
      }
    }
    $form['language'] = [
      '#title' => $this->t('Language'),
      '#type' => 'select',
      '#options' => ['' => '- Select - '] + $lang,
      '#default_value' => '',
    ];

    $form['need_help_upload'] = [
      '#type' => 'managed_file',
      '#title' => t('Choose a CSV file for Need Heelp Service Provider'),
      '#progress_indicator' => 'bar',
      '#progress_message' => t('Please wait....File Uploading in Progress'),
      '#default_value' => '',
      "#upload_validators" => ["file_validate_extensions" => ["csv"]],
      '#states' => [
        'visible' => [
          ':input[name="File_type"]' => ['value' => t('Upload CSV File')],
        ],
      ],
    ];
    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Upload CSV'),
      '#button_type' => 'primary',
      '#name' => 'upload_csv',
    ];

    return $form;
  }

  /**
   * Getter method for Form ID.
   *
   * The form ID is used in implementations of hook_form_alter() to allow other
   * modules to alter the render array built by this form controller. It must be
   * unique site wide. It normally starts with the providing module's name.
   *
   * @return string
   *   The unique ID of the form defined by this class.
   */
  public function getFormId() {
    return 'need_help_form';
  }

  /**
   * Getter method for Form ID.
   *
   * The form ID is used in implementations of hook_form_alter() to allow other
   * modules to alter the render array built by this form controller. It must be
   * unique site wide. It normally starts with the providing module's name.
   *   The unique ID of the form defined by this class.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $form_submitted = $form_state->getValue('upload_csv');
    $values = $form_state->getValues();
    $language = $values['language'];
    $errors = '';
    $csv_file = $form_state->getValue('need_help_upload');
    /* Load the object of the file by it's fid */
    if (empty($language)) {
      $errors .= 'Select Language in dropdown <br>';
    }

    if ($csv_file) {
      if (!empty($form_submitted)) {
        $file = File::load($csv_file[0]);
        $csv_datas = $this->csvtoarray($file->getFileUri(), ',');
        $csv_line = 2;
        $csv_mandatory_fields = ['Service Name',
          'Organisation Name',
          'Telephone Number',
          'Hierarchy level 1',
          'Hierarchy level 2',
          'City',
          'Country',
          'Priority',
        ];
        foreach ($csv_datas as $item) {
          $item = array_map('trim', $item);
          $item_keys = array_keys($item);
          foreach ($item_keys as $key) {
            if (in_array($key, $csv_mandatory_fields) && empty($item[$key])) {
              $errors .= 'Line No: ' . $csv_line . " - " . $key . " can't be empty" . '<br>';
            }
          }
          $csv_line++;
        }
      }
    }
    else {
      $errors .= 'File Upload Failed.';
    }

    if (!empty($errors)) {
      $form_state->setErrorByName('csv', $errors);
      return;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $language = $values['language'];

    /* Fetch the array of the file stored temporarily in database */
    $csv_file = $form_state->getValue('need_help_upload');

    /* Load the object of the file by it's fid */
    $file = File::load($csv_file[0]);
    $datas = $this->csvtoarray($file->getFileUri(), ',');
    $operations = [];
    $data_chunk = array_chunk($datas, 100);

    foreach ($data_chunk as $data) {
      $operations[] = ['\Drupal\vss_custom\ImportNeedHelpData::addImportContentItem',
      [$data, $language],
      ];
    }
    $batch = [
      'title' => t('Importing Data...'),
      'operations' => $operations,
      'init_message' => t('Import is starting.'),
      'finished' => '\Drupal\vss_custom\ImportNeedHelpData::addImportContentItemCallback',
    ];
    batch_set($batch);
  }

  /**
   * CSV to Array.
   */
  public function csvtoarray($filename = '', $delimiter = '') {
    if (!file_exists($filename) || !is_readable($filename)) {
      return FALSE;
    }
    $header = NULL;
    $data = [];

    if (($handle = fopen($filename, 'r')) !== FALSE) {
      while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
        if (!$header) {
          $header = $row;
        }
        else {
          $data[] = array_combine($header, $row);
        }
      }
      fclose($handle);
    }

    return $data;
  }

  /**
   * Headers.
   */
  public function getNeedHelpheaders() {
    $header = [
      'Service Name',
      'Service Provider Name',
      'Email ID',
      'Telephone Number',
      'Hierarchy level 1',
      'Hierarchy level 2',
      'Hierarchy level 3',
      'Hierarchy level 4',
      'Priority',
      'Facebook',
      'Twitter',
      'LinkedIn',
      'City',
      'State',
      'Country',
      'Description',
    ];
    return $header;
  }

}
