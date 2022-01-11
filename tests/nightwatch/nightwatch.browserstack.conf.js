require('dotenv').config();
const baseConfig = require('./nightwatch.conf.js');

const nightwatch_config = {
    ...baseConfig,
    webdriver: {
        'start_process': false,
        'host': 'hub-cloud.browserstack.com',
        'port': 80
    },
};

nightwatch_config.test_settings.default = {
    desiredCapabilities: {
        os: 'Windows',
        os_version: '10',
        browserName: 'chrome',
        ['browserstack.local']: false
    }
};

nightwatch_config.test_settings.default.desiredCapabilities['browserstack.user'] = process.env.BROWSERSTACK_USERNAME;
nightwatch_config.test_settings.default.desiredCapabilities['browserstack.key'] = process.env.BROWSERSTACK_ACCESS_KEY;
//nightwatch_config.test_settings.default.desiredCapabilities.chromeOptions.args = [];

nightwatch_config.test_settings.firefox = {
    desiredCapabilities: {
        os: 'Windows',
        os_version: '10',
        browserName: 'Firefox',
        ['browserstack.local']: false
    }
};

// Code to copy seleniumhost/port into test settings
for (var i in nightwatch_config.test_settings) {
    var test_setting = nightwatch_config.test_settings[i];
    test_setting['selenium_host'] = nightwatch_config.webdriver.host;
    test_setting['selenium_port'] = nightwatch_config.webdriver.port;
}

module.exports = nightwatch_config;
