const { client } = require('nightwatch-api');
const { Given, Then, When, After, Before } = require('@cucumber/cucumber');

var json = require('../Data/components.json');
const rpw = client.page.referralPathwayPage();

Given(/^Navigate to RPW Dashboard$/, () => {
    return rpw.Dashboard();
 });

 Given(/^Choose your preferred Language$/, () => {
    return rpw.languageselection();
 });

 Then(/^Click on SignIn$/, () => {
 return rpw.signIn();
 });

 Then(/^Verify Manage Referral Pathways Elements$/, () => {
    return rpw.Verify_rpw_elements();
 });

 Then(/^Add New Template for RPW$/, () => {
     return rpw.Add_new_Template();
 });

 Then(/^Add Title of RPW$/, () => {
     return rpw.Enter_Title(json.rpwTitle);
 });

 Then(/^Select Country of RPW$/, () => {
    return rpw.Select_Country();
});

Then(/^Select State of RPW$/, () => {
    return rpw.Select_State();
});

Then(/^Select City of RPW$/, () => {
    return rpw.Select_City();
});

Then(/^Select Colony of RPW$/, () => {
    return rpw.Select_Colony();
});

Then(/^Select Camp of RPW$/, () => {
    return rpw.Select_Camp();
});

Then(/^Add Section Name of RPW$/, () => {
    return rpw.Select_Section(json.rpwSection);
});

Then(/^Add Service Type of RPW$/, () => {
    return rpw.Select_Service_Type(json.rpwServiceType);
});

Then(/^Click Publish of RPW$/, () => {
    return rpw.Publish_RPW();
});