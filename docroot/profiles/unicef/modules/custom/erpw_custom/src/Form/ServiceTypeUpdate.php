<?php

namespace Drupal\erpw_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\CloseModalDialogCommand;

/**
 * ModalForm class.
 */
class ServiceTypeUpdate extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'service_type_update';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
      
    if ($form_state->has('page') && $form_state->get('page') == 2) {
        return self::formPageTwo($form, $form_state);
    }

    $form_state->set('page', 1);

    $form['modal_description_1'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="review-msg">',
      '#markup' => $this->t('Are you sure you want to update the details?'),
      '#suffix' => '</div>',
    ];
    $form['modal_description_2'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="email-notify">',
      '#markup' => $this->t('Click on proceed to update or cancel to go back.'),
      '#suffix' => '</div>',
    ];

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Next'),
      '#attributes' => [
        'class' => [
          'signup-next',
        ],
      ],
      '#submit' => ['::submitPageOne'],
      '#validate' => ['::validatePageOne'],
    ];

    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('CANCEL'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitPageOne(array &$form, FormStateInterface $form_state) {
    $form_state->set('page', 2)->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function formPageTwo(array &$form, FormStateInterface $form_state) {
    $form['modal_description_1'] = [
        '#type' => 'markup',
        '#prefix' => '<div class="review-msg">',
        '#markup' => $this->t('Update successful'),
        '#suffix' => '</div>',
      ];
      $form['modal_description_2'] = [
        '#type' => 'markup',
        '#prefix' => '<div class="email-notify">',
        '#markup' => $this->t('The details has been sucessfully updated.'),
        '#suffix' => '</div>',
      ];
  
      $form['actions']['dashboard'] = [
        '#type' => 'submit',
        '#value' => $this->t('BACK TO DASHBOARD'),
        '#attributes' => [
          'class' => [
            'use-ajax',
            'ok-btn',
          ],
        ],
        '#ajax' => [
          'callback' => [$this, 'updatedServiceForm'],
          'event' => 'click',
        ],
      ];
    }

    /**
   * AJAX callback handler that displays any errors or a success message.
   */
  public function updatedServiceForm(array $form, FormStateInterface $form_state) {
    $response->addCommand(new RedirectCommand(\Drupal::request()->query->get('destination')));
    return $response;
  }

  /**
   * AJAX callback handler that displays any errors or a success message.
   */
  public function proceedAjax(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new CloseModalDialogCommand());
    $updated_service_type = \Drupal::formBuilder()->getForm('Drupal\erpw_custom\Form\UpdatedServiceType');
    $response->addCommand(new OpenModalDialogCommand('', $update_service_type, ['width' => '400']));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $response->addCommand(new RedirectCommand(\Drupal::service('path.current')->getPath()));
    return $response;
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['config.modal_form_example_modal_form'];
  }

}
