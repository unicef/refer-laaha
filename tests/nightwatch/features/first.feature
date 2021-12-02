Feature: Google Search

Scenario: Searching Google

  Given I open Google's search pages
  Then the title is "Google"
  And the Google search form exists