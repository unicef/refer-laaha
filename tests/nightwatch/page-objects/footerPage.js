var articleData = require('../Data/components.json');
const { client } = require("nightwatch-api");

module.exports = {
//   url: function () {
//     return `${process.env.VSS_IQ_URL}`;
// },
elements: {

    QuickLink: {
        selector:
        '//li[@class="edit dropbutton-action"]/a[@href="/admin/structure/menu/manage/quick-links?destination=/admin/structure/menu"]',
        locateStrategy: 'xpath'
    },
    verifyQuickLink: {
        selector:
          '//input[@data-drupal-selector="edit-label"]',
        locateStrategy: 'xpath'
      },
      AddLinkBtn: {
          selector:
          '//a[@href="/admin/structure/menu/manage/quick-links/add?destination=/admin/structure/menu"]',
          locateStrategy: 'xpath'
      },
      VerifyAddMenuLinkTitle: {
          selector:
          '//*[@class="js-quickedit-page-title page-title"]',
          locateStrategy: 'xpath'
      },
      AddDataInlinkfield: {
          selector:
          '//input[@data-drupal-selector="edit-link-0-uri"]',
          locateStrategy: 'xpath'
      },
      MenulinkTitle: {
          selector:
          '//input[@data-drupal-selector="edit-title-0-value"]',
          locateStrategy: 'xpath'
      },
      ClickSave: {
          selector:
          '//input[@data-drupal-selector="edit-submit"]',
          locateStrategy: 'xpath'
      },
      EditQuickLink: {
          selector:
          '//*[@href="/admin/structure/menu/item/246/edit?destination=/admin/structure/menu/manage/quick-links"]',
          locateStrategy: 'xpath'
      },
      createdmenu: {
          selector:
          '//*[contains(text(), "Automation Link Added")]',
          locateStrategy: 'xpath'
      }

  },
  commands: [
    {
        Navigate_To_Menu_List: function(){
         return client.url("https://stage.ec.virtualsafespace.net/admin/structure/menu").pause(1000);
        },
        
      Verify_Footer_Quick_Links: function(){
      try {
         return this
          
           .waitForElementPresent('@QuickLink')
           .assert.visible('@QuickLink')
           .click('@QuickLink')
           .pause(1000)
           .waitForElementPresent('@AddLinkBtn')
           .assert.visible('@AddLinkBtn')
           .click('@AddLinkBtn')
           .pause(1000)
          }
          catch (err) {
              console.log('Error is : >>>>>>>>>   ', err)
          }
      },
      Add_Menu_Link: function(link){
        try {
           return this
            
             .waitForElementPresent('@VerifyAddMenuLinkTitle')
             .assert.visible('@VerifyAddMenuLinkTitle')
             .assert.visible('@AddDataInlinkfield')
             .setValue('@AddDataInlinkfield', link)
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },
        Add_Menu_Title: function(Title){
            try {
               return this
                
                 .waitForElementPresent('@VerifyAddMenuLinkTitle')
                 .assert.visible('@VerifyAddMenuLinkTitle')
                 .assert.visible('@MenulinkTitle')            
                 .setValue('@MenulinkTitle', Title)
                 .pause(1000)
                }
                catch (err) {
                    console.log('Error is : >>>>>>>>>   ', err)
                }
            },

            Click_Save: function(){
                try {
                    return this
                   .click('@ClickSave')
                   .pause(1000)
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },
    
       Edit_Quick_Link_Menu_Title: function(editedTitle){
        try {
            return this
                 .waitForElementPresent('@EditQuickLink')
                 .assert.visible('@EditQuickLink')
                 .click('@EditQuickLink')
                 .pause(1000)
                 .waitForElementPresent('@VerifyAddMenuLinkTitle')
                 .assert.visible('@VerifyAddMenuLinkTitle')
                 .assert.visible('@MenulinkTitle')            
                 .setValue('@MenulinkTitle', editedTitle)
                 .pause(1000)
    }
    catch (err) {
        console.log('Error is : >>>>>>>>>   ', err)
    }
},
    Delete_Created_Menu_Item: function(){
    try {
        return this
             .waitForElementPresent('@EditQuickLink')
             .assert.visible('@EditQuickLink')
             .click('@EditQuickLink')
             .pause(1000)
             .waitForElementPresent('@VerifyAddMenuLinkTitle')
             .assert.visible('@VerifyAddMenuLinkTitle')
             .assert.visible('@MenulinkTitle')            
             .setValue('@MenulinkTitle', editedTitle)
             .pause(1000)
}
catch (err) {
    console.log('Error is : >>>>>>>>>   ', err)
}
},
//       Save_edited_menu: function(){
//        try{
//         return this
//         .click(MenulinkTitle)
//     }
//       catch (err) {
//         console.log('Error is : >>>>>>>>>   ', err)
//     }
// }


}],
}
