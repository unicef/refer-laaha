<?php

/**
 * @file
 * Settings for dev environment.
 */

// Override domain hostnames.  These need to be updated for each country added
// to production or else test/dev/local may send browsers to the live site.

$config['domain.record.bn_erefer_org']['hostname'] = 'bn-dev.erefer.org';
$config['domain.record.zm_erefer_org']['hostname'] = 'zw-dev.erefer.org';

// Local environment indicator
$config['environment_indicator.indicator']['bg_color'] = '#6aa84f';
$config['environment_indicator.indicator']['fg_color'] = '#FFFFFF';
$config['environment_indicator.indicator']['name'] = 'VSS Development';
