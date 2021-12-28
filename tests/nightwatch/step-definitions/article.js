const { client } = require('nightwatch-api');
const { Given, Then, When, After, Before } = require('@cucumber/cucumber');

var json = require('../Data/components.json');
const article = client.page.articlePage();


Given(/^Navigate to the article page$/, () => {
   return article.Navigate_to_Article_Page();
});

Then(/^Verify Create Article$/, () => {
   return article.Verify_Artcile_Detail_Elements();
});

Then(/^Verify Article Title$/, () => {
   return article.Verify_Artcile_Detail_Elements();
});

Then(/^Verify Article Language Dropdown$/, () => {
    return article.Verify_Artcile_Detail_Elements();
 });

 Then(/^Verify Artcile Tags$/, () => {
    return article.Verify_Artcile_Detail_Elements();
 });
 
 Then(/^Verify Article Content Layout$/, () => {
    return article.Verify_Artcile_Detail_Elements();
 });

 Then(/^Verify Article Thumbnail Image$/, () => {
    return article.Verify_Artcile_Detail_Elements();
 });

 Then(/^Verify Article Sub Category Dropdown$/, () => {
    return article.Verify_Artcile_Detail_Elements();
 });

 Then(/^Verify Article Moderation State$/, () => {
    return article.Verify_Artcile_Detail_Elements();
 });

 Then(/^Verify Article Save Button$/, () => {
    return article.Verify_Artcile_Detail_Elements();
 });

 Then(/^Verify Article Preview$/, () => {
    return article.Verify_Artcile_Detail_Elements();
 });

 Then(/^Add Title in Article$/, () => {
    return article.Article_Title(json.ArticleTitle);
 }); 

 Then(/^Select Article Language$/, () => {
    return article.Article_Language_Dropdown();
 });

 Then(/^Add Article Tags$/, () => {
    return article.Article_Tags(json.ArticleTags);
 });

Then(/^Add Article Content Layout$/, () => {
    return article.Article_Content_Layout();
});

Then(/^Upload Article Thumbnail Image$/, () => {
    return article.Article_Thumbnail_Image();
});

Then(/^Select Article Sub Category$/, () => {
    return article.Article_Sub_Category();
});

Then(/^Select Moderation State$/, () => {
    return article.Article_Moderation_State();
});

Then(/^Click on Save button$/, () => {
    return article.Save_Article();
});






