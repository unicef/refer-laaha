const { client } = require("nightwatch-api");

module.exports = {
  
  elements: {

        username: '#edit-name',
        password: '#edit-pass',
        loginBtn: '#edit-submit',
        accountIcon: '#toolbar-item-user',
        logoutBtn: '//li[@class="logout"]'
      
    },
    commands: [
      {
        Enter_User_Credentials: function(username, password){
          return this
          .click('@username')
          .setValue('@username', username)
          .click('@password')
          .setValue('@password', password)
          .click('@loginBtn')
          // .assert.urlContains("user/591")
          .pause(2000);
         },

         Dashboard: function(){
            return client.url("https://stage.erefer.org/en/dashboard").pause(1000);

         },

         Navigate_To_RPW: function(){
            return client.url("https://stage.erefer.org/en/referral-pathway-listing").pause(1000);
         },

         Navigate_To_App_Users: function(){
            return client.url("https://stage.erefer.org/en/users-list").pause(1000);
         },

         Navigate_To_Manage_Locs: function(){
            return client.url("https://stage.erefer.org/en/users-list").pause(1000);
         },

         Navigate_To_Manage_Service_Types: function(){
            return client.url("https://stage.erefer.org/en/manage-service-types").pause(1000);
         },

         Navigate_To_Manage_Organisations: function(){
            return client.url("https://stage.erefer.org/en/organisations").pause(1000);
         },

         Navigate_To_Manage_Services: function(){
            return client.url("https://stage.erefer.org/en/manage-service").pause(1000);
         }















        }]
    }