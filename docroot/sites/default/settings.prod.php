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
$config['environment_indicator.indicator']['name'] = 'eRPW Production';

// Include subdomains in trusted host patterns.
$settings['trusted_host_patterns'] = [
  '^erefer\.org$',
  '^*\.erefer\.org$',
  '^refer\.laaha\.org$',
  '^*\.refer\.laaha\.org$',
];
