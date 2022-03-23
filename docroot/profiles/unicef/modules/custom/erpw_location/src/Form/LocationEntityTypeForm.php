<?php

namespace Drupal\erpw_location\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * {@inheritdoc}
 */
class LocationEntityTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $location_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $location_type->label(),
      '#description' => $this->t("Label for the Location Entity type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $location_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\erpw_location\Entity\LocationEntityType::load',
      ],
      '#disabled' => !$location_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $location_type = $this->entity;
    $status = $location_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Location Entity type.', [
          '%label' => $location_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Location Entity type.', [
          '%label' => $location_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($location_type->toUrl('collection'));
  }

}
