const { client } = require("nightwatch-api");

module.exports = {
  
  elements: {
      SignUp: {
          selector: '//*[@class="page-title"]',
          locateStrategy: 'xpath'
      },
      PersonalDetails: {
          selector: '//*[@class="step"]',
          locateStrategy: 'xpath'
      }
 
},
  commands: [
    {
      SignUp: function(){
      return client.url("https://erpwadmin:Unicef%2312345@dev.erefer.org/sign-up").pause(1000);
      },
      Verify_SignUp_Elements: function () {
          return this
          .assert.visible('@SignUp', 'Title is Sign Up')
          .assert.visible('@PersonalDetails', 'Personal Detail appears')
      }


    }]
}