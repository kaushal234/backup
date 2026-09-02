Feature: Directory / Position Category

  Scenario: As an anonymous user, test that I'm not allowed to see position classifications pages
    When I go to "/directory/position-classifications/2/edit"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that I'm allowed to see position classifications pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/position-classifications/2/edit"
    Then the response status code should be 200
    And The module should be "ESM"

  Scenario: As a superuser, test that I can edit a position classification
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/position-classifications/2/edit"
    Then the response status code should be 200
    And The module should be "ESM"
    When I select "/position_categories/1" from "position_classification_batch[positionClassifications][1][positionCategory]"
    And press "position_classification_batch[submit]"
    Then the response status code should be 200
    And I should be on "/directory/position-classifications/2/edit"
    And I should see "Cat & gory (indirect)"
