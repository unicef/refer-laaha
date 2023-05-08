<?php

namespace Drupal\erpw_webform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;

/**
 * Generate key value pair of elements in the webform submission view page.
 */
class ServiceSubmissionsView extends ControllerBase {
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new ServiceWebforms object.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
    $container->get('entity_type.manager'),
    );
  }

  /**
   * Generate key value pair of elements in the webform submission.
   */
  public function content($id) {
    $webformSubmission = $this->entityTypeManager->getStorage('webform_submission')->load($id);
    $output = [];
    $fields = $webformSubmission->getData();
    $location = "";
    $country = "";
    $level_1 = "";
    $level_2 = "";
    $level_3 = "";
    $level_4 = "";
    foreach ($fields as $key => $content) {
      $element = $this->entityTypeManager->getStorage('webform')->load($webformSubmission->getWebform()->id())->getElement($key);
      if ($key != 'erpw_workflow') {
        if ($key == 'location') {
          if ($content["location_options"] != "") {
            $country = $this->entityTypeManager()->getStorage('location')->load($content["location_options"])->getName();
            $location = $country . '.';
          }
          if ($content["level_1"] != "") {
            $level_1 = $this->entityTypeManager()->getStorage('taxonomy_term')->load($content["level_1"])->getName();
            $location = $level_1 . ', ' . $location;
          }
          if ($content["level_2"] != "") {
            $level_2 = $this->entityTypeManager()->getStorage('taxonomy_term')->load($content["level_2"])->getName();
            $location = $level_2 . ', ' . $location;
          }
          if ($content["level_3"] != "") {
            $level_3 = $this->entityTypeManager()->getStorage('taxonomy_term')->load($content["level_3"])->getName();
            $location = $level_3 . ', ' . $location;
          }
          if ($content["level_4"] != "") {
            $level_4 = $this->entityTypeManager()->getStorage('taxonomy_term')->load($content["level_4"])->getName();
            $location = $level_4 . ', ' . $location;
          }
          $output[] = ['Location' => $location];
        }
        elseif ($element['#type'] == "checkbox" && $element['#webform_key'] == $key) {
          if ($content != NULL) {
            if ($content == 1) {
              $output[] = [$element['#title'] => 'Yes'];
            }
            else {
              $output[] = [$element['#title'] => 'No'];
            }
          }
        }
        elseif ($element['#type'] == "checkboxes" && $element['#webform_key'] == $key) {
          $values = [];
          if (gettype($content) == 'array' & $content != NULL) {
            foreach ($content as $key) {
              if ($element['#options'][$key] != NULL) {
                array_push($values, $element['#options'][$key]);
              }
            }
            $output[] = [$element['#title'] => $values];
          }
          else {
            if ($content != NULL) {
              $output[] = [$element['#title'] => $element['#options'][$content]];
            }
          }
        }
        elseif ($element['#type'] == "radios" && $element['#webform_key'] == $key) {
          if ($element['#options'][$content] != NULL) {
            $output[] = [$element['#title'] => $element['#options'][$content]];
          }

        }
        elseif ($element['#type'] == "select" && $element['#webform_key'] == $key) {
          $values = [];
          if (gettype($content) == 'array' & $content != NULL) {
            foreach ($content as $key) {
              if ($element['#options'][$key] != NULL) {
                array_push($values, $element['#options'][$key]);
              }
            }
            $output[] = [$element['#title'] => $values];
          }
          else {
            if ($content != NULL) {
              $output[] = [$element['#title'] => $element['#options'][$content]];
            }
          }
        }
        else {
          $output[] = [$element['#title'] => $content];
        }
      }
    }
    $edit_url = Url::fromRoute('entity.webform_submission.edit_form', [
      'webform' => $webformSubmission->getWebform()->id(),
      'webform_submission' => $id,
    ])->toString();
    $markup = '
    <div class="service-provider-details">
      <div class="service-detail-heading">
        <h3>Service Details</h3>
        <div class="edit-delete-links">
          <span class="edit-link">
            <a href=' . $edit_url . '>Edit</a>
          </span>
        </div>
      </div>
    </div>';
    foreach ($output as $item) {
      foreach ($item as $key => $value) {
        $markup .= '<div class="pair-container"><span class="label">' . Markup::create($key) . ':</span>';
        if (is_array($value)) {
          $markup .= '<span class="value">' . Markup::create(implode(", ", $value)) . '</span>';
        }
        else {
          $markup .= '<span  class="value">' . Markup::create($value) . '</span>';
        }
        $markup .= '</div>';
      }
    }
    return [
      '#type' => 'markup',
      '#markup' => $markup,
    ];
  }

}
