const { client } = require('nightwatch-api');
const { Given, Then, When, After, Before } = require('@cucumber/cucumber');

var json = require('../Data/components.json');
const services = client.page.manageServicesPage();

Given(/^Navigate to Manage Services Dashboard$/, () => {
    return services.Navigate_To_Dashboard();  
  });
  
  Then(/^Submit Language Preferences$/, () => {
    return services.Click_on_Submit();
  });

  Then(/^Click on Add New Service$/, () => {
  return services.Add_new_service();
  });

  Then(/^Add Service Type dropdown$/, () => {
      return services.select_Service_Type_value();
  });

  