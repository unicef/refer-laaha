const { client } = require("nightwatch-api");

module.exports = {
  
  elements: {
    
    submitBtn: '#edit-lang-selector',
    addNewServiceBtn: {
        selector: '//*[contains(text(),"Add a new Service")]',
        locateStrategy: 'xpath'
    },
    selectCountry: {
        selector: '//*[@title="Select country"]',
        locateStrategy: 'xpath'
    },
    serviceTypeDD: {
        selector: '//*[@aria-controls="select2-edit-field-service-type-container"]',
        locateStrategy: 'xpath'
    }
},
commands: [
  {
    Navigate_To_Dashboard: function(){
    return client.url("https://stage.erefer.org/en/manage-service").pause(1000);
    },

    Click_on_Submit: function() {
        client.execute('window.scrollTo(0,document.body.scrollHeight);');
        return this
        .click('@submitBtn')
        .pause(2000)
    },

    Add_new_service: function(){
        return this
        .click('@addNewServiceBtn')
        .pause(2000)
    },

    select_Country: function() {
        return this
        .click('@selectCountry')
        .setValue()
    },

    select_Service_Type_value: function() {
        return this
        .click('@serviceTypeDD')
    }

    
}]
}