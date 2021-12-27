const { client } = require('nightwatch-api');
const { Given, Then, When, After, Before } = require('@cucumber/cucumber');

var json = require('../Data/components.json');
const article = client.page.articlePage();


Given(/^Navigate to the article page$/, () => {
   return article.Navigate_to_Article_Page();
});

Given(/^navigate to the article page \"([^\"]*)\"$/, (pageURL) => {
    const currentUrl = process.env.CMS_URL;
    const outputURL = currentUrl.substring(0, currentUrl.lastIndexOf('.com') + 4);
    const addURL = utility.getValueFromDataFile(pageURL);
    const finalURL = outputURL + addURL;
    client
        .url(finalURL)
        .pause(1000)

    let menuPage = client.page.coreCMS.cmsMenuPage();
    return menuPage.VERIFY_ARTICLE_URL();
});

When(/^click on discard button$/, () => {
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.CLICK_ON_DISCARD_BUTTON();
});

When(/^enter article headline \"([^\"]*)\"$/, (articleHeadline) => {
    const headline = utility.getValueFromDataFile(articleHeadline);
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.ENTER_ARTICLE_HEADLINE(headline);
});

Then(/^enter brief data \"([^\"]*)\"$/, (articleBrief) => {
    const brief = utility.getValueFromDataFile(articleBrief);
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.ENTER_BRIEF_DATA(brief);
});

Then(/^upload hero image$/, () => {
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.UPLOAD_HERO_IMAGE();
});

Then(/^enter data in content text \"([^\"]*)\"$/, (articleContentText) => {
    const contentText = utility.getValueFromDataFile(articleContentText);
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.ENTER_DATA_IN_CONTENT_TEXT(contentText);
});

Then(/^select article category \"([^\"]*)\"$/, (articleCategory) => {
    const category = utility.getValueFromDataFile(articleCategory);
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.SELECT_ARTICLE_CATEGORY(category);
});

Then(/^select topics \"([^\"]*)\"$/, (articleTopic) => {
    const topic = utility.getValueFromDataFile(articleTopic);
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.SELECT_TOPICS(topic);
});

Then(/^select article flag \"([^\"]*)\"$/, (articleFlag) => {
    const flag = utility.getValueFromDataFile(articleFlag);
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.SELECT_ARTICLE_FLAG(flag);
});

Then(/^select article byline \"([^\"]*)\"$/, (articleByline) => {
    const byline = utility.getValueFromDataFile(articleByline);
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.SELECT_ARTICLE_BYLINE(byline);
});

Then(/^select article status \"([^\"]*)\"$/, (articleStatus) => {
    const status = utility.getValueFromDataFile(articleStatus);
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.VERIFY_ARTICLE_STATUS(status);
});

Then(/^click on save and edit button$/, () => {
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.CLICK_ON_SAVE_EDIT_BUTTON();
});

Then(/^verify that article \"([^\"]*)\" is successfully created$/, (articleHeadline) => {
    const headline = utility.getValueFromDataFile(articleHeadline);
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.VERIFY_ARTCILE_CREATED_SUCCESS_MESSAGE(headline);
});

Then(/^edit the article$/, () => {
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.EDIT_ARTICLE();
});

Then(/^verify headline on edit article page should be \"([^\"]*)\"$/, (articleHeadline) => {
    const headline = utility.getValueFromDataFile(articleHeadline);
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.VERIFY_HEADLINE_ON_EDIT_ARTICLE(headline);
});

When(/^check that Resume edit or Discard pop up appears$/, () => {
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.VERIFY_RESUME_OR_DISCARD_DIALOG();
});

Then(/^verify brief on edit article page should be \"([^\"]*)\"$/, (articleBrief) => {
    const brief = utility.getValueFromDataFile(articleBrief);
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.VERIFY_BRIEF_TEXT_ON_EDIT_ARTICLE(brief);
});

Then(/^verify content text on edit article page should be \"([^\"]*)\"$/, (articleContent) => {
    const contentText = utility.getValueFromDataFile(articleContent);
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.VERIFY_CONTENT_TEXT_ON_EDIT_ARTICLE(contentText);
});

When(/^click on the revisions tab$/, () => {
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.CLICK_ON_REVISIONS_TAB();
});

When(/^click on the edit tab$/, () => {
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.CLICK_ON_EDIT_TAB();
});

When(/^click on the Resume Editing button$/, () => {
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.CLICK_ON_RESUME_EDIT_BUTTON();
});

Then(/^verify that content is locked$/, () => {
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.VERIFY_CONTENT_IS_LOCKED();
});

Given(/^break lock for the content$/, () => {
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.BREAK_LOCK();
});

When(/^verify article \"([^\"]*)\" is successfully deleted$/, (articleHeadline) => {
    const headline = utility.getValueFromDataFile(articleHeadline);
    let articlePage = client.page.coreCMS.cmsArticlePage();
    return articlePage.VERIFY_ARTCILE_DELETED_SUCCESS_MESSAGE(headline);
});