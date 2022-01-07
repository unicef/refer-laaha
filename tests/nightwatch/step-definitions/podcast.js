const { client } = require('nightwatch-api');
const { Given, Then, When, After, Before } = require('@cucumber/cucumber');

var json = require('../Data/components.json');
const podcast = client.page.podcastPage();

Given(/^Navigate to the podcast page$/, () => {
    return podcast.Navigate_to_Podcast_Page();
 });

 Then(/^Verify Create Podcast$/, () => {
    return podcast.Verify_Podcast_Detail_Elements();
 });
 
 Then(/^Verify Podcast Title$/, () => {
    return podcast.Verify_Podcast_Detail_Elements();
 });
 
 Then(/^Verify Podcast Language Dropdown$/, () => {
     return podcast.Verify_Podcast_Detail_Elements();
  });
 
  Then(/^Verify Podcast Tags$/, () => {
     return podcast.Verify_Podcast_Detail_Elements();
  });
  
  Then(/^Verify Podcast Content Layout$/, () => {
     return podcast.Verify_Podcast_Detail_Elements();
  });
 
  Then(/^Verify Podcast Thumbnail Image$/, () => {
     return podcast.Verify_Podcast_Detail_Elements();
  });
 
  Then(/^Verify Podcast Sub Category Dropdown$/, () => {
     return podcast.Verify_Podcast_Detail_Elements();
  });
 
  Then(/^Verify Podcast Moderation State$/, () => {
     return podcast.Verify_Podcast_Detail_Elements();
  });
 
  Then(/^Verify Podcast Save Button$/, () => {
     return podcast.Verify_Podcast_Detail_Elements();
  });
 
  Then(/^Verify Podcast Preview$/, () => {
     return podcast.Verify_Podcast_Detail_Elements();
  });
 
  Then(/^Add Title in Podcast$/, () => {
     return podcast.Podcast_Title(json.PodcastTitle);
  }); 
 
  Then(/^Select Podcast Language$/, () => {
     return podcast.Podcast_Title_Language_Dropdown();
  });
 
  Then(/^Add Podcast Tags$/, () => {
     return podcast.Podcast_Tags(json.ArticleTags);
  });
 
 Then(/^Add Podcast Content Layout$/, () => {
     return podcast.Podcast_Content_Layout();
 });

 Then(/^Upload Podcast Thumbnail Image$/, () => {
   return podcast.Podcast_Thumbnail_Image();
});

Then(/^Select Podcast Sub Category$/, () => {
   return podcast.Podcast_Sub_Category();
});

Then(/^Click on Podcast Save button$/, () => {
   return podcast.Podcast_Save();
});
 
 