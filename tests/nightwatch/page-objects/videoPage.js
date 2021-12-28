var videoData = require('../Data/components.json');
const { client } = require("nightwatch-api");
var counts = 0;
module.exports = {
    elements: {
        CreateVideo: {
            selector: '//*[@class="js-quickedit-page-title page-title"]',
            locateStrategy: 'xpath'
        },
        ContentLayout: {
            selector: '//*[@data-target-id="field-content-wrapper--active-items"]',
            locateStrategy: 'xpath'
        },
        selectVideo: {
            selector: '//*[@href= "#video"]',
            locateStrategy: 'xpath'
        },
        VideoTitle: '#edit-title-0-value',
        LanguageDropdown: '#edit-langcode-0-value',
        Tags: '#edit-field-tags-0-target-id',
        ThumbnailImage: '#edit-field-thumbnail-image-0-upload',
        SubCategory: '#edit-field-sub-category',
        ModerationState: '#edit-moderation-state-0-state',
        SaveBtn: '#edit-submit',
        Preview: '#edit-preview',
    },    

    commands: [{

        Navigate_to_Article_Page: function () {
            try{
                return client.url("https://stage.ec.virtualsafespace.net/node/add/video").pause(1000);
            }
            catch(err) {
                console.log('Error is >>>>>>>>    ' + err)
            }

        },

        Verify_Video_Detail_Elements: function () {
            return this
            .assert.visible('@CreateVideo', 'Create Video is displayed')
            .assert.visible('@VideoTitle', 'Video Title is present')
            .assert.visible('@LanguageDropdown', 'Language Dropdown is present')
            .assert.visible('@Tags','Tag field is displayed')
            .assert.visible('@ContentLayout', 'Content Layout icon is displayed')
            .assert.visible('@ThumbnailImage', 'Thumbnail Image appears')
            .assert.visible('@SubCategory', 'Sub Category is present')
            .assert.visible('@ModerationState','Moderate State is displayed')
            .assert.visible('@SaveBtn', 'Save Button is present')
            .assert.visible('@Preview', 'Preview is present')
        },

        Video_Title: function (Video) {
            return this
            .setValue('@VideoTitle', Video)
            .pause(1000)
          },

          Video_Language_Dropdown: function () {
            return this
            .click('@LanguageDropdown')
            .pause(1000)
            .waitForElementPresent('#edit-langcode-0-value > option:nth-child(1)')
            .click('#edit-langcode-0-value > option:nth-child(1)')

          },

          Video_Tags: function (Tags) {
            return this
            .click('@Tags')
            .setValue('@Tags',Tags)
            .pause(1000)

          },

          Video_Content_Layout: function () {
            return this
            .click('css selector','[class="layout-paragraphs-add-content__toggle"]')
            .pause(1000)
            .click('xpath', '//*[@href="#layout"]')
            .pause(1000)
            .click('xpath','//span[contains(text(), "One column")]')
            .pause(1000)
            .click('xpath','//*[@class="button js-form-submit form-submit ui-button ui-corner-all ui-widget"]')
            .pause(1000)
            .click('xpath','//*[@data-target-id = "field-content-wrapper--item-0-content"]')
            .pause(1000)
            .click('@selectVideo')
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




    }],

}

