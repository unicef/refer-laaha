<?php

namespace Drupal\erpw_location\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Location Entity revision.
 *
 * @ingroup erpw_location
 */
class LocationEntityRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The Location Entity revision.
   *
   * @var \Drupal\erpw_location\Entity\LocationEntityInterface
   */
  protected $revision;

  /**
   * The Location Entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $locationEntityStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->locationEntityStorage = $container->get('entity_type.manager')->getStorage('location');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'location_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', [
      '%revision-date' => \Drupal::service('date.formatter')->format($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.location.version_history', ['location' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $location_revision = NULL) {
    $this->revision = $this->LocationEntityStorage->loadRevision($location_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->LocationEntityStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Location Entity: deleted %title revision %revision.', [
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId(),
    ]);
    $this->messenger()->addMessage(t('Revision from %revision-date of Location Entity %title has been deleted.', [
      '%revision-date' => \Drupal::service('date.formatter')->format($this->revision->getRevisionCreationTime()),
      '%title' => $this->revision->label(),
    ]));
    $form_state->setRedirect(
      'entity.location.canonical',
       ['location' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {location_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.location.version_history',
         ['location' => $this->revision->id()]
      );
    }
  }

}
