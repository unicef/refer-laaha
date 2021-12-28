const { client } = require('nightwatch-api');
const { Given, Then, When, And } = require('@cucumber/cucumber');
var footerscreen = require('../Data/components.json');
const login = client.page.loginPage();
const footer = client.page.footerPage();


Given(/^Naviagte to Footer Quick Links Menus$/, () => {
  return footer.Navigate_To_Menu_List();
});

Then(/^Verify Footer Quick Links$/, () => {
  return footer.Verify_Footer_Quick_Links();
});

Then(/^Add Link$/, ()  => {
  return footer.Add_Menu_Link(footerscreen.link);
});

Then(/^Add Title$/, ()  => {
  return footer.Add_Menu_Title(footerscreen.title);
});

Then(/^Click Save$/, () => {
  return footer.Click_Save();
});

Then(/^Edit Title of created Quick Link$/, () => {
  return footer.Edit_Quick_Link_Menu_Title(footerscreen.edited_title);
});

Then(/^Save Edited Quick Link$/, () => {
  return footer.Click_Save();
});



