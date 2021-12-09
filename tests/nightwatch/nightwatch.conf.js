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
  
    test_settings: {
      default: {
        request_timeout_options: {
          timeout: 100000
        },
        desiredCapabilities: {
          browserName: 'chrome',
          chromeOptions: {
            args: [
              '--headless',
              '--no-sandbox',
              '--disable-gpu',
              '--window-size=1280,1696',
              '--disable-dev-shm-usage'
            ]
          }
        }
      }
    }
  };
  