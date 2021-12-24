const { client } = require('nightwatch-api');
const { Given, Then, When, And } = require('@cucumber/cucumber');

const login = client.page.loginPage();

Given(/^launch browser, open vss url$/, () => {
  return login.openUrl_loginCMS();  
});

Then(/^enter the credentials$/, () => {
  return login.Enter_User_Credentials(process.env.USERNAME, process.env.PASSWORD);
  //return login.login(process.env.EMAIL, process.env.PASSWORD);

});

