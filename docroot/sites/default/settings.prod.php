<?php

/**
 * @file
 * Settings for prod environment.
 */

// Config split for prod.
$config['config_split.config_split.prod']['status'] = TRUE;
$config['config_split.config_split.local']['status'] = FALSE;
$config['environment_indicator.indicator']['bg_color'] = '#f44336';
$config['environment_indicator.indicator']['fg_color'] = '#FFFFFF';
$config['environment_indicator.indicator']['name'] = 'VSS Production';

// Disabling shield module on prod.
$config['shield.settings']['shield_enable'] = 0;
$config['sheild.settings']['credentials.shield.user'] = '';
$config['sheild.settings']['credentials.shield.pass'] = '';
