Feature: Video Detail Page

Background: Verify user can launch browser and login into CMS
        Given launch browser, open vss url
        Then enter the credentials

Scenario: Verify Video Detail Elements and Create Video Detail Page
   Given Navigate to the video page
   Then Verify Create video
   Then Verify Video Title
   Then Verify Video Language Dropdown
   Then Verify Video Tags
   Then Verify Video Content Layout
   Then Verify Video Thumbnail Image
   Then Verify Video Sub Category Dropdown
   Then Verify Video Moderation State
   Then Verify Video Save Button
   Then Verify Video Preview
   Then Add Title in Video
   Then Select Video Language
   Then Add Video Tags
   Then Add Video Content Layout
#    Then Upload Article Thumbnail Image
#    Then Select Article Sub Category
#    Then Click on Save button