Due to the utter ridiculous horrible brokenness of this site, these steps must be done BEFORE the code deploy, the database updates, and the config import:


```
drush @live thun vss
drush @live pmu shield
```


Like literally the shield module is so messed up that with *no* configuration present it claims it cannot uninstall because shield.settings is incoming.
