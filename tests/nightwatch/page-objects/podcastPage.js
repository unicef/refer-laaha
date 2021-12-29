var videoData = require('../Data/components.json');
const { client } = require("nightwatch-api");
var counts = 0;
module.exports = {
    elements: {
        CreatePodcast: {
            selector: '//*[@class="js-quickedit-page-title page-title"]',
            locateStrategy: 'xpath'
        },
        ContentLayout: {
            selector: '//*[@data-target-id="field-content-wrapper--active-items"]',
            locateStrategy: 'xpath'
        },
        selectPodcastAudio: {
            selector: '//*[@href= "#podcast_audio"]',
            locateStrategy: 'xpath'
        },
        UploadAudio: {
            selector: '//input[@name="files[field_content_entity_form_1_field_audio_0]"]',
            locateStrategy: 'xpath'
        },
        UploadVTT: {
            selector: '//input[@name="files[field_content_entity_form_1_field_vtt_entries_0_subform_field_vtt_file_0]"]',
            locateStrategy: 'xpath'
        },
        saveAudioButton: {
            selector: '//*[@class= "button js-form-submit form-submit ui-button ui-corner-all ui-widget"]',
            locateStrategy: 'xpath'
          },
          thumbnailClick: {
              selector: '//*[@data-drupal-selector="edit-field-thumbnail-image-0-upload-button"]',
              locateStrategy: 'xpath'
          },
          audioSubCategory: {
            selector: '//*[@id= "edit-field-sub-category"]',
            locateStrategy: 'xpath'
         },
          
        PodcastTitle: '#edit-title-0-value',
        LanguageDropdown: '#edit-langcode-0-value',
        Tags: '#edit-field-tags-0-target-id',
        ThumbnailImage: '#edit-field-thumbnail-image-0-upload',
        SubCategory: '#edit-field-sub-category',
        ModerationState: '#edit-moderation-state-0-state',
        SaveBtn: '#edit-submit',
        Preview: '#edit-preview',
        imgupload:'[id="edit-field-thumbnail-image-0-upload"]'

    },    

    commands: [{

        Navigate_to_Podcast_Page: function () {
            try{
                return client.url("https://stage.ec.virtualsafespace.net/node/add/podcast").pause(1000);
            }
            catch(err) {
                console.log('Error is >>>>>>>>    ' + err)
            }

        },

        Verify_Podcast_Detail_Elements: function () {
            return this
            .assert.visible('@CreatePodcast', 'Create Video is displayed')
            .assert.visible('@PodcastTitle', 'Video Title is present')
            .assert.visible('@LanguageDropdown', 'Language Dropdown is present')
            .assert.visible('@Tags','Tag field is displayed')
            .assert.visible('@ContentLayout', 'Content Layout icon is displayed')
            .assert.visible('@ThumbnailImage', 'Thumbnail Image appears')
            .assert.visible('@SubCategory', 'Sub Category is present')
            .assert.visible('@ModerationState','Moderate State is displayed')
            .assert.visible('@SaveBtn', 'Save Button is present')
            .assert.visible('@Preview', 'Preview is present')
        },

        Podcast_Title: function (Podcast) {
            return this
            .setValue('@PodcastTitle', Podcast)
            .pause(1000)
          },

          Podcast_Title_Language_Dropdown: function () {
            return this
            .click('@LanguageDropdown')
            .pause(1000)
            .waitForElementPresent('#edit-langcode-0-value > option:nth-child(1)')
            .click('#edit-langcode-0-value > option:nth-child(1)')

          },

          Podcast_Tags: function (Tags) {
            return this
            .click('@Tags')
            .setValue('@Tags',Tags)
            .pause(1000)

          },

          Podcast_Content_Layout: function () {
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
            .click('@selectPodcastAudio')
            .pause(1000)
            .setValue('@UploadAudio','/Users/sushmita.sinha/workspace/Unicef/eRPW/tests/nightwatch/Data/media/horse.mp3')
            .pause(3000)
            .click('xpath','//select[@name="field_content[entity_form][1][field_vtt_entries][0][subform][field_language]"]/option[3]')
            .pause(1000)
            .click('@UploadVTT')
            .pause(1000)
            .setValue('@UploadVTT','/Users/sushmita.sinha/workspace/Unicef/eRPW/tests/nightwatch/Data/media/Example.vtt')
            .pause(3000)
            .click('@saveAudioButton')
            .pause(2000)
        },

        Podcast_Thumbnail_Image: function () {
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

        Podcast_Sub_Category: function () {
            try{
                return this
                   .click('@audioSubCategory')
                   .pause(1000)
                   .waitForElementPresent('#edit-field-sub-category > option:nth-child(20)')
                   .click('#edit-field-sub-category > option:nth-child(20)')
                   .pause(2000)
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>>   ',err)
            }
        },

        Podcast_Save: function () {
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

