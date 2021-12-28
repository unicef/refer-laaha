var articleData = require('../Data/components.json');
var counts = 0;
module.exports = {


    // console.log('THis is artcile',test.artcileName);
    url: function () {
        return `${process.env.CMS_URL}`;
    },
    elements: {
        articleResumeEditingButton: '[class="autosave-form-resume-button ui-button ui-corner-all ui-widget button"]',
        articleDiscardButton: '[class="autosave-form-reject-button ui-button ui-corner-all ui-widget button"]',
        articleHeadlineInputBox: '[name="title[0][value]"]',
        articleBriefLabel: '[aria-controls="edit-group-brief"]',
        articleBriefTextArea: '#cke_2_contents > iframe',
        articleBriefTextInputArea: '[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]>P',
        artcileHeroImageButton: '[id="edit-field-hero-media-entity-browser-entity-browser-open-modal"]',
        artcileHeroImageonEditArticle: '.image-style-thumbnail',
        articleContentTextArea: {
            selector: '//*[@id="cke_2_contents"]/iframe',
            locateStrategy: 'xpath'
        },
        textFormatLabel: '[for="edit-field-content-0-subform-field-body-0-format--2"]',
        textFormatSelect: '#edit-field-content-0-subform-field-body-0-format--2',
        articleCategoryInputBox: '[id="autocomplete-deluxe-input--3"]',
        articleSelectedCategoryOnEditArticle: '#edit-field-category-wrapper .autocomplete-deluxe-item',
        articleSelectedCategoryCloseButtonOnEditArticle: '#edit-field-category-wrapper .autocomplete-deluxe-item-delete',
        artcileSelectImageIframe: '#entity_browser_iframe_image_browser',
        aticleSelectImageSubmitButton: '//*[@id="edit-submit"]',
        list: {
            selector: '//*[@id="ui-id-4"]/li',
            locateStrategy: 'xpath'
        },
        generateKeywordButton: '[value="Generate Keywords"]',
        selectStatusDropDown: '#edit-moderation-state-0-state',
        saveButton: '#edit-submit--2',
        articleSuccessMessage: {
            selector: '//ul[@class="status__list"]/li',
            locateStrategy: 'xpath'
        },
        articleSuccessMessageForScheduled: {
            selector: '//ul[@class="status__list"]/li[2]',
            locateStrategy: 'xpath'
        },
        topicsInputBox: '#edit-field-topics-target-id',
        articleSelectedTopicCloseButtonOnEditArticle: '#edit-field-article-flag-wrapper .autocomplete-deluxe-item-delete',
        topicsListItem: '.ui-menu-item-wrapper',
        articleFlagInputBox: '#autocomplete-deluxe-input',
        articleFlagFirstItem: {
            selector: '//ul[4]/li[1]/a[@class="ui-menu-item-wrapper"]',
            locateStrategy: 'xpath'
        },
        articleSelectedFlagOnEditArticle: '#edit-field-article-flag-wrapper .autocomplete-deluxe-item',
        articleBylineInputBox: '#autocomplete-deluxe-input--2',
        articleSelectedByLineCLoseButtonOnEditArticle: '#edit-field-author-wrapper .autocomplete-deluxe-item-delete',
        articleBylineFirstItem: {
            selector: '//ul[5]/li[1]/a[@class="ui-menu-item-wrapper"]',
            locateStrategy: 'xpath'
        },
        articleSelectedByLineOnEditArticle: '#edit-field-author-wrapper .autocomplete-deluxe-item',
        artcileNeedsReviewStatus: {
            selector: '//*[@id="edit-moderation-state-0-state"]/option[@value="needs_review"]',
            locateStrategy: 'xpath'
        },
        artcilePublishedStatus: {
            selector: '//*[@id="edit-moderation-state-0-state"]/option[@value="published"]',
            locateStrategy: 'xpath'
        },
        artcileDraftStatus: {
            selector: '//*[@id="edit-moderation-state-0-state"]/option[@value="draft"]',
            locateStrategy: 'xpath'
        },
        artcileVettedStatus: {
            selector: '//*[@id="edit-moderation-state-0-state"]/option[@value="vetted"]',
            locateStrategy: 'xpath'
        },
        artcileExpiredStatus: {
            selector: '//*[@id="edit-moderation-state-0-state"]/option[@value="expired"]',
            locateStrategy: 'xpath'
        },
        artcileScheduledStatus: {
            selector: '//*[@id="edit-moderation-state-0-state"]/option[@value="scheduled"]',
            locateStrategy: 'xpath'
        },
        articleSelectedStatusOnEditArticle: {
            selector: '#edit-moderation-state-0-current',
            locateStrategy: 'css selector'
        },
        articleEditButton: {
            selector: '//*[@id="block-mc-core-theme-tabs"]/ul/li[2]/a',
            locateStrategy: 'xpath'
        },
        articleDeleteButton: {
            selector: '//*[@id="block-mc-core-theme-tabs"]/ul/li[3]/a',
            locateStrategy: 'xpath'
        },
        artcileConfirmDelete: '#edit-submit',
        artcileStatusDropDownListItems: {
            selector: '//*[@id="edit-moderation-state-0-state"]/option',
            locateStrategy: 'xpath'
        },
        articleUpdateSuccessMessage: '.messages__content',
        articleSchedulingOptions: '[aria-controls="edit-scheduler-settings"]',
        articlePublishOnDate: '#edit-publish-on-0-value-date',
        articlePublishOnDateCloseButton: {
            selector: '//*[@id="edit-publish-on-0-value"]/div[1]/span',
            locateStrategy: 'xpath'
        },
        articlePublishOnTime: '#edit-publish-on-0-value-time',
        articlePublishOnTimeCloseButton: {
            selector: '//*[@id="edit-publish-on-0-value"]/div[2]/span',
            locateStrategy: 'xpath'
        },
        articleUnPublishOnDate: '#edit-unpublish-on-0-value-date',
        articleUnPublishOnDateCloseButton: {
            selector: '//*[@id="edit-unpublish-on-0-value"]/div[1]/span',
            locateStrategy: 'xpath'
        },
        articleUnPublishOnTime: '#edit-unpublish-on-0-value-time',
        articleUnPublishOnTimeCloseButton: {
            selector: '//*[@id="edit-unpublish-on-0-value"]/div[2]/span',
            locateStrategy: 'xpath'
        },
        articleMetaTags: '[aria-controls="edit-field-meta-tags-0"]',
        articleReleaseDate: '#edit-field-release-date-0-value-date',
        articleReleaseDateCloseButton: {
            selector: '//*[@id="edit-field-release-date-0-value"]/div[1]/span',
            locateStrategy: 'xpath'
        },
        articleReleaseTime: '#edit-field-release-date-0-value-time',
        articleReleaseTimeCloseButton: {
            selector: '//*[@id="edit-field-release-date-0-value"]/div[2]/span',
            locateStrategy: 'xpath'
        },
        artcileEmbargoCheckBox: '#edit-field-embargo-value',
        articleEmbargoDate: '#edit-field-embargo-date-0-value-date',
        articleEmbargoDateCloseButton: {
            selector: '//*[@id="edit-field-embargo-date-0-value"]/div[1]/span',
            locateStrategy: 'xpath'
        },
        articleEmbargoTime: '#edit-field-embargo-date-0-value-time',
        articleEmbargoTimeCloseButton: {
            selector: '//*[@id="edit-field-embargo-date-0-value"]/div[2]/span',
            locateStrategy: 'xpath'
        },
        articleContentBody: '[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]',
        articleSaveAndEdit: '#edit-save-edit--2',
        articleCreateSuccessMessage: {
            selector: '//ul[@class="messages__list"]/li',
            locateStrategy: 'xpath'
        },
        breaklockLink: {
            selector: '//a[contains(text(), "Break lock")]',
            locateStrategy: 'xpath'
        },
        confirmBreakLockButton: '#edit-submit',
        unlockButton: '#edit-unlock--2',
        lockBrokenMessage: {
            selector: '//div[@class="messages__content"]',
            locateStrategy: 'xpath'
        },
        resumeOrdiscardMsg: '[class="ui-dialog-content ui-widget-content"]>div',
        revisionsTab: {
            selector: '//*[@id="block-mc-admin-theme-primary-local-tasks"]/nav/ul/li[4]/a',
            locateStrategy: 'xpath'
        },
        editTab: {
            selector: '//*[@id="block-mc-admin-theme-primary-local-tasks"]/nav/ul/li[2]/a',
            locateStrategy: 'xpath'
        },
        contentLockWarningMsg: {
            selector: '//ul[@class="messages__list"]/li',
            locateStrategy: 'xpath'
        },
        articleDeleteMessage: {
            selector: '//ul[@class="status__list"]/li',
            locateStrategy: 'xpath'
        }




    },

    commands: [{

        Navigate_to_Article_Page: function () {

        return client.url("https://stage.ec.virtualsafespace.net/node/add/article").pause(1000);

        },



        ENTER_ARTICLE_HEADLINE: function (articleHeadline) {
            try {
                return this
                    .assert.visible('@articleHeadlineInputBox')
                    .pause(1000)
                    .clearValue('@articleHeadlineInputBox')
                    .setValue('@articleHeadlineInputBox', articleHeadline)
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },


        CLICK_ON_DISCARD_BUTTON: function () {
            try {
                return this.api.element('@articleDiscardButton', results => {
                    if (results.status > -1) {
                        this
                            .pause(1000).click('@articleDiscardButton')
                        console.log('Element is present')
                        // do this
                    } else {
                        console.log('Element is not present')
                    }
                })
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        ENTER_BRIEF_DATA: function (articleBrief) {
            try {
                this.assert.visible('@articleBriefLabel')
                    .click('@articleBriefLabel')
                    .pause(1000)
                    //.waitForElementPresent('@articleBriefTextArea')
                    .api.frame(0)
                    //.click('[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]')
                    // console.log('this is clicked');
                    //this.api.pause(3000)
                    .assert.visible('[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]')
                    .waitForElementPresent('[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]')
                    .clearValue('[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]')
                    .setValue('[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]', articleBrief)
                    .frame(null)
                //.pause(1000)
                //.click('@articleBriefLabel')
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        UPLOAD_HERO_IMAGE: function () {
            try {
                this
                    .assert.visible('@artcileHeroImageButton')
                    .click('@artcileHeroImageButton')
                    .pause(3000)
                    .assert.visible('@artcileSelectImageIframe')
                    .api.frame(3)

                    //.setValue('#edit-name','Pramod')
                    //.click('xpath','//*[@id="edit-submit"]')
                    // .pause(3000)
                    .waitForElementPresent('xpath', '//*[@id="entity-browser-image-browser-form"]/div[1]/div[2]/div/div[1]')
                    .click('xpath', '//*[@id="entity-browser-image-browser-form"]/div[1]/div[2]/div/div[1]')
                    // .waitForElementPresent('#edit-submit')
                    .click('#edit-submit')
                    .pause(1000)
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        ENTER_DATA_IN_CONTENT_TEXT: function (articleContentText) {
            try {
                this.
                    waitForElementPresent('@generateKeywordButton')
                    .waitForElementPresent('@articleContentTextArea')
                    .api.frame(2)
                    //.pause(3000)
                    .click('[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]')
                    .clearValue('[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]')
                    .setValue('[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]', articleContentText)
                    .frame(null)
                //.pause(3000)
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },




        SELECT_ARTICLE_CATEGORY: function (articleCategory) {
            try {
                this
                    .waitForElementPresent('@articleCategoryInputBox')
                    .click('@articleCategoryInputBox')
                    .setValue('@articleCategoryInputBox', articleCategory)
                    .pause(1000)
                this.api.elements('css selector', '[class="autocomplete-deluxe-highlight-char"]', function (result) {
                    // console.log('aaaaaa ----->>> ', result.value.length);
                    for (var i = 1; i <= result.value.length; i++) {
                        if (i <= result.value.length) {
                            this
                                .getText('xpath', '//li[' + i + ']/a[@class="ui-menu-item-wrapper"]', function (results) {
                                    const val = results.value;
                                    //console.log('value is >>', val)
                                    if (val == articleCategory) {
                                        //  console.log('Matched')
                                        this
                                            .waitForElementPresent('xpath', '//li[1]/a[@class="ui-menu-item-wrapper"]')
                                            .click('xpath', '//li[1]/a[@class="ui-menu-item-wrapper"]')
                                        // .pause(3000)

                                    }
                                })
                        }
                    }
                }

                );

                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        SELECT_NEEDS_REVIEW_STATUS: function () {
            try {
                this
                    .waitForElementPresent('@selectStatusDropDown')
                    .click('@selectStatusDropDown')
                    //.pause(1000)
                    //.waitForElementPresent('#edit-moderation-state-0-state > option:nth-child(4)')
                    //.click('#edit-moderation-state-0-state > option:nth-child(4)')
                    .waitForElementPresent('@artcileNeedsReviewStatus')
                    .click('@artcileNeedsReviewStatus')
                //.pause(3000)
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        CLICK_ON_SAVE_BUTTON: function () {
            try {
                this
                    .waitForElementPresent('@saveButton')
                    .click('@saveButton')
                    .pause(2000)
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },


        VERIFY_ARTCILE_CREATED_SUCCESS_MESSAGE: function (expectedArticleHeadline) {
            try {
                var successMsg = 'Article ' + expectedArticleHeadline + ' has been created.'
                this
                    //.waitForElementPresent('xpath','@artcleCreateSuccessMessage')
                    .getText('@articleCreateSuccessMessage', function (results) {
                        const val = results.value;
                        //console.log('Success message is >>>>>>>>',val)
                        this.assert.equal(val, successMsg);
                    })
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        VERIFY_ARTCILE_UPDATED_SUCCESS_MESSAGE: function (articleHeadline, articleType) {
            try {
                var successMsg = 'Article ' + articleHeadline + ' has been updated.'
                if (articleType == 'No') {
                    this
                        //.waitForElementPresent('xpath','@artcleCreateSuccessMessage')
                        .getText('@articleSuccessMessage', function (results) {
                            const val = results.value;
                            // console.log('Success message is >>>>>>>>', val)
                            this.assert.equal(val, successMsg);
                        })
                } else if (articleType == 'Yes') {
                    this
                        //.waitForElementPresent('xpath','@artcleCreateSuccessMessage')
                        .getText('@articleSuccessMessageForScheduled', function (results) {
                            const val = results.value;
                            //   console.log('Success message is >>>>>>>>', val)
                            this.assert.equal(val, successMsg);
                        })
                }
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },


        SELECT_TOPICS: function (articleTopic) {
            try {
                this
                    .waitForElementPresent('@topicsInputBox')
                    .setValue('@topicsInputBox', articleTopic)
                    .pause(1000)
                    .waitForElementPresent('@topicsListItem')
                    .getText('@topicsListItem', function (results) {
                        const val = results.value
                        //console.log('Topic value is >>>>>>>',val)
                        this.assert.equal(articleTopic, val)
                    })
                    .click('@topicsListItem')
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        SELECT_ARTICLE_FLAG: function (articleFlag) {
            try {
                this
                    .waitForElementPresent('@articleFlagInputBox')
                    .setValue('@articleFlagInputBox', articleFlag)
                    .pause(1000)
                    .waitForElementPresent('@articleFlagFirstItem')

                    .getText('@articleFlagFirstItem', function (results) {
                        const val = results.value
                        //console.log('Article Flag value is >>>>>>>',val)
                        this.assert.equal(articleFlag, val)
                    })
                    .click('@articleFlagFirstItem')
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },



        SELECT_ARTICLE_BYLINE: function (articleByline) {
            try {
                this
                    .waitForElementPresent('@articleBylineInputBox')
                    .setValue('@articleBylineInputBox', articleByline)
                    .pause(1000)
                    .waitForElementPresent('@articleBylineFirstItem')
                    .getText('@articleBylineFirstItem', function (results) {
                        const val = results.value
                        // console.log('Article ByLine value is >>>>>>>',val)
                        this.assert.equal(articleByline, val)
                    })
                    .click('@articleBylineFirstItem')
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        VERIFY_STATUS_COUNT_ON_ARTICLE: function (articleType) {
            try {
                this
                    .waitForElementPresent('@artcileStatusDropDownListItems')
                if (articleType == "New") {
                    this
                        .api.elements('@artcileStatusDropDownListItems', function (result) {
                            this.assert.equal(articleData.StatusCountInDropdownForNewArticle, result.value.length)
                        })
                }
                else if (articleType == "Composer User") {
                    this
                        .api.elements('@artcileStatusDropDownListItems', function (result) {
                            this.assert.equal(articleData.StatusCountInDropdownForNewArticleComposerUserRole, result.value.length)
                        })
                }
                else if (articleType == "Journalist User") {
                    this
                        .api.elements('@artcileStatusDropDownListItems', function (result) {
                            this.assert.equal(articleData.StatusCountInDropdownForNewArticleJournalistrUserRole, result.value.length)
                        })
                }
                else if (articleType == "Needs Review") {
                    this
                        .api.elements('@artcileStatusDropDownListItems', function (result) {
                            this.assert.equal(articleData.StatusCountInDropdownForNeedsReviewArticle, result.value.length)
                        })
                }
                else if (articleType == "Published") {
                    this
                        .api.elements('@artcileStatusDropDownListItems', function (result) {
                            this.assert.equal(articleData.StatusCountInDropdownForPublishedArticle, result.value.length)
                        })
                }
                else if (articleType == "Expired") {
                    this
                        .api.elements('@artcileStatusDropDownListItems', function (result) {
                            this.assert.equal(articleData.StatusCountInDropdownForExpiredArticle, result.value.length)
                        })
                }
                else if (articleType == "Draft") {
                    this
                        .api.elements('@artcileStatusDropDownListItems', function (result) {
                            this.assert.equal(articleData.StatusCountInDropdownForDraftArticle, result.value.length)
                        })
                }
                else if (articleType == "Vetted") {
                    this
                        .api.elements('@artcileStatusDropDownListItems', function (result) {
                            this.assert.equal(articleData.StatusCountInDropdownForVettedArticle, result.value.length)
                        })
                }
                else if (articleType == "Scheduled") {
                    this
                        .api.elements('@artcileStatusDropDownListItems', function (result) {
                            this.assert.equal(articleData.StatusCountInDropdownForScheduledArticle, result.value.length)
                        })
                }
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },



        VERIFY_ARTICLE_STATUS: function (articleStatus) {
            try {
                this
                    .waitForElementPresent('@selectStatusDropDown')
                    .click('@selectStatusDropDown')
                    .pause(1000)
                if (articleStatus == articleData.articleNeedsReviewDropdownStatus) {
                    this
                        .waitForElementPresent('@artcileNeedsReviewStatus')
                        .getText('@artcileNeedsReviewStatus', function (results) {
                            const val = results.value
                            this.assert.equal(articleData.articleNeedsReviewDropdownStatus, val)
                        })
                        .click('@artcileNeedsReviewStatus')
                } else if (articleStatus == articleData.articlePublishedDropdownStatus) {
                    this
                        .waitForElementPresent('@artcilePublishedStatus')
                        .getText('@artcilePublishedStatus', function (results) {
                            const val = results.value
                            this.assert.equal(articleData.articlePublishedDropdownStatus, val)
                        })
                        .click('@artcilePublishedStatus')
                }
                else if (articleStatus == articleData.articleDraftDropdownStatus) {
                    this
                        .waitForElementPresent('@artcileDraftStatus')
                        .getText('@artcileDraftStatus', function (results) {
                            const val = results.value
                            this.assert.equal(articleData.articleDraftDropdownStatus, val)
                        })
                        .click('@artcileDraftStatus')
                }
                else if (articleStatus == articleData.articleExpiredDropdownStatus) {
                    this
                        .waitForElementPresent('@artcileExpiredStatus')
                        .getText('@artcileExpiredStatus', function (results) {
                            const val = results.value
                            this.assert.equal(articleData.articleExpiredDropdownStatus, val)
                        })
                        .click('@artcileExpiredStatus')
                }
                else if (articleStatus == articleData.articleScheduledDropdownStatus) {
                    this
                        .waitForElementPresent('@artcileScheduledStatus')
                        .getText('@artcileScheduledStatus', function (results) {
                            const val = results.value
                            this.assert.equal(articleData.articleScheduledDropdownStatus, val)
                        })
                        .click('@artcileScheduledStatus')
                }
                else if (articleStatus == articleData.articleVettedDropdownStatus) {
                    this
                        .waitForElementPresent('@artcileVettedStatus')
                        .getText('@artcileVettedStatus', function (results) {
                            const val = results.value
                            this.assert.equal(articleData.articleVettedDropdownStatus, val)
                        })
                        .click('@artcileVettedStatus')
                }
                this.pause(1000)
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        EDIT_ARTICLE: function () {
            try {
                this
                    .pause(1000)
                    .waitForElementPresent('@articleEditButton')
                    .click('@articleEditButton')
                    .assert.urlContains('edit')
                    .pause(2000)
                    .waitForElementPresent('@articleHeadlineInputBox')
                // .getValue('@articleHeadlineInputBox', function (results) {
                //     const val = results.value
                //     // console.log('Edit Articke Valus is >>>>', val)
                //     this.assert.equal(articleData.artcileHeadline, val)
                // })
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },



        ARTICLE_SELECT_SCHEDULE_DATE: function () {
            try {
                //console.log('callled >>>>>', this.GET_CURRENT_DATE())
                this
                    .waitForElementPresent('@articleSchedulingOptions')
                    .click('@articleSchedulingOptions')
                    .waitForElementPresent('@articlePublishOnDate')
                    .setValue('@articlePublishOnDate', this.GET_CURRENT_DATE())
                    .waitForElementPresent('@articlePublishOnTime')
                    .setValue('@articlePublishOnTime', articleData.articlePublishOnTime)
                    .setValue('@articlePublishOnTime', articleData.articlePublishOnTimeAMPM)
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        ARTICLE_SELECT_UNPUBLISH_ON_DATE: function () {
            try {
                this
                    // .waitForElementPresent('@articleSchedulingOptions')
                    //.click('@articleSchedulingOptions')
                    .waitForElementPresent('@articleUnPublishOnDate')
                    .setValue('@articleUnPublishOnDate', this.GET_CURRENT_DATE())
                    .waitForElementPresent('@articleUnPublishOnTime')
                    .setValue('@articleUnPublishOnTime', articleData.articlePublishOnTime)
                    .setValue('@articleUnPublishOnTime', articleData.articlePublishOnTimeAMPM)
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },


        ARTICLE_SELECT_RELEASE_DATE: function () {
            try {
                //console.log('callled >>>>>', this.GET_CURRENT_DATE())
                this
                    .waitForElementPresent('@articleReleaseDate')
                    .setValue('@articleReleaseDate', this.GET_FUTURE_DATE())
                    .waitForElementPresent('@articleReleaseTime')
                    .setValue('@articleReleaseTime', articleData.articlePublishOnTime)
                    .setValue('@articleReleaseTime', articleData.articlePublishOnTimeAMPM)

            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        GET_CURRENT_DATE: function () {
            try {
                var today = new Date();
                var dd = String(today.getDate()).padStart(2, '0');
                var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
                var yyyy = today.getFullYear();
                today = dd + '/' + mm + '/' + yyyy;
                console.log("Date is ---->>>>>>  ", today);
                return today;
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        GET_FUTURE_DATE: function () {
            try {
                var future = new Date();
                var dd = String(future.getDate() + 1).padStart(2, '0');
                var mm = String(future.getMonth() + 1).padStart(2, '0'); //January is 0!
                var yyyy = future.getFullYear();
                future = dd + '/' + mm + '/' + yyyy;
                //console.log("Future Date is ---->>>>>>  ",future);
                return future;

            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        GET_CURRENT_DATE_IN_FORMAT: function () {
            try {
                var today = new Date();
                var dd = String(today.getDate()).padStart(2, '0');
                var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
                var yyyy = today.getFullYear();
                today = yyyy + '-' + mm + '-' + dd;
                //console.log("Date is ---->>>>>>  ",today);
                return today;
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        GET_FUTURE_DATE_IN_FORMAT: function () {
            try {
                var future = new Date();
                var dd = String(future.getDate() + 1).padStart(2, '0');
                var mm = String(future.getMonth() + 1).padStart(2, '0'); //January is 0!
                var yyyy = future.getFullYear();
                future = yyyy + '-' + mm + '-' + dd;
                //console.log("Future Date is ---->>>>>>  ",future);
                return future;
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        ARTICLE_RESET_SCHEDULED_DATE: function () {
            try {
                this
                    .waitForElementPresent('@articleSchedulingOptions')
                    //.pause(10000)
                    //.click('@articleSchedulingOptions')
                    .waitForElementPresent('@articlePublishOnDateCloseButton')
                    .click('@articlePublishOnDateCloseButton')
                    .waitForElementPresent('@articlePublishOnTimeCloseButton')
                    .click('@articlePublishOnTimeCloseButton')
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        ARTICLE_RESET_UNPUBLISH_DATE: function () {
            try {
                this
                    .waitForElementPresent('@articleSchedulingOptions')
                    //.pause(10000)
                    //.click('@articleSchedulingOptions')
                    .waitForElementPresent('@articleUnPublishOnDateCloseButton')
                    .click('@articleUnPublishOnDateCloseButton')
                    .waitForElementPresent('@articleUnPublishOnTimeCloseButton')
                    .click('@articleUnPublishOnTimeCloseButton')
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        ARTICLE_RESET_RELEASE_DATE: function () {
            try {
                this
                    .waitForElementPresent('@articleReleaseDateCloseButton')
                    .click('@articleReleaseDateCloseButton')
                    .waitForElementPresent('@articleReleaseTimeCloseButton')
                    .click('@articleReleaseTimeCloseButton')
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        VERIFY_DATE_IS_RESET: function (dateType) {
            try {

                if (dateType == 'Publish Date') {
                    this
                        .getValue('@articlePublishOnDate', function (results) {
                            const val = results.value
                            //console.log('This is Publish date ------>>>>>',val)
                            this.assert.equal(val, '')
                        })
                        .getValue('@articlePublishOnTime', function (results) {
                            const val = results.value
                            //console.log('This is Publish time ------>>>>>',val)
                            this.assert.equal(val, '')
                        })
                }
                else if (dateType == 'UnPublish Date') {
                    this
                        .getValue('@articleUnPublishOnDate', function (results) {
                            const val = results.value
                            //console.log('This is UnPublish date ------>>>>>',val)
                            this.assert.equal(val, '')
                        })
                        .getValue('@articleUnPublishOnTime', function (results) {
                            const val = results.value
                            //console.log('This is UnPublish time ------>>>>>',val)
                            this.assert.equal(val, '')
                        })
                }
                else if (dateType == 'Release Date') {
                    this
                        .getValue('@articleReleaseDate', function (results) {
                            const val = results.value
                            //console.log('This is Release date ------>>>>>',val)
                            this.assert.equal(val, '')
                        })
                        .getValue('@articleReleaseTime', function (results) {
                            const val = results.value
                            // console.log('This is Release time ------>>>>>',val)
                            this.assert.equal(val, '')
                        })
                }
                else if (dateType == 'Embargo Date') {
                    this
                        .getValue('@articleEmbargoDate', function (results) {
                            const val = results.value
                            //console.log('This is Embargo date ------>>>>>',val)
                            this.assert.equal(val, '')
                        })
                        .getValue('@articleEmbargoTime', function (results) {
                            const val = results.value
                            //console.log('This is Embargo time ------>>>>>',val)
                            this.assert.equal(val, '')
                        })
                }
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        ARTICLE_RESET_EMBARGO_DATE: function () {
            try {
                this
                    .waitForElementPresent('@artcileEmbargoCheckBox')
                    .assert.visible('@articleReleaseTimeCloseButton')
                    .click('@artcileEmbargoCheckBox')
                    .pause(1000)
                    .waitForElementPresent('@articleEmbargoDateCloseButton')
                    .click('@articleEmbargoDateCloseButton')
                    .waitForElementPresent('@articleEmbargoTimeCloseButton')
                    .click('@articleEmbargoTimeCloseButton')
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },



        CLICK_ON_DELETE_BUTTON: function () {
            try {
                this
                    .waitForElementPresent('@articleDeleteButton')
                    .click('@articleDeleteButton')
                    .waitForElementPresent('@artcileConfirmDelete')
                    .click('@artcileConfirmDelete')
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }

        },

        VERIFY_ARTCILE_DELETED_SUCCESS_MESSAGE: function (articleName) {
            try {
                var successMsg = 'The Article ' + articleName + ' has been deleted.'
                this
                    //.waitForElementPresent('xpath','@artcleCreateSuccessMessage')
                    .getText('@articleDeleteMessage', function (results) {
                        const val = results.value;
                        //console.log('Success message is >>>>>>>>',val)
                        this.assert.equal(val, successMsg);
                    })
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        VERIFY_HEADLINE_ON_EDIT_ARTICLE: function (artcileHeadline) {
            try {
                this
                    .waitForElementPresent('@articleHeadlineInputBox')
                    .getValue('@articleHeadlineInputBox', function (results) {
                        const val = results.value;
                        //console.log('Article Headline is >>>>>>>>',val)
                        this.assert.equal(val, artcileHeadline);
                    })
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        VERIFY_BRIEF_TEXT_ON_EDIT_ARTICLE: function (articleBriefText) {
            try {
                this.assert.visible('@articleBriefLabel')
                    .click('@articleBriefLabel')
                    //.pause(3000)
                    .waitForElementPresent('@articleBriefTextArea')
                    .api.frame(0)
                    .click('[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]')
                    //.pause(3000)
                    .getText('[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]', function (results) {
                        const val = results.value;
                        //console.log('Article Brief Text is >>>>>>>>',val)
                        this.assert.equal(val, articleBriefText);
                    })
                    .frame(null)
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        VERIFY_IMAGE_URL: function () {
            try {
                this
                    .waitForElementPresent('@artcileHeroImageonEditArticle')
                    .expect
                    .element('@artcileHeroImageonEditArticle').to.have.attribute('src').which.contains(articleData.cloudinaryURL)
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        VERIFY_CONTENT_TEXT_ON_EDIT_ARTICLE: function (articleContentText) {
            try {
                this
                    .waitForElementPresent('@articleContentTextArea')
                    .api.frame(2)
                    .click('[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]')
                    //.pause(3000)
                    .getText('[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]', function (results) {
                        const val = results.value;
                        //console.log('Article Content Text is >>>>>>>>',val)
                        this.assert.equal(val, articleContentText);
                    })
                    .frame(null)
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        VERIFY_CATEGORY_ON_EDIT_ARTICLE: function (articleCategory) {
            try {
                this
                    .waitForElementPresent('@articleSelectedCategoryOnEditArticle')
                    .getText('@articleSelectedCategoryOnEditArticle', function (results) {
                        const val = results.value;
                        //console.log('Article Category Text is >>>>>>>>',val)
                        this.assert.equal(val, articleCategory);
                    })
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        VERIFY_TOPICS_ON_EDIT_ARTICLE: function (articleTopic) {
            try {
                this
                    .waitForElementPresent('@topicsInputBox')
                    .expect
                    .element('@topicsInputBox').to.have.attribute('value').which.contains(articleTopic)
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        VERIFY_ARTICLE_FLAG_ON_EDIT_ARTICLE: function (articleFlag) {
            try {
                this
                    .waitForElementPresent('@articleSelectedFlagOnEditArticle')
                    .getText('@articleSelectedFlagOnEditArticle', function (results) {
                        const val = results.value;
                        //console.log('Article Selected Flag is >>>>>>>>',val)
                        this.assert.equal(val, articleFlag);
                    })
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        VERIFY_BYLINE_ON_EDIT_ARTICLE: function (articleByLine) {
            try {
                this
                    .waitForElementPresent('@articleSelectedByLineOnEditArticle')
                    .getText('@articleSelectedByLineOnEditArticle', function (results) {
                        const val = results.value;
                        //console.log('Article Selected ByLine is >>>>>>>>',val)
                        this.assert.equal(val, articleByLine);
                    })
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        VERIFY_STATUS_ON_EDIT_ARTICLE: function (articleSelectedStatus) {
            try {
                this
                    .waitForElementPresent('@articleSelectedStatusOnEditArticle')
                    .assert.containsText('@articleSelectedStatusOnEditArticle', articleSelectedStatus)
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        UPDATE_ARTICLE_HEADLINE: function (updatedArticleHeadline) {
            try {
                this
                    .assert.visible('@articleHeadlineInputBox')
                    .clearValue('@articleHeadlineInputBox')
                    .setValue('@articleHeadlineInputBox', updatedArticleHeadline)
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        UPDATE_ARTICLE_BRIEF_DATA: function (updatedArticleBriefData) {
            try {
                this.assert.visible('@articleBriefLabel')
                    .click('@articleBriefLabel')
                    //.pause(3000)
                    .waitForElementPresent('@articleBriefTextArea')
                    .api.frame(0)
                    .click('[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]')
                    .clearValue('[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]')
                    //.pause(3000)
                    .setValue('[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]', updatedArticleBriefData)
                    .frame(null)
                //.pause(1000)
                //.click('@articleBriefLabel')
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },


        UPDATE_DATA_IN_CONTENT_TEXT: function (updatedaArticleContentText) {
            try {
                this.
                    waitForElementPresent('@articleContentTextArea')
                    .api.frame(1)
                    //.pause(3000)
                    .click('[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]')
                    .clearValue('[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]')
                    .setValue('[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]', updatedaArticleContentText)
                    .frame(null)
                //.pause(3000)
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        UPDATE_ARTICLE_CATEGORY: function (updateSelectedArticleCategory) {
            try {
                this
                    .waitForElementPresent('@articleCategoryInputBox')
                    .waitForElementPresent('@articleSelectedCategoryCloseButtonOnEditArticle')
                    .click('@articleSelectedCategoryCloseButtonOnEditArticle')
                    .click('@articleCategoryInputBox')
                    .setValue('@articleCategoryInputBox', updateSelectedArticleCategory)
                    .pause(1000)
                this.api.elements('css selector', '[class="autocomplete-deluxe-highlight-char"]', function (result) {
                    // console.log('aaaaaa ----->>> ', result.value.length);
                    for (var i = 1; i <= result.value.length; i++) {
                        if (i <= result.value.length) {
                            this
                                .getText('xpath', '//li[' + i + ']/a[@class="ui-menu-item-wrapper"]', function (results) {
                                    const val = results.value;
                                    //console.log('value is >>', val)
                                    if (val == updateSelectedArticleCategory) {
                                        //  console.log('Matched')
                                        this
                                            .waitForElementPresent('xpath', '//li[1]/a[@class="ui-menu-item-wrapper"]')
                                            .click('xpath', '//li[1]/a[@class="ui-menu-item-wrapper"]')
                                            .pause(1000)

                                    }
                                })
                        }
                    }
                }

                );

                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },


        UPDATE_TOPICS: function (updatedArticleTopic) {
            try {
                this
                    .waitForElementPresent('@topicsInputBox')
                    .clearValue('@topicsInputBox')
                    .setValue('@topicsInputBox', updatedArticleTopic)
                    .pause(1000)
                    .waitForElementPresent('@topicsListItem')
                    .getText('@topicsListItem', function (results) {
                        const val = results.value
                        //console.log('Topic value is >>>>>>>',val)
                        this.assert.equal(updatedArticleTopic, val)
                    })
                    .click('@topicsListItem')
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        UPDATE_ARTICLE_FLAG: function (updatedArticleFlag) {
            try {
                this
                    .waitForElementPresent('@articleFlagInputBox')
                    .waitForElementPresent('@articleSelectedTopicCloseButtonOnEditArticle')
                    .click('@articleSelectedTopicCloseButtonOnEditArticle')
                    .setValue('@articleFlagInputBox', updatedArticleFlag)
                    .pause(1000)
                    .waitForElementPresent('@articleFlagFirstItem')

                    .getText('@articleFlagFirstItem', function (results) {
                        const val = results.value
                        // console.log('Article Flag value is >>>>>>>',val)
                        this.assert.equal(val, updatedArticleFlag)
                    })
                    .click('@articleFlagFirstItem')
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },



        UPDATE_ARTICLE_BYLINE: function (updatedArticleByLine) {
            try {
                this
                    .waitForElementPresent('@articleBylineInputBox')
                    .waitForElementPresent('@articleSelectedByLineCLoseButtonOnEditArticle')
                    .click('@articleSelectedByLineCLoseButtonOnEditArticle')
                    .setValue('@articleBylineInputBox', updatedArticleByLine)
                    .pause(1000)
                    .waitForElementPresent('@articleBylineFirstItem')
                    .getText('@articleBylineFirstItem', function (results) {
                        const val = results.value
                        // console.log('Article ByLine value is >>>>>>>',val)
                        this.assert.equal(updatedArticleByLine, val)
                    })
                    .click('@articleBylineFirstItem')
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        VERIFY_META_TAGS_VISIBLE: function () {
            try {
                this
                    .assert
                    .visible('@articleMetaTags')
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },


        VERIFY_HTML_ACCESS: function () {
            try {
                this
                    .assert
                    .visible('@textFormatLabel')
                    .assert
                    .visible('@textFormatSelect')
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },


        VERIFY_ARTICLE_RELEASE_DATE: function (dateFormat) {
            try {
                this
                    .waitForElementPresent('@articleReleaseDate')
                    .getValue('@articleReleaseDate', function (results) {
                        const val = results.value
                        //console.log('Article Release Date value is >>>>>>>',val)
                        this.assert.equal(val, dateFormat)
                    })
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        ENTER_ARTICLE_BODY_BRIEF: function (articleBrief) {
            try {
                this
                    .api.frame(0)
                    .assert.visible('[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]')
                    .waitForElementPresent('[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]')
                    .setValue('[class="cke_editable cke_editable_themed cke_contents_ltr cke_show_borders"]', articleBrief)
                    .frame(null)
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        CLICK_ON_SAVE_EDIT_BUTTON: function () {
            try {
                this
                    .waitForElementPresent('@articleSaveAndEdit')
                    .click('@articleSaveAndEdit')
                    .pause(2000)
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        VERIFY_CONTENT_IS_LOCKED: function () {
            let expectedMsg = "and is therefore locked to prevent other users changes. This lock is in place since";
            try {
                this
                    .assert
                    .visible('@breaklockLink')
                    .assert
                    .containsText('@contentLockWarningMsg', expectedMsg)
                return this;
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        BREAK_LOCK: function () {
            try {
                this
                    .pause(3000)
                    .isVisible('@breaklockLink', results => {
                        if (results.value) {
                            this
                                .click('@breaklockLink')
                                .pause(2000)
                                .waitForElementPresent('@confirmBreakLockButton')
                                .click('@confirmBreakLockButton')
                                .pause(2000)
                        }
                    });
                this.isVisible('@unlockButton', results => {
                    if (results.value) {
                        this
                            .click('@unlockButton')
                            .pause(2000)
                            .waitForElementPresent('@confirmBreakLockButton')
                            .click('@confirmBreakLockButton')
                            .pause(2000)
                            .getText('@lockBrokenMessage', function (results) {
                                const val = results.value
                                console.log('Lock broken message >>>>>>>', val)
                                this.assert.equal('Lock broken. Anyone can now edit this content.', val)
                            })
                    }
                });
                return this;
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        VERIFY_RESUME_OR_DISCARD_DIALOG: function () {
            try {

                let today = new Date();
                let months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                let mon = months[today.getMonth()];
                let dt = today.getDate();
                let yr = today.getFullYear();
                let expectedDate = `${mon} ${dt}, ${yr}`;

                let expectedMsg1 = `A version of this page you were editing at ${expectedDate}`;
                let expectedMsg2 = "was saved as a draft. Do you want to resume editing or discard it?";

                this
                    .assert.visible('@resumeOrdiscardMsg')
                    .assert.visible('@articleResumeEditingButton')
                    .assert.visible('@articleDiscardButton')
                    .getText('@resumeOrdiscardMsg', function (results) {
                        const val = results.value
                        let actualMsg1 = val.slice(0, 55);
                        let actualMsg2 = val.slice(62);
                        this
                            .assert.equal(actualMsg1, expectedMsg1)
                            .assert.equal(actualMsg2, expectedMsg2)
                    })
                return this;
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        CLICK_ON_REVISIONS_TAB: function () {
            try {
                this
                    .waitForElementPresent('@revisionsTab')
                    .click('@revisionsTab')
                    .pause(2000)
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        CLICK_ON_EDIT_TAB: function () {
            try {
                this
                    .waitForElementPresent('@editTab')
                    .click('@editTab')
                    .pause(2000)
                return this
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        },

        CLICK_ON_RESUME_EDIT_BUTTON: function () {
            try {
                this
                    .pause(1000)
                    .click('@articleResumeEditingButton')
                    .pause(3000)

                return this;
            }
            catch (err) {
                console.log('Error is : >>>>>>>>>   ', err)
            }
        }

    }],






}