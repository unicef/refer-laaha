<img alt="Drupal Logo" src="https://www.drupal.org/files/Wordmark_blue_RGB.png" height="60px">

Welcome to Unicef VSS - eRPW Multisite repository

## VSS Urls:
__Local:__ https://vss.lndo.site
__Dev:__ https://dev.virtualsafespace.net
__Stage:__ https://stage.virtualsafespace.net
__Prod:__ https://virtualsafespace.net

## eRPW Urls:
__Local:__ https://erpw.lndo.site
__Dev:__ https://dev.erefer.org
__Stage:__ https://stage.erefer.org
__Prod:__ https://erefer.org

## Site Setup on Local.
- Clone the unicef/eRPW repo on your local machine
- Download latest stable release for lando
- Run `lando start`
- Run `lando composer install`
- For VSS,
   - Copy the `example.settings.local.php` from docroot/sites/default location and rename it as settings.local.php
   - Add the below lines in your settings.local.php
    ```
       $databases['default'] = array (
        'default' => array (
          'driver' => 'mysql',
          'database' => 'acquia',
          'username' => 'acquia',
          'password' => 'acquia',
          'prefix' => '',
          'port' => 3306,
        )
      );
      // The only thing to add from the out-of-the-box Lando db is the special host for each subsite
      $databases['default']['default']['host'] = 'database';
      $settings["config_sync_directory"] = "../config/vss";
      $config['config_split.config_split.local']['status'] = TRUE;
      $config['environment_indicator.indicator']['bg_color'] = '#6aa84f';
      $config['environment_indicator.indicator']['fg_color'] = '#FFFFFF';
      $config['environment_indicator.indicator']['name'] = 'Local Development';
      $config['shield.settings']['shield_enable'] = 0;
      $config['sheild.settings']['credentials.shield.user'] = '';
      $config['sheild.settings']['credentials.shield.pass'] = '';
    ```
- For eRPW,
   - Copy the `example.settings.local.php` from docroot/sites/default location and rename it as settings.local.php
   - Add the below lines in your settings.local.php
    ```
     $databases['default'] = array (
      'default' => array (
        'driver' => 'mysql',
        'database' => 'erpw',
        'username' => 'erpw',
        'password' => 'erpw',
        'prefix' => '',
        'port' => 3306,
      )
    );
    // The only thing to add from the out-of-the-box Lando db is the special host for each subsite
    $databases['default']['default']['host'] = 'database';
    $settings["config_sync_directory"] = "../config/erpw";
    $config['config_split.config_split.local']['status'] = TRUE;
    $config['environment_indicator.indicator']['bg_color'] = '#6aa84f';
    $config['environment_indicator.indicator']['fg_color'] = '#FFFFFF';
    $config['environment_indicator.indicator']['name'] = 'eRPW Local Development';
    $config['shield.settings']['shield_enable'] = 0;
    $config['sheild.settings']['credentials.shield.user'] = '';
    $config['sheild.settings']['credentials.shield.pass'] = '';
    ```
    - Run `lando mysql`
    - Inside mysql, run `create database erpw;`
    - Run `CREATE USER 'erpw'@'%' IDENTIFIED BY 'erpw';`
    - Run `GRANT ALL PRIVILEGES ON erpw.* TO 'erpw'@'%';`
- You may choose to run drush site install via following command to import existing config
  - `lando drush <local-site-alias> si -y --existing-config --sites-subdir=<site-folder-name>` 

# Local environment Drush site alias
- VSS -> @vss2.vsslocal
- eRPW -> @vss2.erpwlocal

## Configuration Management
Drupal stores all your site's configuration data in a consistent manner. All of your site configuration from the list of enabled modules, through to content types, taxonomy vocabularies, fields, views, and so on, is stored with this system. The system is designed to make it easy to make changes, export site configuration to files, and import those changes back into the site. This allows your site's configuration to be stored as part of your site's codebase, and thus integrated in your version control system and your deployment process.
- `lando drush <site-alias> cex -y`
