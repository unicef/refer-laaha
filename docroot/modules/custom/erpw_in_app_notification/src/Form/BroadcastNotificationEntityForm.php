<?php

namespace Drupal\erpw_in_app_notification\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the broadcast notification entity entity edit forms.
 */
class BroadcastNotificationEntityForm extends ContentEntityForm {

  /**
   * Drupal\domain\DomainNegotiatorInterface definition.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->domainNegotiator = $container->get('domain.negotiator');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\erpw_in_app_notification\Entity\NotificationEntity $entity */
    $form = parent::buildForm($form, $form_state);
    $current_domain_id = $this->domainNegotiator->getActiveDomain()->id();
    unset($form['field_roles']['widget']['#options']['administrator']);
    unset($form['field_roles']['widget']['#options']['anonymous']);
    unset($form['field_roles']['widget']['#options']['authenticated']);
    unset($form['field_roles']['widget']['#options']['super_admin']);
    if (in_array($current_domain_id, ['bn_erefer_org', 'sl_erefer_org'])) {
      unset($form['field_roles']['widget']['#options']['service_provider_staff']);
      if ($current_domain_id == 'bn_erefer_org') {
        $form['field_roles']['widget']['#options']['service_provider_focal_point'] = 'GBV Service Provider Focal Point';
      }
    }
    // Remove GBV FP from all domains except BN.
    if ($current_domain_id != 'bn_erefer_org') {
      unset($form['field_roles']['widget']['#options']['gbv_focal_point']);
    }
    $organisation_options = $form['field_organisation']['widget']['#options'];
    foreach ($organisation_options as &$value) {
      $value = trim($value);
    }

    // Alphabetic sorting.
    asort($organisation_options);

    // Set the updated options back to the form.
    $form['field_organisation']['widget']['#options'] = $organisation_options;
    if (in_array('broadcast-notification-entity-edit-form', $form['#attributes']['class'])) {
      $form['field_location']['#disabled'] = TRUE;
      $form['field_organisation']['#disabled'] = TRUE;
      $form['field_type']['#disabled'] = TRUE;
    }
    return $form;
  }

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
    if ($form_state->getValue('field_message_type')[0]['value'] == 'custom') {
      $form_state->setRedirect('entity.broadcast_notification_entity.content_translation_overview', ['broadcast_notification_entity' => $entity->id()]);
    }
    else {
      $form_state->setRedirect('entity.broadcast_notification_entity.collection');
    }
    return $result;
  }

}
