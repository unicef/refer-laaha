var articleData = require('../Data/components.json');
const { client } = require("nightwatch-api");
var counts = 0;
module.exports = {

    elements: {
        CreateScorm: {
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
        selectScorm: {
           selector: '//*[@href= "#scorm"]',
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
        createdScorm: {
          selector: '//a[contains(text(), "Automated Scorm Detail Page")]',
          locateStrategy: 'xpath'
        },
        deleteScorm: {
          selector: '//*[@aria-label="Tabs"]/ul/li[4]/a',
          locateStrategy: 'xpath'
        },
        articlemoderationstate: '#edit-moderation-state-0-state > option:nth-child(1)',
        ScormTitle: '#edit-title-0-value',
        LanguageDropdown: '#edit-langcode-0-value',
        Tags: '#edit-field-tags-0-target-id',
        ContentLayout: '#field-content-wrapper--active-items',
        ThumbnailImage: '#edit-field-thumbnail-image-0-upload',
        SubCategory: '#edit-field-sub-category',
        ModerationState: '#edit-moderation-state-0-state',
        SaveBtn: '#edit-submit',
        Preview: '#edit-preview',
        imgupload:'[id="edit-field-thumbnail-image-0-upload"]',
        readtime: '[id="edit-field-read-time-0-value"]'
    },    

    commands: [{

        Navigate_to_Scorm_Page: function () {

        return client.url("https://stage.ec.virtualsafespace.net/node/add/scorm").pause(1000);

        },

        Verify_Scorm_Detail_Elements: function () {
            return this
            .assert.visible('@CreateScorm', 'Create Scorm is displayed')
            .assert.visible('@ScormTitle', 'Article Title is present')
            .assert.visible('@LanguageDropdown', 'Language Dropdown is present')
            .assert.visible('@Tags','Tag field is displayed')
            .assert.visible('@ContentLayout', 'Content Layout icon is displayed')
            .assert.visible('@ThumbnailImage', 'Thumbnail Image appears')
            .assert.visible('@SubCategory', 'Sub Category is present')
            .assert.visible('@ModerationState','Moderate State is displayed')
            .assert.visible('@SaveBtn', 'Save Button is present')
            .assert.visible('@Preview', 'Preview is present')
        },

        Scorm_Title: function (Scorm) {
          return this
          .setValue('@ScormTitle', Scorm)
          .pause(1000)
        },

          Scorm_Language_Dropdown: function () {
            return this
            .click('@LanguageDropdown')
            .pause(1000)
            .waitForElementPresent('#edit-langcode-0-value > option:nth-child(1)')
            .click('#edit-langcode-0-value > option:nth-child(1)')

          },

          Scorm_Tags: function (Tags) {
            return this
            .click('@Tags')
            .setValue('@Tags',Tags)
            .pause(1000)

          },

          Scorm_Content_Layout: function () {
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
              .click('@selectScorm')
              .pause(1000)
              // .click('@imageAddMedia')
              // .pause(1000)
              // .click('@selectImageFromLibrary')
              // .pause(1000)
              // .click('@clickInsertSelected')
              // .pause(3000)
              // .click('@saveButtonOnImgMedia')
              // .pause(2000)
          },

          Scorm_Thumbnail_Image: function () {
              try {           
            this.assert.visible('@ThumbnailImage', 'Thumbnail Image appears')
            client.execute('window.scrollTo(0,document.body.scrollHeight);');
            this.pause(2000)
            this.waitForElementPresent('@thumbnailClick')
            this.click('xpath','//div[@class="image-widget-data"]')            
            this.pause(3000)
            .setValue('@imgupload', '/Users/sushmita.sinha/workspace/Unicef/eRPW/tests/nightwatch/Data/media/image1.png')
            .pause(1000)
            return this;
              }
              catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
          
          },

          Scorm_Sub_Category: function () {
              try{
                  return this
                     .click('@articleSubCategory')
                     .pause(1000)
                     .waitForElementPresent('xpath','//*[contains(text(), "About Covid")]')
                     .click('xpath','//*[contains(text(), "About Covid")]')
                     .pause(2000)
              }
              catch (err) {
                  console.log('Error is : >>>>>>>>>>   ',err)
              }
          },
          Scorm_Read_Time: function (readtime) {
            return this
            .click('@readtime')
            .setValue('@readtime', readtime)
            .pause(1000)

          },

          Scorm_Moderation_State: function () {
              try{
                  return this
                     .setValue('articlemoderationstate')
                     .pause(1000)
              }
              catch (err) {
                console.log('Error is : >>>>>>>>>>   ',err)
            }

          },

          Save_Scorm: function () {
              try{
                  return this
                      .click('#edit-submit')
                      .pause(2000)
              }
              catch (err) {
                console.log('Error is : >>>>>>>>>>   ',err)
            }
          },

          Verify_created_scorm_on_FE: function() {
            return this
            .assert.visible('@createdScorm', 'Created Scorm is present on FE')
          },

          Delete_created_scorm_content: function() {
            return this
            .assert.visible('@createdScorm')
            .click('@createdScorm')
            .pause(3000)
            .click('@deleteScorm')
            .pause(2000)
            .click('@SaveBtn')
            .pause(2000)



          }

    }],

}