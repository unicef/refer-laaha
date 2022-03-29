const { client } = require('nightwatch-api');
const { Given, Then, When, And } = require('@cucumber/cucumber');

const signIn = client.page.eSignInPage();

Given(/^launch browser, open eRPW url$/, () => {
  return signIn.openUrl_loginCMS();  
});

Then(/^enter eRPW credentials$/, () => {
  return signIn.Enter_User_Credentials(process.env.eAdminUsername, process.env.eAdminPassword);

});

