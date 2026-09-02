Feature: Remove all files created during behat tests

  Scenario: generated files must be cleaned after tests
    Then I delete all the files created during test