module.exports = {
    "Step one: Navigate to google.com and verify URL and title" : function(browser) {
     browser
     .url("http://www.google.com")
     .waitForElementPresent('body', 1000)
     .assert.urlContains("google")
     .assert.title("Google")
    },
    
    "Step two: Search 'Nightwatch' and verify result" : function(browser) {
      browser
     .setValue('input[type=text]', ['nightwatch', browser.Keys.ENTER])
     .pause(1000)
     .assert.containsText('#main', 'nightwatch')
     .end();
    }
}