const { client } = require('nightwatch-api');
const { Given, Then, When, And } = require('@cucumber/cucumber');
var headerscreen = require('../Data/components.json');
const login = client.page.loginPage();
const header = client.page.headerPage();
const footer = client.page.footerPage();

Given(/^Verify Exit Functionality$/, () => {
  return header.Verify_Exit_Website();  
});

Then(/^Click on Exit Functionality$/, () => {
  return header.Click_Exit_Website_Button();
});

Then(/^Verify VSS Title$/, () => {
  return header.Verify_VSS_Title();
});

Given(/^Naviagte to Header Site Navigation Menus$/, () => {
  return footer.Navigate_To_Menu_List();
});

Then(/^Verify Header Site Navigation Menus$/, () => {
   return header.Verify_Header_Site_Nav();
});

Given(/^Verify Language Switcher$/, () => {
    return header.Verify_Language_Switcher();
});

Then(/^Verify VSS Logo$/, () => {
  return header.Verify_VSS_Logo();
});

Then(/^Verify Discover nav Menu$/, () => {
  return header.Verify_Discover_Nav_Menu_Terms();
});

Then(/^Verify FAQs nav menu$/, () => {
  return header.Verify_FAQs_menu();
});

Then(/^Verify Get In Touch nav menu$/, () => {
  return header.Verify_GIT_menu();
});

Then(/^Verify Resource Library nav menu$/, () => {
  return header.Verify_ResourceLibrary_menu();
});

Then(/^Navigate to created Header Menu Items$/, () => {
  return header.Delete_Created_Header_Menu_Item();
});