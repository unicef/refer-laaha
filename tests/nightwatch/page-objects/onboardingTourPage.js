const { client } = require("nightwatch-api");

module.exports = {
  
  elements: {
    addTourBTN: {
        selector: '//*[contains(text(), "Add tour")]',
        locateStrategy: 'xpath'
    },
    Addtip: {
      selector: '//*[contains(text(), "Add tip")]',
      locateStrategy: 'xpath'
    },
    addLabel: {
      selector: '//*[@name="label"]',
      locateStrategy: 'xpath'
    },
    verifyRoute: {
      selector: '//*[contains(text(),"entity.node.canonical - node:91")]',
      locatestrategy: 'xpath'
    },
    tourName: '#edit-label',
    moduleName: '#edit-module',
    routes: '#edit-routes',
    save:'#edit-submit',
    findRoute: '#edit-find-routes',
    language: '#edit-langcode',
    selectText: '#edit-new--2',
    Addbtn: '#edit-add',
    selector: '#edit-selector',
    position: '#edit-position>option:nth-child(1)',
    Body: '#edit-body'
  },
  commands: [
    {
      Navigate_To_Tour_Screen: function(){
      return client.url("https://stage.ec.virtualsafespace.net/admin/config/user-interface/tour").pause(1000);
      },

      Verify_Click_Add_Tour_Button: function() {
          return this
          .assert.visible('@addTourBTN', 'Button is present')
          .click('@addTourBTN')
          .pause(3000)
      },

      Verify_Add_tour_Screen: function() {
          return this
          //.waitForElementPresent('@addTourBTN')
          //.assert.visible('@addTourBTN', 'Add Tour is present')
          .assert.visible('@tourName', 'Tour Name is present')
          .assert.visible('@routes', 'Route is present')
          .assert.visible('@save', 'Save is present')
          .assert.visible('@findRoute', 'Find Route is present')
          .assert.visible('@language', 'Find Route is present')
      },

        Generate_random_String: function () {
           var candidates = "ABCDEFGHIJKLMNOPQRSTUVWXY123456789";
           var randomstring = "",
           random;
           for(var i=0; i<1; i++){
               for (var j=0; j<8; j++){
                   random = Math.floor(Math.random()*candidates.length);
                   randomstring += candidates.charAt(random);
               }           
              }
              return randomstring;
        },

      Add_Tour_Name: function() {
          return this
          .click('@tourName')
          .setValue('@tourName', this.Generate_random_String())
        //   .setValue('@tourName', name)
          .pause(1000)
      },

      Add_Module_Name: function(mod) {
        return this
        .click('@moduleName')
        .setValue('@moduleName', mod)
        .pause(1000)
    },

    Add_Routes: function(route) {
        return this
        .click('@routes')
        .setValue('@routes', route)
        .pause(1000)
    },

    Save_Tour: function() {
        return this
        .click('@save')
        .pause(1000)
    },

    SelectText: function() {
      client.execute('window.scrollTo(0,document.body.scrollHeight);');
      this.click('@selectText')
      this.pause(1000)
      this.waitForElementPresent('#edit-new--2 > option:nth-child(2)')
      this.click('#edit-new--2 > option:nth-child(2)')
      this.pause(1000)
      return this;
    },

    Click_On_Add_Button: function() {
      return this
      .click('@Addbtn')
      .pause(3000)
      //.assert.visible('@Addtip', 'Add Tip is present')
    },

    Add_Label: function() {
      return this
      .click('@addLabel')
      .setValue('@addLabel', this.Generate_random_String())
      .pause(1000)
    },

    Add_Selector: function(selector) {
      return this
      .click('@selector')
      .setValue('@selector', selector)
      .pause(1000)
    },

    Add_Position: function() {
      return this
      .assert.visible('@position', 'Position is Auto')
    },

    Body: function(body) {
      return this
      .click('@Body')
      .setValue('@Body', body)
    },

    Verify_Node: function() {
      return client.url("https://stage.ec.virtualsafespace.net/node/91?tour").pause(10000);
    },

    Delete_created_Tour: function() {
       
       client.execute('window.scrollTo(0,document.body.scrollHeight);');
       this.assert.visible('@verifyRoute', 'Node is present')
       this.click('xpath','//*[contains(text(),"entity.node.canonical - node:91")]//ancestor::tr//td[5]/div/div/ul/li[2]/button')
       ////*[contains(text(),"entity.node.canonical - node:91")]//ancestor::tr//td[5]/div
       this.pause(1000)
       this.click('xpath','//*[contains(text(),"entity.node.canonical - node:91")]//ancestor::tr//td[5]/div/div/ul/li[4]/a')
       this.pause(1000)
       this.click('@save')
       this.pause(1000)
       return this
       
    },


    }],
}