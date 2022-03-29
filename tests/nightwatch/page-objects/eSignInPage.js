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
      openUrl_loginCMS: function(){
      return client.url("https://erpwadmin:Unicef%2312345@stage.erefer.org/").pause(1000);
      },

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
            
      logout() {
        return this.assert
          .visible('@accountIcon', 'Admin menu is displayed')
          .click('@accountIcon')
          .click('@logoutBtn');
      }
    }]
}