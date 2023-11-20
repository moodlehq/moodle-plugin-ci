@local @local_ci
Feature: Testing feature from auth login

  Scenario: Log in with the predefined admin user with Javascript disabled
    Given I log in as "admin"
    Then I should see "You are logged in as Admin User" in the "page-footer" "region"

  @javascript
  Scenario: Log in with the predefined admin user with Javascript enabled
    Given I log in as "admin"
    Then I should see "You are logged in as Admin User" in the "page-footer" "region"

  @javascript @app
  Scenario: Log in with the predefined admin user in the app
    Given I entered the app as "admin"
    Then I should find "Your users are not receiving any notification" in the app

    When I press "OK" in the app
    And I press the more menu button in the app
    Then I should find "Travis Tester" in the app

    When I press "Travis Tester" in the app
    Then I should find "Hello CI!" in the app
