Feature: Verify Header Elements

Background: Verify user can launch browser and login into CMS
        Given launch browser, open vss url
        Then enter the credentials

        Scenario: Verify all Header elememts
          Given Verify Exit Functionality
          Then Verify VSS Title
          Then Verify VSS Logo
          Then Verify Language Switcher
          Then Verify Discover nav Menu
          Then Verify FAQs nav menu
          Then Verify Get In Touch nav menu
          Then Verify Resource Library nav menu

        Scenario: Verify Virtual Safe Space Title and logo
          Given Verify VSS Title

        Scenario: Add and Verify VSS Header Menu Items
          Given Naviagte to Header Site Navigation Menus
          Then Verify Header Site Navigation Menus
          Then Add Link
          Then Add Title
          Then Click Save

        Scenario: Exit Functionality
          Given Verify Exit Functionality
          Then Click on Exit Functionality

