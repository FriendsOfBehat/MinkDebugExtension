Feature: Testing MinkDebugExtension
  In order to test MinkDebugExtension
  As a behat
  I want to download a page and fail

  Scenario: Downloading a page and failing
     When I go to "http://sylius.org"
     Then I select "Create failing test" from "Available steps"

  @javascript
  Scenario: Downloading a page and failing (Javascript session)
    When I go to "http://sylius.org"
    Then I select "Create failing test" from "Available steps"
