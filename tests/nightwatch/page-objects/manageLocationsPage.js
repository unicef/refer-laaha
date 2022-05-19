const { client } = require("nightwatch-api");

module.exports = {
  
  elements: {
    addNewLocationBtn: {
        selector: '//*[@href="/en/add-location"]',
        locateStrategy: 'xpath'
    },
    manageLocations: {
        selector: '//*[@href="/manage-location"]',
        locateStrategy: 'xpath'
    },
    selectCountry: {
        selector: '//*[@name="location_options"]',
        locateStrategy: 'xpath'
    },
    coxBazarSelect: {
       selector: '//*[@name="location_options"]/option[2]',
       locateStrategy: 'xpath'
    },
    selectDepartment: {
        selector: '//*[@name="level2"]',
        locateStrategy: 'xpath'
    },
    selectRegion: {
        selector: '//*[@name="level1"]',
        locateStrategy: 'xpath'
    },
    selectUnion: {
        selector: '//*[@name="level3"]',
        locateStrategy: 'xpath'
    },
    selectNeighbour: {
        selector: '//*[@name="level4"]',
        locateStrategy: 'xpath'
    },
    publishBtn: {
        selector: '//*[@class="button-border button js-form-submit form-submit"]',
        locateStrategy: 'xpath'
    },
    verifyCreatedLocations: {
        selector: '//*[contains(text(),"Delhi Neighbour")]//parent::div/div/div/span[2]/a',
        locateStrategy: 'xpath'
    },
    deleteLoc: {
       selector: '//*[@value="DELETE LOCATION"]',
       locateStrategy: 'xpath'
    },
    csvUpload: {
       selector: '//*[@name="files[import_location_csv_file]"]',
       locateStrategy: 'xpath'
    },
    importCSV: '#edit-open-modal',
    exportCSV: '#edit-export-csv',
    prefLang: '#edit-lang-selector'
},
commands: [
  {
    Navigate_To_Dashboard: function(){
    return client.url("https://stage.erefer.org/en/manage-location").pause(1000);
    },

    Verify_Manage_Locations_elements: function() {
        return this
        .assert.visible('@addNewLocationBtn', 'Add New Location Button is displayed')
        //.assert.visible('@selectCountry', 'Select Country is displayed')
        // .assert.visible('@importCSV', 'Import CSV is displayed')
        // .assert.visible('@exportCSV', 'Export CSV is displayed')
    },

    Click_Manage_Locations: function() {
      return this
      .click('@manageLocations')
      .pause(1000)
    },

    Add_New_Location_Button: function() {
        return this
        .click('@addNewLocationBtn')
        .pause(2000)
    },

    select_Cox_Bazar: function() {
        return this
        .click('@coxBazarSelect')
        //.setValue('@coxBazarSelect')
        .pause(1000)
    },

    select_Region: function(region) {
        return this
        .setValue('@selectRegion', region)
    },

    select_Department: function(department) {
        return this
        .setValue('@selectDepartment', department)
    },

    select_Union: function(union) {
        return this
        .setValue('@selectUnion', union)
    },

    select_Neighbour: function(neighbour) {
        return this
        .setValue('@selectNeighbour', neighbour)
    },

    click_Publish: function() {
        client.execute('window.scrollTo(0,document.body.scrollHeight);');
        this.click('@publishBtn')
        .pause(2000)
    },

    Navigate_To_Manage_Locations: function(){
        return client.url("https://stage.erefer.org/manage-location").pause(1000);
        },

    Preferred_Language_Selection: function() {
        return this
        .click('@prefLang')

    },   

        Verify_created_Location: function() {
            return this
            .assert.visible('@verifyCreatedLocations','Created Location is displayed')
            .click('@verifyCreatedLocations')
            .pause(1000)
            .click('@deleteLoc')
            .pause(1000)

        },

        Import_CSV: function() {
            return this
            .click('xpath','//*[@href="/import-location-modal"]')            
            .pause(2000)
            .setValue('@csvUpload', '/Users/sushmita.sinha/workspace/Unicef/eRPW/tests/nightwatch/Data/media/Cox Bazar.csv')
            .pause(1000)
            .click('xpath', '//*[@value="Import"]')
            .pause(1000)
            .click('xpath', '//*[@value="Import"]')
            .pause(1000)

        },

        Export_CSV: function() {
            return this
            .click('@exportCSV')
            .pause(2000)
        }
















}]
}