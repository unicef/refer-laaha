const { client } = require('nightwatch-api');
const { Given, Then, When, After, Before } = require('@cucumber/cucumber');

var json = require('../Data/components.json');
const workflow = client.page.workflowModPage();

Given(/^Navigate to the Article Content$/, () => {
    return workflow.content_navigation();
 });

 Then(/^Click on Article Edit$/,() => {
    return workflow.Click_On_Article_Edit();
 });

 Then(/^select article status$/,() => {
  // const status = utils.utility.getValueFromDataFile(articleStatus);
   return workflow.VERIFY_ARTICLE_STATUS(json.articleStatus);
});

Then(/^verify article is visible on frontend$/, () => {
       return workflow.CONTENT_VISIBLE_ON_FE();
   });

Then(/^click save$/, () => {
      return workflow.save();
  });