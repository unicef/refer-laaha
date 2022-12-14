Due to the utter ridiculous horrible brokenness of this site, these steps must be done BEFORE the code deploy, the database updates, and the config import:

Switch PHP environment to 8.1 at:
https://cloud.acquia.com/a/environments/325120-249931c2-c947-4980-b6e4-953c4206177c/config/runtime

**Also before** deploy to live:

```
ssh vss2.dev@vss2r9rhtjfnzn.ssh.devcloud.acquia-sites.com
cd /mnt/www/html/vss2.prod/
drush --uri=https://erefer.org thun vss
drush --uri=https://erefer.org pmu shield
```

Like literally the shield module is so messed up that with *no* configuration present it claims it cannot uninstall because shield.settings is incoming.


Make certain files are actually deployed:

```
ssh vss2.dev@vss2r9rhtjfnzn.ssh.devcloud.acquia-sites.com
cd /mnt/www/html/vss2.dev/docroot/
less composer.json # Or any file you can identify a recent change.
```
