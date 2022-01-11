var articleData = require('../Data/components.json');
const { client } = require("nightwatch-api");
var counts = 0;
module.exports = {

    elements: {
        CreateArticle: {
            selector: '//*[@class="js-quickedit-page-title page-title"]',
            locateStrategy: 'xpath'
        },
        ImageInLayout: {
            selector: '//*[@title = "Create new Image"]',
            locateStrategy: 'xpath'
        },
        PlusInContentLayout: {
            selector: '//div[@id="field-assets-wrapper--active-items"]/button',
            locateStrategy: 'xpath'
        },
        thumbnailClick: '#edit-field-thumbnail-image-0-upload-button',
        ThumbnailImageUpload: {
           selector: '//input[@id="edit-field-thumbnail-image-0-upload"]',            
           locateStrategy: 'xpath'
        },
        layoutBtn: {
          selector: '[class="layout-paragraphs-add-content__toggle"]',
          locateStrategy: 'xpath'
        },
        layout: {
            selector: '[class="layout-paragraphs-add-more-menu__item paragraph-type-layout layout-paragraph"]',
            locateStrategy: 'xpath'
        },
        selectImage: {
           selector: '//*[@href= "#image"]',
           locateStrategy: 'xpath'
        },
        imageAddMedia: {
           selector: '//*[@value= "Add media"]',
           locateStrategy: 'xpath'
        },
        selectImageFromLibrary: {
          selector: '//*[@data-quickedit-field-id= "media/71/thumbnail/en/media_library"]',
          locateStrategy: 'xpath'
        },
        clickInsertSelected: {
          selector: '//*[@class= "media-library-select button button--primary js-form-submit form-submit ui-button ui-corner-all ui-widget"]',
          locateStrategy: 'xpath'
        },
        saveButtonOnImgMedia: {
          selector: '//*[@class= "button js-form-submit form-submit ui-button ui-corner-all ui-widget"]',
          locateStrategy: 'xpath'
        },
        articleSubCategory: {
           selector: '//*[@id= "edit-field-sub-category"]',
           locateStrategy: 'xpath'
        },
        articlemoderationstate: '#edit-moderation-state-0-state > option:nth-child(1)',
        ArticleTitle: '#edit-title-0-value',
        LanguageDropdown: '#edit-langcode-0-value',
        Tags: '#edit-field-tags-0-target-id',
        ContentLayout: '#field-assets-wrapper--active-items',
        ThumbnailImage: '#edit-field-thumbnail-image-0-upload',
        SubCategory: '#edit-field-sub-category',
        ModerationState: '#edit-moderation-state-0-state',
        SaveBtn: '#edit-submit',
        Preview: '#edit-preview',
        imgupload:'[id="edit-field-thumbnail-image-0-upload"]',
        readtime: '[id="edit-field-read-time-0-value"]'
    },    

    commands: [{

        Navigate_to_Article_Page: function () {

        return client.url("https://stage.ec.virtualsafespace.net/node/add/article").pause(1000);

        },

        Verify_Artcile_Detail_Elements: function () {
            return this
            .assert.visible('@CreateArticle', 'Create Article is displayed')
            .assert.visible('@ArticleTitle', 'Article Title is present')
            .assert.visible('@LanguageDropdown', 'Language Dropdown is present')
            .assert.visible('@Tags','Tag field is displayed')
            .assert.visible('@ContentLayout', 'Content Layout icon is displayed')
            .assert.visible('@ThumbnailImage', 'Thumbnail Image appears')
            .assert.visible('@SubCategory', 'Sub Category is present')
            .assert.visible('@ModerationState','Moderate State is displayed')
            .assert.visible('@SaveBtn', 'Save Button is present')
            .assert.visible('@Preview', 'Preview is present')
        },

        Article_Title: function (Article) {
          return this
          .setValue('@ArticleTitle', Article)
          .pause(1000)
        },

          Article_Language_Dropdown: function () {
            return this
            .click('@LanguageDropdown')
            .pause(1000)
            .waitForElementPresent('#edit-langcode-0-value > option:nth-child(1)')
            .click('#edit-langcode-0-value > option:nth-child(1)')

          },

          Article_Tags: function (Tags) {
            return this
            .click('@Tags')
            .setValue('@Tags',Tags)
            .pause(1000)

          },

          Article_Content_Layout: function () {
              return this
              .click('css selector','[class="layout-paragraphs-add-content__toggle"]')
              .pause(1000)
              .click('xpath', '//*[@href="#layout"]')
              .pause(1000)
              .click('xpath','//span[contains(text(), "One column")]')
              .pause(1000)
              .click('xpath','//*[@class="button js-form-submit form-submit ui-button ui-corner-all ui-widget"]')
              .pause(1000)
              .click('xpath','//*[@data-target-id = "field-assets-wrapper--item-0-content"]')
              .pause(1000)
              .click('@selectImage')
              .pause(1000)
              .click('@imageAddMedia')
              .pause(1000)
              .click('@selectImageFromLibrary')
              .pause(1000)
              .click('@clickInsertSelected')
              .pause(3000)
              .click('@saveButtonOnImgMedia')
              .pause(2000)
          },

          Article_Thumbnail_Image: function () {
              try {           
            this.assert.visible('@ThumbnailImage', 'Thumbnail Image appears')
            client.execute('window.scrollTo(0,document.body.scrollHeight);');
            this.pause(2000)
            this.waitForElementPresent('@thumbnailClick')
            this.click('xpath','//div[@class="image-widget-data"]')            
            this.pause(3000)
            .setValue('@imgupload', 'https://stage.ec.virtualsafespace.net/profiles/unicef/themes/custom/vss/nightwatch-media/image1.png')
            .pause(1000)
            return this;
              }
              catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
          
          },

          Article_Sub_Category: function () {
              try{
                  return this
                     .click('@articleSubCategory')
                     .pause(1000)
                     .waitForElementPresent('#edit-field-sub-category > option:nth-child(2)')
                     .click('#edit-field-sub-category > option:nth-child(2)')
                     .pause(2000)
              }
              catch (err) {
                  console.log('Error is : >>>>>>>>>>   ',err)
              }
          },
          Article_Read_Time: function (readtime) {
            return this
            .click('@readtime')
            .setValue('@readtime', readtime)
            .pause(1000)

          },

          Article_Moderation_State: function () {
              try{
                  return this
                     .setValue('articlemoderationstate')
                     .pause(1000)
              }
              catch (err) {
                console.log('Error is : >>>>>>>>>>   ',err)
            }

          },

          Save_Article: function () {
              try{
                  return this
                      .click('#edit-submit')
                      .pause(2000)
              }
              catch (err) {
                console.log('Error is : >>>>>>>>>>   ',err)
            }
          },

    }],

}