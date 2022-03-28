const { client } = require('nightwatch-api');
const { Given, Then, When, After, Before } = require('@cucumber/cucumber');

var json = require('../Data/components.json');
const Organisation = client.page.manageOrgPage();

Given(/^Navigate to Org Dashboard$/, () => {
    return Organisation.Dashboard();
 });

Then(/^Click Manage Org$/, () => {
    return Organisation.Verify_Add_Organisation_Elements();
 });

 Then(/^Verify Manage Org elements$/, () => {
    return Organisation.Verify_Add_Service_Type_Elements();
 });

 Then(/^Click Add New Org Button$/, () => {
    return Organisation.Enter_Name_Of_Service_Type(json.ServiceTypeName);
 });

