const { client } = require('nightwatch-api');
const { Given, Then, When, After, Before } = require('@cucumber/cucumber');

var json = require('../Data/components.json');
const scorm = client.page.scormPage();


Given(/^Navigate to the scorm page$/, () => {
   return scorm.Navigate_to_Scorm_Page();
});

Then(/^Verify all scorm elements$/, () => {
   return scorm.Verify_Scorm_Detail_Elements();
});

 Then(/^Add scorm Read Time$/, () => {
    return scorm.Scorm_Read_Time(json.ReadTime)
 });

 Then(/^Add Title in scorm$/, () => {
    return scorm.Scorm_Title(json.ScormTitle);
 }); 

 Then(/^Select scorm Language$/, () => {
    return scorm.Scorm_Language_Dropdown();
 });

 Then(/^Add scorm Tags$/, () => {
    return scorm.Scorm_Tags(json.ArticleTags);
 });

Then(/^Add scorm Content Layout$/, () => {
    return scorm.Scorm_Content_Layout();
});

Then(/^Upload scorm Thumbnail Image$/, () => {
    return scorm.Scorm_Thumbnail_Image();
});

Then(/^Select scorm Sub Category$/, () => {
    return scorm.Scorm_Sub_Category();
});

Then(/^Select scorm Moderation State$/, () => {
    return scorm.Scorm_Moderation_State();
});

Then(/^Click on scorm Save button$/, () => {
    return scorm.Save_Scorm();
});

Then(/^Verify created scorm content on FE$/, () => {
    return scorm.Verify_created_scorm_on_FE();
});

Then(/^Delete created scorm content$/, () => {
    return scorm.Delete_created_scorm_content();
});






