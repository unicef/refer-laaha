<?php

// @codingStandardsIgnoreFile

/**
 * @file
 * Drupal site-specific configuration file.
 */

/**
 * Location of the site configuration files.
 *
 * The $settings['config_sync_directory'] specifies the location of file system
 * directory used for syncing configuration data. On install, the directory is
 * created. This is used for configuration imports.
 *
 * The default location for this directory is inside a randomly-named
 * directory in the public files path. The setting below allows you to set
 * its location.
 */
$settings['config_sync_directory'] = '../config/zw';
$settings['config_vcs_directory'] = '../config/zw';

/**
 * Acquia sets a hash salt per environment.
 */
$settings['hash_salt'] = '';

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.yml';

/**
 * The default list of directories that will be ignored by Drupal's file API.
 *
 * By default ignore node_modules and bower_components folders to avoid issues
 * with common frontend tools and recursive scanning of directories looking for
 * extensions.
 *
 * @see \Drupal\Core\File\FileSystemInterface::scanDirectory()
 * @see \Drupal\Core\Extension\ExtensionDiscovery::scanDirectory()
 */
$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];

/**
 * The default number of entities to update in a batch process.
 *
 * This is used by update and post-update functions that need to go through and
 * change all the entities on a site, so it is useful to increase this number
 * if your hosting configuration (i.e. RAM allocation, CPU speed) allows for a
 * larger number of entities to be processed in a single batch run.
 */
$settings['entity_update_batch_size'] = 50;

/**
 * Entity update backup.
 *
 * This is used to inform the entity storage handler that the backup tables as
 * well as the original entity type and field storage definitions should be
 * retained after a successful entity update process.
 */
$settings['entity_update_backup'] = TRUE;

/**
 * Node migration type.
 *
 * This is used to force the migration system to use the classic node migrations
 * instead of the default complete node migrations. The migration system will
 * use the classic node migration only if there are existing migrate_map tables
 * for the classic node migrations and they contain data. These tables may not
 * exist if you are developing custom migrations and do not want to use the
 * complete node migrations. Set this to TRUE to force the use of the classic
 * node migrations.
 */
$settings['migrate_node_migrate_type_classic'] = FALSE;

// On Acquia Cloud, this include file configures Drupal to use the correct
// database in each site environment (Dev, Stage, or Prod). To use this
// settings.php for development on your local workstation, set $db_url
// (Drupal 5 or 6) or $databases (Drupal 7 or 8) as described in comments above.
if (file_exists('/var/www/site-php/vss2/erpw-settings.inc')) {
  require('/var/www/site-php/vss2/erpw-settings.inc');
}

ini_set("session.cookie_samesite", "lax");

// Each of these are committed to the repository, and only one is loaded
// depending on the environment (including last one only
if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  switch ($_ENV['AH_SITE_ENVIRONMENT']) {
    case 'dev':
      include $app_root . '/' . $site_path . '/settings.dev.php';
      break;
    case 'test':
      include $app_root . '/' . $site_path . '/settings.test.php';
      break;
    case 'prod':
      include $app_root . '/' . $site_path . '/settings.prod.php';
      break;
  }
} else {
  // Shared DDEV (local development) configuration and includes DDEV.
  include $app_root . '/' . $site_path . '/settings.local-dev-shared.php';
}

$settings['config_sync_directory'] = '../config/zw';

/**
 * Load local development override configuration, if available.
 *
 * Create a settings.local.php file to override variables on secondary (staging,
 * development, etc.) installations of this site.
 *
 * Typical uses of settings.local.php include:
 * - Disabling caching.
 * - Disabling JavaScript/CSS compression.
 * - Rerouting outgoing emails.
 *
 * Keep this code block at the end of this file to take full effect.
 */
if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}
// Note, settings.local.php is customarily not committed, and we do not commit,
// nor necessarily use it, but it is available for individual local overrides.

// Automatically generated include for settings managed by ddev.
$ddev_settings = dirname(__FILE__) . '/settings.ddev.php';
if (getenv('IS_DDEV_PROJECT') == 'true' && is_readable($ddev_settings)) {
  require $ddev_settings;
}

// Repeat this at the bottom so it knows we really need business.
$settings['config_sync_directory'] = '../config/zw';
$settings['config_vcs_directory'] = '../config/zw';

$databases['default']['default']['database'] = 'zw';
