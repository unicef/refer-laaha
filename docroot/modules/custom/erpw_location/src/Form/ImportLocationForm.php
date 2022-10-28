<?php

namespace Drupal\erpw_location\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\erpw_location\LocationImportProcess;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form that allows users to import location.
 */
class ImportLocationForm extends FormBase {

  /**
   * The location import service.
   *
   * @var \Drupal\erpw_location\LocationImportProcess
   */
  protected $importProcess;

  /**
   * ImportLocationForm constructor.
   *
   * @param \Drupal\erpw_location\LocationImportProcess $import_process
   *   The location import service.
   */
  public function __construct(LocationImportProcess $import_process) {
    $this->importProcess = $import_process;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('erpw_location.import_location')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'import_location_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['import_location_title'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="review-msg">',
      '#markup' => $this->t('Upload Location CSV'),
      '#suffix' => '</div>',
    ];
    $form['import_location_csv_file'] = [
      '#type' => 'managed_file',
      '#multiple' => FALSE,
      '#size' => 15,
      '#upload_location' => 'public://location_csv',
      "#upload_validators" => ["file_validate_extensions" => ["csv"]],
    ];
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!isset($form_state->getValue('import_location_csv_file')[0])) {
      $message = $this->t('Please upload the Location CSV file to start the import process.');
      $form_state->setError($form['import_location_csv_file'], $message);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file_obj = $form_state->getValue('import_location_csv_file');
    if ($file_obj) {
      $this->importProcess->setBatch($file_obj);
    }
  }

}
