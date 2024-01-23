<?php

/**
 * @file
 * Contains declaration of the custom view field plugin: FP Role Name change.
 */

/**
 * Implements hook_views_data_alter().
 */
function erpw_custom_views_data_alter(array &$data) {
  $data['users']['role_name_change'] = [
    'title' => t('FP Role Name Change'),
    'group' => t('User'),
    'field' => [
      'title' => t('FP Role Name Change'),
      'help' => t('Changes the name of Service Provider Focal Point role for BN domain.'),
      'id' => 'role_name_change',
    ],
  ];
}
