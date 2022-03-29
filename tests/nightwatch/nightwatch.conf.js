require('dotenv').config();

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
      credentials:{
        username: "vssadmin",
        password: "Unicef#12345",
      },
      request_timeout_options: {
        timeout: 100000
      },
      desiredCapabilities: {
          // browserName: 'chrome',
          // javascriptEnabled : true,
          // acceptSslCerts : true,
          // // chromeOptions: {mobileEmulation: {
          // // deviceMetrics: {width: 375, height: 667, pixelRatio: 3},
          // chromeOptions: {
          // // mobileEmulation: { "deviceName" : "Galaxy S5"},
          // // userAgent:'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Mobile Safari/537.36 profi-autotest',
          //  args: [

          //   '--no-sandbox',
          //    "--ignore-certificate-errors",
          //    '--incognito',
          //   '--disable-gpu',
          //   '--window-size=1280,1696',
          //   // '--window-size=width: 360, height: 640, pixelRatio: 3',
          // ],
          //    mobileEmulation: {
          //     deviceName: "Iphone SE"
          
          //   }
          // }
        


        browserName: 'chrome',
        javascriptEnabled: true,
        acceptSslCerts: true,
        nativeEvents: true,
        elementScrollBehavior: 1,
        acceptSslCerts: true,
        //sslCertTrusted = true,
        chromeOptions: {
          //mobileEmulation: { "deviceName" : "Galaxy S5"},
          args: [
            //'--headless',
             //'--no-sandbox',
             //"--ignore-certificate-errors",
             //'--incognito',
            //'--disable-gpu',
            //'--window-size=1280,1696',
            //'--window-size=412 x 892',
            //'--window-size=width: 360, height: 640, pixelRatio: 3',
        ],
      //       mobileEmulation: {
      //         deviceName: "Iphone SE"
      //  //],

      //   }
      },
      // env1: {
      //   desiredCapabilities: {
      //     "browser": "safari",
      //     "browser_version": "latest",
      //     "os": "OS X",
      //     "os_version": "Big Sur"
        }
      }
    }
  //   }
  // }
};