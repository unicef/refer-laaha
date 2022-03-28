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
      },
      firstName: '#edit-first-name',
      lastName: '#edit-last-name',
      email: '#edit-email',
      phone: '#edit-phone',
      organisation: '#edit-organisation',
      position: '#edit-position',
      systemRole: '#edit-system-role',
      next: '#edit-next',
      selectSrijan: '#edit-organisation > option:nth-child(2)',
      selectAuthUser: '#edit-system-role > option:nth-child(2)',
      selectCountry: '#edit-location-options > option:nth-child(2)',
      password: '#edit-password',
      confirmPassword: '#edit-confirm-password',
      requestRegistration: '#edit-submit'
 
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
          .assert.visible('@firstName', 'First Name is displayed')
          .assert.visible('@lastName', 'Last name is displayed')
          .assert.visible('@email', 'Email is displayed')
          .assert.visible('@phone', 'Phone is displayed')
          .assert.visible('@organisation', 'Organisation is displayed')
          .assert.visible('@position', 'Position is displayed')
          .assert.visible('@systemRole', 'System Role is displayed')
          .assert.visible('@next', 'Next Button is displayed')
      },
      SignUpscreen: function(){
        return client.url("https://stage.erefer.org/sign-up").pause(1000);
      },
      Add_First_Name: function (firstname) {
        return this
        .click('@firstName')
        .setValue('@firstName', firstname)
        .pause(1000)
      },
      Add_Last_Name: function (lastname) {
        return this
        .click('@lastName')
        .setValue('@lastName', lastname)
        .pause(1000)
      },
      GENERATE_RANDOM_EMAIL: function () {
        var candidates = "ABCDEFGHIJKLMNOPQRSTUVWXY123456789";
        var randomemail = "",
        rand;
        for (var i = 0; i < 1; i++) {
        for (var j = 0; j < 8; j++) {
        rand = Math.floor(Math.random() * candidates.length);
        randomemail += candidates.charAt(rand);
        }
        randomemail += "@automation.com";
        }
        return randomemail;
        },
        Add_Email: function () {
          return this
          .click('@email')
          .setValue('@email', this.GENERATE_RANDOM_EMAIL())
          .pause(1000)
        },
        GENERATE_RANDOM_Phone_Number: function () {
          var phone = "1234567890";
          var randomphone = "",
          rand;
          for (var i = 0; i < 1; i++) {
          for (var j = 0; j < 10; j++) {
          rand = Math.floor(Math.random() * phone.length);
          randomphone += phone.charAt(rand);
          }
          }
          return randomphone;
          },

        Add_Phone: function () {
          return this
          .click('@phone')
          .setValue('@phone', this.GENERATE_RANDOM_Phone_Number())
          .pause(1000)
        },

        Select_Organisation: function () {
          return this
          .click('@organisation')
          .click('@selectSrijan')
        },

        Add_Position: function (position) {
          return this
          .click('@position')
          .setValue('@position', position)
        },

        Add_System_Role: function () {     
          client.execute('window.scrollTo(0,document.body.scrollHeight);');
          this.click('@systemRole')
          this.click('@selectAuthUser')
          this.pause(1000)
          return this
        },
        Click_Next: function () {
          client.execute('window.scrollTo(0,document.body.scrollHeight);');
          this.click('@next')
          this.pause(5000)
          return this
        },

        Select_Country: function () {
          return this
          .click('@selectCountry')
          .pause(7000)
        },

        Enter_Password: function (password) {
          return this
          .setValue('@password', password)
          .pause(2000)
        },

        Enter_Confirm_Password: function (confirmPassword) {
          return this
          .setValue('@confirmPassword', confirmPassword)
          .pause(2000)
        },

        Click_Request_Registration: function () {
          return this
          .click('@requestRegistration')
          .pause(2000)
        },
    }]
}