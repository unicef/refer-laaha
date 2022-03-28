var refData = require('../Data/components.json');
const { client } = require("nightwatch-api");
var counts = 0;
module.exports = {
    elements: {
        CreatePodcast: {
            selector: '//*[@class="js-quickedit-page-title page-title"]',
            locateStrategy: 'xpath'
        },
        rpwTitle:{
            selector: '//*[contains(text(),"Add new template for RPW")]',
            locateStrategy: 'xpath'
        },
        rpwTitle:{
           selector: '//*[@name="title[0][value]"]',
           locateStrategy: 'xpath'
        },
        addTemplate:{
          selector: '//*[@href="/node/add/referral_path_way"]',
          locateStrategy: 'xpath'
        },
        EnterCountryList:{
          selector: '//input[@class="select2-search__field"]',
          locateStrategy: 'xpath'
        },
        SelectCountry:{
          selector: '//*[@class="select2 select2-container select2-container--default select2-container--below"]/span/span/span[1]',
          locateStrategy: 'xpath'
        },
        enterSection:{
          selector: '//input[@name="field_section[0][subform][field_section_name][0][value]"]',
          locateStrategy: 'xpath'
        },
        selectServiceType:{
            selector: '//input[@name="field_section[0][subform][field_service_type][0][target_id]"]',
            locateStrategy: 'xpath'
        },
        rpwPublish:{
            selector: '//input[@value="PUBLISH"]',
            locateStrategy: 'xpath'
        },
        selectState:{
            selector: '//*[@title="Select 1 Label"]',
            locateStrategy: 'xpath'
        },
        SelectState:{
           selector: '//*[@class="select2-results__options"]/li[2]',
           locateStrategy: 'xpath'
        },
        SelectCity:{
           selector: '//*[@title="Select 2 Label"]',
           locateStrategy: 'xpath'
        },
        SelectColony:{
           selector: '//*[@title="Select 3 Label"]',
           locateStrategy: 'xpath'
        },
        languagesubmit: '#edit-lang-selector',
        signIn: 'edit-export-csv',
        selectCountry: '#select2-edit-level-0-container'


    },    

    commands: [{
        Dashboard: function(){
            return client.url("https://stage.erefer.org/en/referral-pathway-listing").pause(1000);
            },

            languageselection: function(){
                return this
                .click('@languagesubmit')
                .pause(1000)

            },

            signIn: function(){
            return client.url("https://stage.erefer.org/user/login")
               },         

            

            Verify_rpw_elements: function(){
            return this
            // .assert.visible('@rpwTitle','Title is present')
            .pause(1000)
            },

            Add_new_Template: function(){
                return this
                .click('@addTemplate')
                .pause(1000)
                .click('@rpwTitle')
                .pause(1000)
            },

            Enter_Title: function(NAME){
                return this
                .setValue('@rpwTitle',NAME)

            },

            Select_Country: function() {
                return this
                .click('@selectCountry')
                // .setValue('@SelectCountry',Bangladesh)
                .pause(1000)
                
            },
            Select_State: function(Cox) {
                return this
                // .click('@selectState')
                // .pause(1000)
                .click('@SelectState')
                // .setValue('@SelectState',Cox)
                .pause(1000)
                
            },

            Select_City: function() {
                return this
                .click('@SelectCity')
                .pause(1000)
                .click('@SelectState')
                .pause(1000)
                
            },
            Select_Colony: function() {
                return this
                .click('@SelectColony')
                .pause(1000)
                .click('@SelectState')
                .pause(1000)          
            },

            Select_Camp: function() {
                return this
                .click('@SelectColony')
                .pause(1000)
                .click('@SelectState')
                .pause(1000)          
            },


            Select_Section: function(newSection){
                return this
                .click('@enterSection')
                .setValue('@enterSection', newSection)
            },

            Select_Service_Type: function(Health){
                return this
                .setValue('@selectServiceType', Health)
            },

            Publish_RPW: function(){
            return this
            .click('@rpwPublish')
            .pause(3000)
            }

    }],

}
