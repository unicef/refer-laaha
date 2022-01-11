module.exports = {
    src_folders: ['./'],
    page_objects_path: ['page-objects'],
    skip_testcases_on_fail: false,
  
    test_workers: {
      enabled: true,
      workers: 'auto'
    },
  
    screenshots: {
      enabled: true,
      path: 'reports/screenshots',
      on_failure: true,
      on_error: true
    },
  
    webdriver: {
      start_process: true,
      server_path: 'node_modules/.bin/chromedriver',
      port: 9515
    },

    common_capabilities: {
      'build': 'browserstack-build-1',
      'browserstack.user': 'testingaccount_LpAgVkt',
      'browserstack.key': 'XKr5kWBy4qkG1uzjXqZp',
      'browserstack.debug': true
    },
  
    test_settings: {
      default: {
        credentials:{
          username: "vssadmin",
          password: "Unicef#12345",
        },
        request_timeout_options: {
          timeout: 100000
        },
        desiredCapabilities: {
          browserName: 'chrome',
          javascriptEnabled: true,
    	    acceptSslCerts: true,
          nativeEvents: true,
          elementScrollBehavior: 1,
          //acceptSslCerts: true,
          // sslCertTrusted = true,
          chromeOptions: {
            args: [
              //'--headless',
               '--no-sandbox',
               "--ignore-certificate-errors",
               '--incognito',
              '--disable-gpu',
              '--window-size=1280,1696',
            ]
          }
        }
      }
    }
  };