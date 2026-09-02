Feature: Quality / CRAB

  Scenario: As an anonymous user, test that i'm not allowed to see derogations pages
    When I go to "/quality/derogations"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/quality/derogations/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see derogations pages detail
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/derogations"
    Then the response status code should be 200
    And The module should be "DEROGATION"
    And I should see "Last derogations created"
    And I should see "FILTER"
    When I go to "/quality/derogations/1/show"
    Then the response status code should be 200

  Scenario: As a superuser, test that i can edit a derogation
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/derogations/2/edit"
    Then the response status code should be 200
    And I should be on "/quality/derogations/2/edit"
    And I fill in "derogation[shortDescription]" with "Finalement, elle est plutôt grande, genre 10cm"
    And I fill in "derogation[description]" with "Bahblahblah spooky"
    And press "derogation[submit]"
    Then the response status code should be 200
    And I should be on "/quality/derogations/2/show"

  Scenario: As a superuser, test that i can download derogations
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/derogations"
    And press "download"
    Then the response status code should be 200

  Scenario: As a basic user, test that i can't delete a derogation
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/derogations/1/show"
    Then the response status code should be 200
    And I should not see an "a[href$='/quality/derogations/1/delete']" element

  Scenario: As a qam, test that i can delete a derogation
    Given I authenticate as "user-qam@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/derogations/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/quality/derogations/1/delete']" element
    When I go to "/quality/derogations/1/delete"
    Then the response status code should be 200
    And I should be on "/quality/derogations"
    And I should see "Derogation deleted successfully"

  Scenario: As a qam, test that i can't delete an accepted derogation
    Given I authenticate as "user-qam@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/derogations/3/show"
    And I should not see an "a[href$='/quality/derogations/3/delete']" element
    When I go to "/quality/derogations/3/delete"
    Then the response status code should be 200
    And I should be on "/quality/derogations/3/show"
    And I should see "Unable to delete derogation"
