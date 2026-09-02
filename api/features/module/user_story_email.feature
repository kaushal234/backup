Feature: Test email of user_story

  Scenario: Resource should not be available for extranet users
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/emails/1"
    Then the response status code should be 403


  Scenario: Resource should not be available for evendors users
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/emails/1"
    Then the response status code should be 403

  Scenario: I can Get one email
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/emails/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/user_story_emails/schemas/email.json"

  Scenario: I can Get all emails
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/emails"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/user_story_emails/schemas/emails.json"

  Scenario: I can't POST a email
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/emails" with body:
    """
    {
    }
    """
    Then the response status code should be 405

  Scenario:  I can't Update a email
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/emails/20" with body:
    """
    {
    }
    """
    Then the response status code should be 405

  Scenario: I can't delete a email
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/mis/emails/5"
    Then the response status code should be 405