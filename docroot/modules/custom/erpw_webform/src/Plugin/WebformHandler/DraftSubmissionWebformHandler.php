<?php

namespace Drupal\erpw_webform\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Draft submission Webform handler.
 *
 * @WebformHandler(
 *   id = "redirect_to_drafts",
 *   label = @Translation("Redirect on Transition changes."),
 *   category = @Translation("eRPW Webform Handler"),
 *   description = @Translation("Redirect to Drafts or Services Listing page based on transition."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class DraftSubmissionWebformHandler extends WebformHandlerBase {

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The Path alias service.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MessengerInterface $messenger, AliasManagerInterface $alias_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->messenger = $messenger;
    $this->aliasManager = $alias_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('messenger'),
      $container->get('path_alias.manager')
    );
  }

  /**
   * Implements Drupal\webform\Plugin\WebformHandlerInterface::confirmForm().
   */
  public function confirmForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {

    $workflow_selected = $webform_submission->getElementData('erpw_workflow');
    $draft_alias = '/service-providers';
    $manage_service_alias = '/manage-services';
    $draft_path = $this->aliasManager->getPathByAlias($draft_alias);
    $manage_service_path = $this->aliasManager->getPathByAlias($manage_service_alias);

    // Redirect the user to their saved Drafts if their transition was Save as Draft.
    if ($workflow_selected['transition'] == "save_as_draft") {
      $query = [
        'service_type' => 'All',
        'webform_submission_workflow_filter' => 'draft',
      ];
      $this->messenger->addMessage($this->t('Draft Saved Successfully!'));
      $form_state->setRedirectUrl(Url::fromUri('internal:' . $draft_path, ['query' => $query]));
    }
    else {
      $this->messenger->addMessage($this->t('Changes Saved Successfully!'));
      $form_state->setRedirectUrl(Url::fromUri('internal:' . $manage_service_path));
    }
  }

}
