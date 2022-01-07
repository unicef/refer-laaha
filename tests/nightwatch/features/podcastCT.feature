Feature: Podcast Detail Page

Background: Verify user can launch browser and login into CMS
        Given launch browser, open vss url
        Then enter the credentials

Scenario: Verify Podcast Detail Elements and Create Podcast Detail Page
   Given Navigate to the podcast page
   Then Verify Create Podcast
   Then Verify Podcast Title
   Then Verify Podcast Language Dropdown
   Then Verify Podcast Tags
   Then Verify Podcast Content Layout
   Then Verify Podcast Thumbnail Image
   Then Verify Podcast Sub Category Dropdown
   Then Verify Podcast Moderation State
   Then Verify Podcast Save Button
   Then Verify Podcast Preview
   Then Add Title in Podcast
   Then Select Podcast Language
   Then Add Podcast Tags
   Then Add Podcast Content Layout
   Then Upload Podcast Thumbnail Image
   Then Select Podcast Sub Category
   Then Click on Podcast Save button