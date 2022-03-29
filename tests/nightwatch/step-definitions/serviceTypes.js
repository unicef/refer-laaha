const { client } = require('nightwatch-api');
const { Given, Then, When, After, Before } = require('@cucumber/cucumber');

var json = require('../Data/components.json');
const serviceType = client.page.serviceTypesPage();

Given(/^Navigate to Dashboard$/, () => {
    return serviceType.Dashboard();
 });

Then(/^Verify Manage Service Types Elements$/, () => {
    return serviceType.Verify_Manage_Service_Types_Elements();
 });

 Then(/^Verify Add Service Type Elements$/, () => {
    return serviceType.Verify_Add_Service_Type_Elements();
 });

 Then(/^Add Name of Service Type$/, () => {
    return serviceType.Enter_Name_Of_Service_Type(json.ServiceTypeName);
 });

 Then(/^Select Icon from Picker$/, () => {
    return serviceType.Select_From_Icon_Picker();
 });

 Then(/^Select Color from Picker$/, () => {
    return serviceType.Select_From_Color_Picker();
 });

 Then(/^Click Publish$/, () => {
    return serviceType.Click_Publish();
 });