var articleData = require('../Data/components.json');
const { client } = require("nightwatch-api");

module.exports = {
  
  elements: {
      artcileDraftStatus: {
         selector:'//*[@id="edit-moderation-state-0-state"]/option[@value="draft"]',
         locateStrategy: 'xpath'
      },
      artcilePublishedStatus: {
        selector:'//*[@id="edit-moderation-state-0-state"]/option[@value="published"]',
        locateStrategy: 'xpath'
     },
     artcileArchivedStatus: {
        selector:'//*[@id="edit-moderation-state-0-state"]/option[@value="archived"]',
        locateStrategy: 'xpath'
     },
     artcileReqForUnPublishStatus: {
        selector:'//*[@id="edit-moderation-state-0-state"]/option[@value="request_for_un_publish"]',
        locateStrategy: 'xpath'
     },
     artcileUnPublishStatus: {
        selector:'//*[@id="edit-moderation-state-0-state"]/option[@value="un_publish"]',
        locateStrategy: 'xpath'
     },
     artcileReqToDeleteStatus: {
        selector:'//*[@id="edit-moderation-state-0-state"]/option[@value="request_to_delete"]',
        locateStrategy: 'xpath'
     },
     articleEdit: {
        selector:'//*[@href="/node/681/edit"]',
        locateStrategy: 'xpath'
     },
     contentStatusonFE: {
        selector:'//*[@aria-label="Status message"]',
        locateStrategy: 'xpath'
     },
      modState: '#edit-moderation-state-0-state',
      savebtn: '#edit-submit'
    },
    commands: [
      {
        content_navigation: function(){
        return client.url("https://stage.ec.virtualsafespace.net/welcome-unicef").pause(1000);
        },

        Click_On_Article_Edit: function() {
        //   client.execute('window.scrollTo(0,document.body.scrollHeight);');
          return this
          .click('@articleEdit')
          .pause(2000)
        },
        VERIFY_ARTICLE_STATUS: function (articleStatus) {
          try {
              this
                  .waitForElementPresent('@modState')
                  .click('@modState')
                  .pause(1000)
              if (articleStatus == articleData.articleDraftDropdownStatus) {
                  this
                      .waitForElementPresent('@artcileDraftStatus')
                      .getText('@artcileDraftStatus', function (results) {
                          const val = results.value
                          this.assert.equal(articleData.articleDraftDropdownStatus, val)
                      })
                      .click('@artcileDraftStatus')
              } else if (articleStatus == articleData.articlePublishedDropdownStatus) {
                  this
                      .waitForElementPresent('@artcilePublishedStatus')
                      .getText('@artcilePublishedStatus', function (results) {
                          const val = results.value
                          this.assert.equal(articleData.articlePublishedDropdownStatus, val)
                      })
                      .click('@artcilePublishedStatus')
              }
              else if (articleStatus == articleData.articleArchivedDropdownStatus) {
                  this
                      .waitForElementPresent('@artcileArchivedStatus')
                      .getText('@artcileArchivedStatus', function (results) {
                          const val = results.value
                          this.assert.equal(articleData.articleArchivedDropdownStatus, val)
                      })
                      .click('@artcileArchivedStatus')
              }
              else if (articleStatus == articleData.articleReqForUnPublishStatus) {
                  this
                      .waitForElementPresent('@artcileReqForUnPublishStatus')
                      .getText('@artcileReqForUnPublishStatus', function (results) {
                          const val = results.value
                          this.assert.equal(articleData.articleReqForUnPublishStatus, val)
                      })
                      .click('@artcileReqForUnPublishStatus')
              }
              else if (articleStatus == articleData.articleUnPublishStatus) {
                  this
                      .waitForElementPresent('@artcileUnPublishStatus')
                      .getText('@artcileUnPublishStatus', function (results) {
                          const val = results.value
                          this.assert.equal(articleData.articleUnPublishStatus, val)
                      })
                      .click('@artcileUnPublishStatus')
              }
              else if (articleStatus == articleData.articleUnPublishStatus) {
                this
                    .waitForElementPresent('@artcileReqToDeleteStatus')
                    .getText('@artcileReqToDeleteStatus', function (results) {
                        const val = results.value
                        this.assert.equal(articleData.articleUnPublishStatus, val)
                    })
                    .click('@artcileReqToDeleteStatus')
            }
              this.pause(1000)
              return this
          }
          catch (err) {
              console.log('Error is : >>>>>>>>>   ', err)
          }
      },
      save: function() {
          return this
          .click('@savebtn')
          .pause(3000)

      },

      CONTENT_VISIBLE_ON_FE: function (browser) {
        try {
            var statusOnFE;
            this
                .getText('@contentStatusonFE', function (actualStatus) {
                statusOnFE = actualStatus.value;
            if (statusOnFE == "Published") {
                this 
                console.log("Status of Article on FE is >>>>>>>>>>>>>>" + statusOnFE)
                .api.expect.element('@articleTitleOnFE').text.to.equal(articleData.articleTitleOnFE)
            }
             if (statusOnFE == "Draft") {
                this
                console.log("Status of Article on FE is >>>>>>>>>>>>>>" + statusOnFE)
                .api.expect.not.element('@articleTitleOnFE').text.to.equal(articleData.articleTitleOnFE)
            }
            else if (statusOnFE == "Unpublished") {
                this
                console.log("Status of Article on FE is >>>>>>>>>>>>>>" + statusOnFE)
                .api.expect.not.element('@articleTitleOnFE').text.to.equal(articleData.articleTitleOnFE)        
            }
            else if (statusOnFE == "Scheduled") {
                this
                console.log("Status of Article on FE is >>>>>>>>>>>>>>" + statusOnFE)
                .api.expect.element('@articleTitleOnFE').text.to.equal(articleData.articleTitleOnFE)    
            }
            }) 
        return this
}
catch (err) {
    console.log('Error is : >>>>>>>>>   ', err)
}
},



    }]
}