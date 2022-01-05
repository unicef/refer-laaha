var articleData = require('../Data/components.json');
const { client } = require("nightwatch-api");

module.exports = {

elements: {

  ExitWebsiteBTN: {
    selector: 
         '//*[@class="exit-website-btn button-defult-exit"]',
         locateStrategy: 'xpath',
  },
  VSSTitle: {
    selector:
    '//*[@class="name navbar-brand"]',
    locateStrategy: 'xpath',
  },
  SiteNavMenu: {
    selector:
    '//li[@class="edit dropbutton-action"]/a[@href="/admin/structure/menu/manage/navigation-header-menu?destination=/admin/structure/menu"]',
    locateStrategy: 'xpath',
  },
  AddLinkBtn: {
    selector:
    '//a[@href="/admin/structure/menu/manage/navigation-header-menu/add?destination=/admin/structure/menu"]',
    locateStrategy: 'xpath',
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
LanguageSwitcherContainer: {
  selector:
  '//*[@class="language-switcher-language-url block block-language block-language-blocklanguage-interface clearfix"]',
  locateStrategy: 'xpath'
},
VSSlogo: {
  selector:
  '//*[@class="logo navbar-btn pull-left"]',
  locateStrategy: 'xpath',
},
langSwitch: {
  selector:
  '//*[@class="links dropdown-menu"]',
  locateStrategy: 'xpath',
},
DiscoverMenu: {
  selector:
   '//*[contains(text(), "DISCOVER")]',
   locateStrategy: 'xpath',
},
FAQS: {
  selector:
  '//*[contains(text(), "FAQS")]',
  locateStrategy: 'xpath',
},
Get_In_Touch: {
  selector:
  '//*[contains(text(), "GET IN TOUCH")]',
  locateStrategy: 'xpath',
},
ResourceLibrary: {
  selector:
  '//*[contains(text(), "RESOURCE LIBRARY")]',
  locateStrategy: 'xpath',
},
editDelete: {
  selector:
  '//*[@class="button button--danger"]',
  locateStrategy:'xpath'
},


    
  },
  commands: [
{
      Verify_Exit_Website: function(){
         return this
         .waitForElementPresent('@ExitWebsiteBTN')
         .assert.visible('@ExitWebsiteBTN', 'Exit Website Button is displayed')
      },

      Click_Exit_Website_Button: function(){
        return this
        .click('@ExitWebsiteBTN')
        .pause(1000)
      },

      Verify_VSS_Title: function(){
        return this
        .assert.visible('@VSSTitle', 'Virtual Safe Space is displayed')
        .click('@VSSTitle')
        .pause(1000)
      },

      Verify_VSS_Logo: function(){
        return this
        .assert.visible('@VSSlogo', 'VSS Logo is displayed')
      },

      Verify_Header_Site_Nav: function(){
        try {
           return this
            
             .waitForElementPresent('@SiteNavMenu')
             .assert.visible('@SiteNavMenu')
             .click('@SiteNavMenu')
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

            Verify_Language_Switcher: function(){
                 return this
                 .assert.visible('@langSwitch', 'Language Switcher is displayed')
            },
            Verify_Discover_Nav_Menu_Terms: function(){
              return this
              .assert.visible('@DiscoverMenu', 'Discover Menu is displayed')
            },

            Verify_FAQs_menu: function(){
              return this
              .assert.visible('@FAQS', 'FAQs is displayed')
            },

            Verify_GIT_menu: function(){
              return this
              .assert.visible('@Get_In_Touch', 'Get In Touch menu is displayed')
            },

            Verify_ResourceLibrary_menu: function(){
              return this
              .assert.visible('@ResourceLibrary', 'Resource Library menu is displayed')
            },

            Delete_Created_Header_Menu_Item: function(){
              try {
          
                       this.assert.visible('@SiteNavMenu')
                       this.click('@SiteNavMenu')
                       this.pause(1000)
                       this.click('xpath','//td/a[contains(text(), "Automation Link Added")]//ancestor::tr/td[4]/div/div/ul/li/a')
                       client.execute('window.scrollTo(0,document.body.scrollHeight);');
                       this.pause(1000)
                       this.click('@editDelete')
                       this.pause(1000)
                       this.click('xpath','//input[@id="edit-submit"]')
                       this.pause(1000)
                       return this
          }
          catch (err) {
              console.log('Error is : >>>>>>>>>   ', err)
          }
          },

    }]
}