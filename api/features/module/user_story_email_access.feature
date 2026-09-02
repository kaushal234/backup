Feature: Test email_access of module

  Scenario: I can Get one email access
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/email_accesses/5"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/user_story_emails_accesses/schemas/emails_access.json"

  Scenario: I can Get all email access
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/email_accesses"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/user_story_emails_accesses/schemas/emails_accesses.json"

  Scenario: I can't Post a email access
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/email_accesses" with body:
    """
    {
    }
    """
    Then the response status code should be 405

  Scenario: I can't Put(update) a email access
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/email_accesses/5" with body:
    """
    {
    }
    """
    Then the response status code should be 405

  Scenario: I can't delete a email access
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/mis/email_accesses/5"
    Then the response status code should be 405