Feature: Test role_access of user story

  Scenario: As basic user I can Get one role access
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/role_accesses/13"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/roles_accesses/schemas/role_access.json"

  Scenario: As extranet user I can't Get one role access
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/role_accesses/13"
    Then the response status code should be 401

  Scenario: As extranet user authenticate I can't Get one role access
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/role_accesses/13"
    Then the response status code should be 403

  Scenario: As vendor user authenticate I can't Get one role access
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/role_accesses/13"
    Then the response status code should be 403

  Scenario: I can Get all roles accesses
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/role_accesses"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/roles_accesses/schemas/role_accesses.json"

  Scenario: As extranet user I can't Get all role access
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/role_accesses"
    Then the response status code should be 401

  Scenario: As extranet user authenticate I can't Get all role access
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/role_accesses"
    Then the response status code should be 403

  Scenario: As vendor user authenticate I can't Get all role access
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/role_accesses"
    Then the response status code should be 403

  Scenario: I can't Post a role access
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/role_accesses" with body:
    """
    {
    }
    """
    Then the response status code should be 405

  Scenario: I can't Put(update) a role access
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/role_accesses/9" with body:
    """
    {
    }
    """
    Then the response status code should be 405

  Scenario: I can't delete a role access
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/mis/role_accesses/9"
    Then the response status code should be 405