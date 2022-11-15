# Laaha.org

The Virtual Safe Space.  Formerly virtualsafespace.net

## Urls

__Local:__ https://laaha-org.ddev.site  
__Dev:__ https://dev.laaha.org  
__Test/Stage:__ https://test.laaha.org  
__Live/Prod:__ https://laaha.org


## Whenever a new country is added to the live site

Add to and follow the pattern for `docroot/sites/default/settings.test.php`:

```php
// Override domain hostnames.  These need to be updated for each country added
// to production or else test/dev/local may send browsers to the live site.

$config['domain.record.ec_virtualsafespace_net']['hostname'] = 'ec.test.laaha.org';
$config['domain.record.iq_virtualsafespace_net']['hostname'] = 'iq.test.laaha.org';
```

And the same section at `docroot/sites/default/settings.local-dev-shared.php`.

## Local setup

Prerequisites: Git and [DDEV](https://ddev.readthedocs.io/en/stable/#installation)

```bash
git clone git@github.com:unicef/laaha-org.git
cd laaha-org
ddev start
ddev auth ssh
ddev composer update
```

## Get live database

If needed, `mkdir backups` (an ignored directory) first.

The database can be retrieved from nightly backups, for example:

```bash
ddev drush sql-dump > backups/local-paranoia-backup-2022-09-07.sql
scp erpw.dev@erpwcfg84izyh6.ssh.devcloud.acquia-sites.com:/home/erpw/dev/backups/on-demand/backup-2022-10-17-17-05-erpw-146875152.sql.gz backups/
ddev import-db --src backups/backup-2022-10-17-17-05-erpw-146875152.sql.gz
ddev drush cr
```

## Configure local Solr

Configuration overrides for the acquia_search_server are provided in settings.local-dev-shared.php, and included in a development environment by default. A solr instance is configured by ddev already, and is used by these overrides. The collection's configuration is not auto-configured however, so these commands will perform this action (which can also be done from https://laaha-org.ddev.site/en/admin/config/search/search-api/server/acquia_search_server if the search_api_solr_admin module is enabled).

```bash
ddev drush pm:enable search_api_solr_admin
ddev drush cr
ddev drush solr-upload-conf acquia_server_test_support --numShards=1
ddev drush pm:uninstall search_api_solr_admin   # the admin module is not needed for normal use
ddev drush sapi-i acquia_live_search_index      # rebuild the index (or just use UI)
```

## Configuration Management

Make local changes and export with:

- `ddev drush cex -y`

Pull down changes made directly on an Acquia environment with, for dev:

- `ddev drush cpull @laaha-org.dev @self`

## Deploy to Acquia dev environment

```bash
ddev ssh
blt artifact:deploy --commit-msg "Deploy to build" --branch "build"
```

When that eventually finishes (Acquia's approach is very slow) these additional steps are needed:

```bash
ddev drush @laaha-org.dev cr
ddev drush @laaha-org.dev -y updb
ddev drush @laaha-org.dev -y cim
```



(From the hosted dev environment, further code deployments to test and prod are done through the cloud.acquia.com dashboard.)
