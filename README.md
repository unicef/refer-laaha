# Erefer.org

Refer.laaha.org.  Formerly erefer.org

## Urls

__Local:__ https://refer-laaha.ddev.site
__Dev:__ https://refer-laaha.org
__Test/Stage:__ https://test-refer.laaha.org
__Live/Prod:__ https://refer.laaha.org


## Local setup

Prerequisites: Git and [DDEV](https://ddev.readthedocs.io/en/stable/#installation)

```bash
git clone git@github.com:unicef/refer-laaha.git
cd refer-laaha
ddev start
ddev auth ssh
ddev composer update
```

## Get live database

If needed, `mkdir backups` (an ignored directory) first.

The database can be retrieved from nightly backups, for example:

```bash
ddev export-db > backups/local-paranoia-backup-2022-09-07.sql.gz
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

- `ddev drush cpull @dev @self`

## Deploy to Acquia dev environment

Push a tag to the main branch, and GitHub will use `blt artifact:deploy`.  Acquia's approach is very slow; when that eventually finishes (visit https://github.com/unicef/laaha-org/actions/ to watch progress or https://cloud.acquia.com/a/applications/1453586a-0bf5-4dce-a39c-c53e788c86f7 to see the result in the Task Log) these additional steps are needed:

```bash
ddev drush @dev cr
ddev drush @dev -y updb
ddev drush @dev -y cim
```

From the hosted dev environment, further code deployments to test and prod are done through the cloud.acquia.com dashboard.  After each code deployment through the UI, the database update and the configuration import are still needed.


For test:

```bash
ddev drush @test cr
ddev drush @test -y updb
ddev drush @test -y cim
```

And live:

```bash
ddev drush @live cr
ddev drush @live -y updb
ddev drush @live -y cim
```
## Whenever a new country is added to the live site

Add to and follow the pattern for `docroot/sites/default/settings.test.php`:

```php
// Override domain hostnames.  These need to be updated for each country added
// to production or else test/dev/local may send browsers to the live site.

$config['domain.record.bn_erefer_org']['hostname'] = 'bn-stage.erefer.org';
```

Edit the same section at `docroot/sites/default/settings.local-dev-shared.php`.

And you must [add the domain on Acquia to the Stage domains configuration](https://cloud.acquia.com/a/environments/325121-249931c2-c947-4980-b6e4-953c4206177c/domain-management/domains).

(The dash in "ec-test" allows us to have a valid security sertificate while testing,
but does require these subdomains be added manually to Acquia stage environment.)

1) Add a new domain record at /admin/config/domain.
2) Override the domain hostnames in settings.local-dev-shared.php , settings.test.php.
3) Add the new domain hostname in .ddev/config.yml under additional_hostnames.
4) Configure settings such as language detection and selection,languages and so on for the new domain from operations on /admin/config/domain.
5) Re-arrange the detection methods under Content language detection from /admin/config/domain/language-detect-select/<new domain>
6) From /admin/content , enable the content of type homepage and basic page for the the new domain , and you can perform the same for the other required contents.
7) From /admin/structure/block/manage/navigationheadermenu , configure the navigation menu block to appear on the new domain.
8) Add new content under /admin/structure/taxonomy/manage/categories/overview for the hero categories to be displayed.


## 522 - Domain and location issue fixes used MySQL procedure.

```
DELIMITER //

DROP FUNCTION IF EXISTS GetTopLevelParent //

CREATE FUNCTION GetTopLevelParent(eid INT) RETURNS INT

BEGIN

  DECLARE parent INT;

  DECLARE entityid INT;

  SET parent = eid;

  

  WHILE parent != 0 DO

    SET entityid = parent;

    SELECT ttp.parent_target_id INTO parent FROM taxonomy_term__parent AS ttp JOIN taxonomy_term_data AS ttd ON ttd.tid = ttp.entity_id WHERE ttp.entity_id = entityid && ttd.vid = 'country';

  END 

WHILE;

  

  RETURN entityid;

END //

DELIMITER ;
```
The above function will returen the top most location id by given location id, directly executed this on Acqia database.