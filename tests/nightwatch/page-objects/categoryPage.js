var articleData = require('../Data/components.json');
const { client } = require("nightwatch-api");
var counts = 0;
module.exports = {

    elements: {
        selectCountry: {
            selector: '//*[@name="field_domain"]',
            locateStrategy: 'xpath'
        },
        selectEQcountry: {
            selector: '//*[@name="field_domain"]/option[@value="stage_ec_virtualsafespace_net"]',
            locateStrategy: 'xpath'
        },
        description: {
            selector: '//*[@class="cke_wysiwyg_frame cke_reset"]',
            locateStrategy: 'xpath'
        },
        categoryColor: {
            selector: '//*[@class="color-field-widget-box-form"]/button[3]',
            locateStrategy: 'xpath'
        },
        discoverContent: {
            selector: '//*[@class="form-autocomplete form-text required ui-autocomplete-input"]',
            locateStrategy: 'xpath'
        },
        categoryTerm: {
            selector: '//a[contains(text(), "category stage")]',
            locateStrategy: 'xpath'
        },
        deleteCategory: {
            selector: '//*[@aria-label="Tabs"]/ul/li[3]/a',
            locateStrategy: 'xpath'
        },
        del: '#edit-submit',
        name: '#edit-name-0-value',
        IconUpload: '#edit-field-icon-0-upload-button',
        saveCat: 'edit-submit'

    },    

    commands: [{

        Navigate_to_Category_Page: function () {

        return client.url("https://stage.ec.virtualsafespace.net/admin/structure/taxonomy/manage/categories/add").pause(1000);

        },

        select_country: function() {
            return this
            .assert.visible('@selectCountry', 'Country Dropdown is displayed')
            .click('@selectCountry')
            .click('@selectEQcountry')

        },

        Add_Name: function(name) {
            return this
            .assert.visible('@name', 'name is present')
            .setValue('@name',name)
            .pause(1000)

        },

        Add_Description: function(description) {
            return this
            .assert.visible('@description','Description is present')
            .setValue('@description',description)
            .pause(1000)

        },

        Add_Icon: function () {
            try {           
          this.assert.visible('@IconUpload', 'Icon appears')
          client.execute('window.scrollTo(0,document.body.scrollHeight);');
          this.pause(2000)
          this.waitForElementPresent('@IconUpload')
          .setValue('@IconUpload', '/Users/sushmita.sinha/workspace/Unicef/eRPW/tests/nightwatch/Data/media/image1.png')
          .pause(1000)
          return this;
            }
            catch (err) {
              console.log('Error is : >>>>>>>>>   ', err)
          }
        
        },

        Select_Category_Color: function() {
            return this
            .assert.visible('@categoryColor')
            .click('@categoryColor')
        },

        Add_Discover_Section_Content: function(discoverContent) {
            return this
            .click('@discoverContent')
            .setValue('@discoverContent', discoverContent)

        },
     
        Click_Save: function() {
        return this
        .click('@saveCat')
        .pause(1000)

        },

        verify_created_category_term: function() {
            return this
            .assert.visible('@categoryTerm', 'Category Term is present')
        },

        Delete_created_category_term: function() {
            return this
            .click('@categoryTerm')
            .pause(1000)
            .click('@deleteCategory')
            .pause(1000)
            .click('@del')
            .pause(1000)

        }




    }],

}