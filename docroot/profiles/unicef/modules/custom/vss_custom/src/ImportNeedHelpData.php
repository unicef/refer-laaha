<?php

namespace Drupal\vss_custom;

use Drupal\node\Entity\Node;

/**
 * Importer.
 */
class ImportNeedHelpData {

  /**
   * Addimport.
   */
  public static function addImportContentItem($datas, $language_datas, &$context) {
    if (!isset($context['master_results']['language_datas'])) {
      $context['master_results']['language_datas'] = $language_datas;
    }
    foreach ($datas as $item) {
      $message = 'Processing Data: ' . $item['Service Name'];
      ImportNeedHelpData::createData($item, $context);
      $context['message'] = $message;
      $context['results']['items'][] = $item;
    }
  }

  /**
   * Callback.
   */
  public static function addImportContentItemCallback($success, $results, $operations) {
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results['items']), 'One item processed.', '@count items processed.'
      );
    }
    else {
      $message = t('Finished with an error.');
    }
    \Drupal::messenger()->addMessage($message);
  }

  /**
   * Insert data.
   */
  public static function createData($item, &$context) {
    $item = array_map('trim', $item);
    $language = $context['master_results']['language_datas'];
    $service_base_name = $item['Service base name'];
    $service_provider_base_name = $item['Organisation Base name'];
    $hierarchy_4_base_name = $item['Base hierarchy level 4'];
    $service_name = $item['Service Name'];
    $service_provider_name = $item['Organisation Name'];
    $email = $item['Email ID'];
    $tel_no = $item['Telephone Number'];
    $hierarchy_1 = $item['Hierarchy level 1'];
    $hierarchy_2 = $item['Hierarchy level 2'];
    $hierarchy_3 = $item['Hierarchy level 3'];
    $hierarchy_4 = $item['Hierarchy level 4'];
    $priority = $item['Priority'];
    $fb = $item['Facebook'];
    $twitter = $item['Twitter'];
    $linkedin = $item['LinkedIn'];
    $city = $item['City'];
    $state = $item['State'];
    $country = $item['Country'];
    $description = $item['Description'];

    if (!empty($service_base_name) && !empty($service_provider_base_name) && !empty($hierarchy_4_base_name)) {
      $node = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
        'title' => $service_base_name,
        'field_service_provider_name' => $service_provider_base_name,
        'field_hierarchy_level_4' => $hierarchy_4_base_name,
      ]);
    }
    if ($node) {
      $nid = array_key_first($node);
      $nd = Node::load($nid);
      if (!$nd->hasTranslation($language)) {
        $nd = $nd->addTranslation($language);
      }
      else {
        $nd->set('langcode', $language);
      }
      $nd->set('title', $service_name);
      $nd->set('field_city', $city);
      $nd->set('field_service_provider_name', $service_provider_name);
      $nd->set('field_country', $country);
      if ($email) {
        $nd->set('field_email_id', $email);
      }
      if ($fb) {
        $nd->set('field_facebook', $fb);
      }
      $nd->set('field_hierarchy_level_1', $hierarchy_1);
      $nd->set('field_hierarchy_level_2', $hierarchy_2);
      $nd->set('field_hierarchy_level_3', $hierarchy_3);
      $nd->set('field_hierarchy_level_4', $hierarchy_4);
      if ($linkedin) {
        $nd->set('field_linkedin', $linkedin);
      }
      $nd->set('field_priority', $priority);
      if ($state) {
        $nd->set('field_state', $state);
      }
      $nd->set('field_telephone_number', $tel_no);
      if ($twitter) {
        $nd->set('field_twitter', $twitter);
      }
      if ($description) {
        $nd->set('field_description', $description);
      }
      $nd->save();
    }
    else {
      // Create node object with attached file.
      $node = Node::create([
        'type'        => 'service_provider',
        'langcode' => $language,
        'title'       => $service_name,
        'field_city' => $city ? $city : '',
        'field_service_provider_name' => $service_provider_name ? $service_provider_name : '',
        'field_country' => $country ? $country : '',
        'field_email_id' => $email ? $email : '',
        'field_facebook' => $fb ? $fb : '',
        'field_hierarchy_level_1' => $hierarchy_1 ? $hierarchy_1 : '',
        'field_hierarchy_level_2' => $hierarchy_2 ? $hierarchy_2 : '' ,
        'field_hierarchy_level_3' => $hierarchy_3 ? $hierarchy_3 : '',
        'field_hierarchy_level_4' => $hierarchy_4 ? $hierarchy_4 : '',
        'field_linkedin' => $linkedin ? $linkedin : '',
        'field_priority' => $priority,
        'field_state' => $state ? $state : '',
        'field_telephone_number' => $tel_no ? $tel_no : '',
        'field_twitter' => $twitter ? $twitter : '',
        'field_description' => $description ? $description : '',
        'field_domain_access' => \Drupal::service('domain.negotiator')->getActiveDomain()->id(),
        'uid' => \Drupal::currentUser()->id(),
      ]);
      $node->save();
    }
    \Drupal::messenger()->addMessage(t('Data succesfully uploaded'));
  }

}
