const { client } = require("nightwatch-api");

module.exports = {
  
  elements: {
      Title: {
          selector: '//*[contains(text(), "Manage service types")]',
          locateStrategy: 'xpath'
      },
      ServiceTypeTitle: {
          selector: '//*[contains(text(), "Add Service Type")]',
          locateStrategy: 'xpath'
      },
      selectColor: {
         selector: '//*[@color="#62489b"]',
         locateStrategy: 'xpath'
      },
      manageservicetypes: '.dashboard-link service-link',
      addNewServiceType: {
          selector: '//*[@href="/manage-service-types"]',
          locateStrategy: 'xpath'
      },
      addNewServiceTypeBtn: {
         selector: '//*[@href="/node/add/service_type"]',
         locateStrategy: 'xpath'
      },
      nameOfServiceType: '#edit-title-0-value',
      iconPicker: '.button-with-icon icon-select js-form-required form-required',
      colorPicker: '.button-with-icon color-select js-form-required form-required',
      draft: '#edit-preview',
      publish: '#edit-submit',
      selectIcon: '.fip-icons-container > span:nth-child(6)'

    },
    commands: [
      {
        Dashboard: function(){
        return client.url("https://stage.erefer.org/dashboard").pause(1000);
        },

        Verify_Manage_Organisation_Elements: function () {
            return this
            .click('@addNewServiceType')
            .pause(1000)
            //.assert.visible('@addNewServiceType', 'Add New Service Type is displayed')
            //.assert.visible('@Title', 'Manage Service Types is displayed')             
        },

        Verify_Add_Organisation_Elements: function () {
            this.click('@addNewServiceTypeBtn')
            this.pause(3000)
            // this.assert.visible('@ServiceTypeTitle', 'Add Service Type is displayed')
            // this.assert.visible('@nameOfServiceType', 'Name field is displayed')
            // client.execute('window.scrollTo(0,document.body.scrollHeight);');
            // this.assert.visible('@iconPicker', 'Icon Picker is displayed')
            // this.assert.visible('@colorPicker', 'Color Picker is displayed')
            // this.assert.visible('@draft', 'Save As Draft button is displayed')
            // this.assert.visible('@publish', 'Publish button is displayed')
            return this

        },

        Enter_Name_Of_Organisation: function (NAME) {
            return this
            .setValue('@nameOfServiceType',NAME)
            .pause(1000)
        },

        Select_From_Icon_Picker: function () {  
            client.execute('window.scrollTo(0,document.body.scrollHeight);');
            this.click('@selectIcon')
            this.pause(1000)
            return this
        },

        Select_From_Color_Picker: function () {
            this.click('@selectColor')
            this.pause(1000)
            return this
        },

        Click_Publish: function () {
            this.click('@publish')
            this.pause(5000)
            return this
        },

        

    
    }],
}
