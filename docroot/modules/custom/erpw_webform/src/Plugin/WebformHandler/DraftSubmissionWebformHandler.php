<?php

namespace Drupal\erpw_webform\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Draft submission Webform handler.
 *
 * @WebformHandler(
 *   id = "redirect_to_drafts",
 *   label = @Translation("Redirect to Drafts"),
 *   category = @Translation("eRPW Webform Handler"),
 *   description = @Translation("Redirect to Drafts or Services Listing page based on transition."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class DraftSubmissionWebformHandler extends WebformHandlerBase {

  protected $messenger;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, MessengerInterface $messenger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->messenger = $messenger;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('messenger')
    );
  }

  /**
   * Implements Drupal\webform\Plugin\WebformHandlerInterface::confirmForm().
   */
  public function confirmForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {

    $workflow_selected = $webform_submission->getElementData('erpw_workflow');
    $alias = '/service-providers';
      $path = \Drupal::service('path_alias.manager')->getPathByAlias($alias);
      $url = Url::fromUri('internal:' . $path);
      $parameters = $url->getRouteParameters();
      if (isset($parameters['node'])) {
        $node = Node::load($parameters['node']);
        if ($node) {
          // Redirect the user to their saved Drafts if their transition was Save as Draft.
          if ($workflow_selected['transition'] == "save_as_draft") {
            $query = [
              'service_type' => 'All',
              'webform_submission_workflow_filter' => 'draft',
            ];
            $this->messenger->addMessage($this->t('Draft Saved Successfully!'));
            $form_state->setRedirect('entity.node.canonical', ['node' => $node->id()], ['query' => $query]);
          } 
          // For all other transitions, redirect them to Review page
          else {
            $this->messenger->addMessage($this->t('Changes Saved Successfully!'));
            $form_state->setRedirect('entity.node.canonical', ['node' => $node->id()]);
          }
        }
      }
  }

}
