Feature: Logging debug data
  In order to debug my Behat suites with ease
  As a developer
  I want to be able to access logs

  Background:
    Given there is following Behat extension configuration:
        | directory  | logs |
        | screenshot | true |

  Scenario:
     When I run behat with failing scenarios
     Then there should be text log generated

  Scenario:
     When I run behat with failing scenarios using javascript profile
     Then there should be text log generated
      And a screenshot should be made

  Scenario:
    Given configuration option "screenshot" is set to "false"
     When I run behat with failing scenarios using javascript profile
     Then there should be text log generated
      And a screenshot should not be made
