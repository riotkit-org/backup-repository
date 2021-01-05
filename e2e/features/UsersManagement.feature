Feature: Create users, edit basic fields in existing user profiles, searching the user list

Scenario: Create a example user from web panel
  Given I am authenticated as administrator
  And I visit users search page
  And I press "Add user" button
