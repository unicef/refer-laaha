<?php
// Using this file, included from settings.php when no Acquia environment
// is present, allows us to commit shared local configuration.


// Local environment indicator
$config['environment_indicator.indicator']['name'] = 'Local Development';
$config['environment_indicator.indicator']['bg_color'] = '#6aa84f';
$config['environment_indicator.indicator']['fg_color'] = '#FFFFFF';

// Local Solr settings
$config['search_api.server.acquia_server_test_support'] = [
  'backend_config' => [
    'connector' => 'solr_cloud_basic_auth',
    'connector_config' => [
      'scheme' => 'http',
      'host' => 'ddev-laaha-org-solr',
      'path' => '/',
      'core' => 'drupal',
      'port' => '8983',
      'username' => 'solr',
      'password' => 'SolrRocks'
    ],
  ],
];

$settings['config_sync_directory'] = '../config/vcs';
/**
 * Assertions.
 *
 * The Drupal project primarily uses runtime assertions to enforce the
 * expectations of the API by failing when incorrect calls are made by code
 * under development.
 *
 * @see http://php.net/assert
 * @see https://www.drupal.org/node/2492225
 *
 * If you are using PHP 7.0 it is strongly recommended that you set
 * zend.assertions=1 in the PHP.ini file (It cannot be changed from .htaccess
 * or runtime) on development machines and to 0 in production.
 *
 * @see https://wiki.php.net/rfc/expectations
 */
assert_options(ASSERT_ACTIVE, TRUE);
\Drupal\Component\Assertion\Handle::register();

/**
 * Enable local development services.
 */
$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';
# $settings['container_yamls'][] = DRUPAL_ROOT . '/sites/default/ddev.services.yml';

/**
 * Show all error messages, with backtrace information.
 *
 * In case the error level could not be fetched from the database, as for

/**
 * Show all error messages, with backtrace information.
 *
 * In case the error level could not be fetched from the database, as for
 * example the database connection failed, we rely only on this value.
 */
$config['system.logging']['error_level'] = 'verbose';

/**
 * Disable CSS and JS aggregation.
 */
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;

/**
 * Enable access to rebuild.php.
 *
 * This setting can be enabled to allow Drupal's php and database cached
 * storage to be cleared via the rebuild.php page. Access to this page can also
 * be gained by generating a query string from rebuild_token_calculator.sh and
 * using these parameters in a request to rebuild.php.
 */
$settings['rebuild_access'] = TRUE;

/**
 * Skip file system permissions hardening.
 *
 * The system module will periodically check the permissions of your site's
 * site directory to ensure that it is not writable by the website user. For
 * sites that are managed with a version control system, this can cause problems
 * when files in that directory such as settings.php are updated, because the
 * user pulling in the changes won't have permissions to modify files in the
 * directory.
 */
$settings['skip_permissions_hardening'] = TRUE;
