const { client } = require('nightwatch-api');
const { Given, Then, When, After, Before } = require('@cucumber/cucumber');

var json = require('../Data/components.json');
const CA = client.page.spCountryAdminPage();

Then(/^Enter Country Admin Role credentials$/,() => {
  return CA.Enter_User_Credentials(process.env.CAEmail, process.env.CAPassword);
});

Then(/^Navigate to Country Admin Dashboard$/,() => {
   return CA.Dashboard();
});

Then(/^CA navigates to Refferal Pathway Listings$/, () => {
  return CA.Navigate_To_RPW();
});

Then(/^CA navigates to Application Users Listings$/, () => {
    return CA.Navigate_To_App_Users();
  });

Then(/^CA navigates to Manage Locations Listings$/, () => {
    return CA.Navigate_To_Manage_Locs();
  });

  Then(/^CA navigates to Manage Service Types$/, () => {
    return CA.Navigate_To_Manage_Service_Types();
  });

  Then(/^CA navigates to Manage Organisations$/, () => {
    return CA.Navigate_To_Manage_Organisations();
  });

  Then(/^CA navigates to Manage Services$/, () => {
    return CA.Navigate_To_Manage_Services();
  });

