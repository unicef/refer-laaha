const { client } = require('nightwatch-api');
const { Given, Then, When, After, Before } = require('@cucumber/cucumber');

var json = require('../Data/components.json');
const staff = client.page.spStaffRolePage();

Then(/^Enter Staff Role credentials$/,() => {
  return staff.Enter_User_Credentials(process.env.staffEmail, process.env.staffPassword);
});

Then(/^Navigate to Manage Service on Dashboard$/, () => {
  return staff.Navigate_To_Manage_Services();
});

Then (/^Staff navigates to Dashboard$/, () => {
  return staff.Dashboard();
});