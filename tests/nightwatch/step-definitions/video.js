const { client } = require('nightwatch-api');
const { Given, Then, When, After, Before } = require('@cucumber/cucumber');

var json = require('../Data/components.json');
const video = client.page.videoPage();

Given(/^Navigate to the video page$/, () => {
    return video.Navigate_to_Article_Page();
 });

 Then(/^Verify Create video$/, () => {
    return video.Verify_Video_Detail_Elements();
 });
 
 Then(/^Verify Video Title$/, () => {
    return video.Verify_Video_Detail_Elements();
 });
 
 Then(/^Verify Video Language Dropdown$/, () => {
     return video.Verify_Video_Detail_Elements();
  });
 
  Then(/^Verify Video Tags$/, () => {
     return video.Verify_Video_Detail_Elements();
  });
  
  Then(/^Verify Video Content Layout$/, () => {
     return video.Verify_Video_Detail_Elements();
  });
 
  Then(/^Verify Video Thumbnail Image$/, () => {
     return video.Verify_Video_Detail_Elements();
  });
 
  Then(/^Verify Video Sub Category Dropdown$/, () => {
     return video.Verify_Video_Detail_Elements();
  });
 
  Then(/^Verify Video Moderation State$/, () => {
     return video.Verify_Video_Detail_Elements();
  });
 
  Then(/^Verify Video Save Button$/, () => {
     return video.Verify_Video_Detail_Elements();
  });
 
  Then(/^Verify Video Preview$/, () => {
     return video.Verify_Video_Detail_Elements();
  });
 
  Then(/^Add Title in Video$/, () => {
     return video.Video_Title(json.VideoTitle);
  }); 
 
  Then(/^Select Video Language$/, () => {
     return video.Video_Language_Dropdown();
  });
 
  Then(/^Add Video Tags$/, () => {
     return video.Video_Tags(json.ArticleTags);
  });
 
 Then(/^Add Video Content Layout$/, () => {
     return video.Video_Content_Layout();
 });
 
 