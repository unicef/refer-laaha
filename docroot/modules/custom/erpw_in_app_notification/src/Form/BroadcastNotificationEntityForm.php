<?php

namespace Drupal\erpw_in_app_notification\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the broadcast notification entity entity edit forms.
 */
class BroadcastNotificationEntityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);

    $entity = $this->getEntity();

    $message_arguments = ['%label' => $entity->toLink()->toString()];
    $logger_arguments = [
      '%label' => $entity->label(),
      'link' => $entity->toLink($this->t('View'))->toString(),
    ];

    switch ($result) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('New broadcast notification entity %label has been created.', $message_arguments));
        $this->logger('erpw_in_app_notification')->notice('Created new broadcast notification entity %label', $logger_arguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The broadcast notification entity %label has been updated.', $message_arguments));
        $this->logger('erpw_in_app_notification')->notice('Updated broadcast notification entity %label.', $logger_arguments);
        break;
    }

    $form_state->setRedirect('entity.broadcast_notification_entity.canonical', ['broadcast_notification_entity' => $entity->id()]);

    return $result;
  }

}
