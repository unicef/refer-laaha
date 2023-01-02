<?php

/**
 * @file
 * Settings for stage environment.
 */

// Override domain hostnames.  These need to be updated for each country added
// to production or else test/dev/local may send browsers to the live site.

$config['domain.record.bn_erefer_org']['hostname'] = 'bn-stage.erefer.org'; // $config['domain.record.bn_erefer_org']['hostname'] = 'bn.test.refer.laaha.org';
$config['domain.record.zm_erefer_org']['hostname'] = 'zw-stage.erefer.org'; // $config['domain.record.zm_erefer_org']['hostname'] = 'zw.test.refer.laaha.org';

// Local environment indicator
$config['environment_indicator.indicator']['bg_color'] = '#2986cc';
$config['environment_indicator.indicator']['fg_color'] = '#FFFFFF';
$config['environment_indicator.indicator']['name'] = 'eRPW Stage';

// Include subdomains in trusted host patterns.
$settings['trusted_host_patterns'] = [
  '^stage\.erefer\.org$',
  '^*\.stage\.erefer\.org$',
  '^*\.erefer\.org$',
  '^zw-stage\.erefer\.org$',
  '^bn-stage\.erefer\.org$',
  '^test\.refer\.laaha\.org$',
  '^*\.test\.refer\.laaha\.org$',
];
