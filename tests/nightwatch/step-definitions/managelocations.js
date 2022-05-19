const { client } = require('nightwatch-api');
const { Given, Then, When, After, Before } = require('@cucumber/cucumber');

var json = require('../Data/components.json');
const locations = client.page.manageLocationsPage();

Given(/^Navigate to Locations Dashboard$/, () => {
    return locations.Navigate_To_Dashboard();  
  });
  
  Then(/^Click Manage Locations$/, () => {
    return locations.Click_Manage_Locations();
  });

  Then(/^Verify Manage Locations elements$/, () => {
    return locations.Verify_Manage_Locations_elements();
  });

  Then(/^Click Add New Location Button$/, () => {
    return locations.Add_New_Location_Button();
  });

  Then(/^Select Cox Bazar$/, () => {
    return locations.select_Cox_Bazar();
  });

  Then(/^Add Region$/, () => {
      return locations.select_Region(json.region);
  });

  Then(/^Add Department$/, () => {
    return locations.select_Department(json.department);
});

Then(/^Add Union$/, () => {
    return locations.select_Union(json.union);
});

Then(/^Add Neighbour$/, () => {
    return locations.select_Neighbour(json.neighbour);
});

Then(/^Click Location Publish$/, () => {
    return locations.click_Publish();
});

Then(/^Navigate to Manage Locations$/, () => {
    return locations.Navigate_To_Manage_Locations();
});

Then(/^Select Preferred Language$/, () => {
  return locations.Preferred_Language_Selection();
});

Then(/^Import CSV$/, () => {
  return locations.Import_CSV();
});

Then(/^Export CSV$/, () => {
  return locations.Export_CSV();
});