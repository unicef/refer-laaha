<?php
// Using this file, included from settings.php when no Acquia environment
// is present, allows us to commit shared local configuration.

// Override domain hostnames.  These need to be updated for each country added
// to production or else test/dev/local may send browsers to the live site.
$config['domain.record.bn_erefer_org']['hostname'] = 'bn.refer-laaha.ddev.site';
$config['domain.record.zm_erefer_org']['hostname'] = 'zw.refer-laaha.ddev.site';
$config['domain.record.sl_erefer_org']['hostname'] = 'sl.refer-laaha.ddev.site';
$config['domain.record.txb_erefer_org']['hostname'] = 'txb.refer-laaha.ddev.site';

// Include subdomains in trusted host patterns.
$settings['trusted_host_patterns'] = [
  '^refer-laaha\.ddev\.site$',
  '^.+\.refer-laaha\.ddev\.site$',
];

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

$config['acquia_connector.settings']['site_environment'] = 'dev';
$config['acquia_connector.settings']['env_detection_enabled'] = TRUE;


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
//$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';
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

// Memcached settings for Local
$settings['memcache']['servers'] = ['memcached:11211' => 'default'];
$settings['memcache']['bins'] = ['default' => 'default'];

// Check for PHP Memcached libraries.
$memcache_exists = class_exists('Memcache', FALSE);
$memcached_exists = class_exists('Memcached', FALSE);
$memcache_services_yml = DRUPAL_ROOT . '/modules/contrib/memcache/memcache.services.yml';
$memcache_module_is_present = file_exists($memcache_services_yml);
if ($memcache_module_is_present && ($memcache_exists || $memcached_exists)) {
  // Use Memcached extension if available.
  if ($memcached_exists) {
    $settings['memcache']['extension'] = 'Memcached';
  }
  if (class_exists(ClassLoader::class)) {
    $class_loader = new ClassLoader();
    $class_loader->addPsr4('Drupal\\memcache\\', DRUPAL_ROOT . '/modules/contrib/memcache/src');
    $class_loader->register();
    $settings['container_yamls'][] = $memcache_services_yml;

    // Acquia Default Settings for the memcache module
    // Default settings for the Memcache module.
    // Enable compression for PHP 7.
    $settings['memcache']['options'][Memcached::OPT_COMPRESSION] = TRUE;

    // Set key_prefix to avoid drush cr flushing all bins on multisite.
    $settings['memcache']['key_prefix'] = $conf['acquia_hosting_site_info']['db']['name'] . '_';

    // Decrease latency.
    $settings['memcache']['options'][Memcached::OPT_TCP_NODELAY] = TRUE;

    // Bootstrap cache.container with memcache rather than database.
    $settings['bootstrap_container_definition'] = [
      'parameters' => [],
      'services' => [
        'database' => [
          'class' => 'Drupal\Core\Database\Connection',
          'factory' => 'Drupal\Core\Database\Database::getConnection',
          'arguments' => ['default'],
        ],
        'settings' => [
          'class' => 'Drupal\Core\Site\Settings',
          'factory' => 'Drupal\Core\Site\Settings::getInstance',
        ],
        'memcache.settings' => [
          'class' => 'Drupal\memcache\MemcacheSettings',
          'arguments' => ['@settings'],
        ],
        'memcache.factory' => [
          'class' => 'Drupal\memcache\Driver\MemcacheDriverFactory',
          'arguments' => ['@memcache.settings'],
        ],
        'memcache.timestamp.invalidator.bin' => [
          'class' => 'Drupal\memcache\Invalidator\MemcacheTimestampInvalidator',
          'arguments' => [
            '@memcache.factory',
            'memcache_bin_timestamps',
            0.001,
          ],
        ],
        'memcache.backend.cache.container' => [
          'class' => 'Drupal\memcache\DrupalMemcacheInterface',
          'factory' => ['@memcache.factory', 'get'],
          'arguments' => ['container'],
        ],
        'cache_tags_provider.container' => [
          'class' => 'Drupal\Core\Cache\DatabaseCacheTagsChecksum',
          'arguments' => ['@database'],
        ],
        'cache.container' => [
          'class' => 'Drupal\memcache\MemcacheBackend',
          'arguments' => [
            'container',
            '@memcache.backend.cache.container',
            '@cache_tags_provider.container',
            '@memcache.timestamp.invalidator.bin',
            '@memcache.settings',
          ],
        ],
      ],
    ];

    // Content Hub 2.x requires the Depcalc module which needs to use the
    // database backend.
    $settings['cache']['bins']['depcalc'] = 'cache.backend.database';

    // Use memcache for bootstrap, discovery, config instead of fast chained
    // backend to properly invalidate caches on multiple webs.
    // See https://www.drupal.org/node/2754947
    $settings['cache']['default'] = 'cache.backend.memcache';
    $settings['cache']['bins']['render'] = 'cache.backend.memcache';
    $settings['cache']['bins']['discovery'] = 'cache.backend.memcache';
    $settings['cache']['bins']['bootstrap'] = 'cache.backend.memcache';
    $settings['cache']['bins']['config'] = 'cache.backend.memcache';
    $settings['cache']['bins']['data'] = 'cache.backend.memcache';
    $settings['cache']['bins']['default'] = 'cache.backend.memcache';
    $settings['cache']['bins']['discovery'] = 'cache.backend.memcache';
    $settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.memcache';
    $settings['cache']['bins']['entity'] = 'cache.backend.memcache';
    $settings['cache']['bins']['menu'] = 'cache.backend.memcache';
    $settings['cache']['bins']['page'] = 'cache.backend.memcache';
    $settings['cache']['bins']['render'] = 'cache.backend.memcache';
    $settings['cache']['bins']['toolbar'] = 'cache.backend.memcache';

  }
}

/**
 * Enable local development services.
 */
$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';
