Feature: Test Type Default Assignee Entity

  Scenario: Request all type default assignees as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/type_default_assignees"
    Then the response status code should be 404

  Scenario: Request all type default assignees as extranet user
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/type_default_assignees"
    Then the response status code should be 404

  Scenario: Request all type default assignees as intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/type_default_assignees"
    Then the response status code should be 404

  Scenario: Request all type default assignees as vendor user
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/type_default_assignees"
    Then the response status code should be 404

  Scenario: Request a single type default assignee as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/type_default_assignees/1"
    Then the response status code should be 403

  Scenario: Request a single type default assignee as extranet user
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/type_default_assignees/1"
    Then the response status code should be 403

  Scenario: Request a single type default assignee as vendor user
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/type_default_assignees/1"
    Then the response status code should be 403

  Scenario: Request a single type default assignee as intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/type_default_assignees/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/type_default_assignee/schemas/type_default_assignee.json"

  Scenario: Update a type default assignee should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/types/1" with body:
    """
    {}
    """
    Then the response status code should be 405

  Scenario: Create a type default assignee should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/types" with body:
    """
    {}
    """
    Then the response status code should be 405

  Scenario: Delete a type default assignee should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/mis/types/1"
    Then the response status code should be 405

  Scenario: Update type default assignees as a batch
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/type_default_updates" with body:
    """
    {}
    """
    And the response status code should be 403
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/type_default_updates" with body:
    """
    {
      "modules": [{
        "@id": "/modules/1",
        "typeDefaultAssignees": [
          {
            "type": "/mis/types/1",
            "defaultAssignee": "LKU / GKU"
          }
        ]
      }]
    }
    """
    And the response status code should be 204
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    #ID 2 is the one we just created, there is only one in database fixtures
    When I send a "GET" request to "/mis/type_default_assignees/2"
    And the response status code should be 200

  Scenario: DELETE type default assignees as a batch only for MISM
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/mis/type_default_assignees/1"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/mis/type_default_assignees/1"
    And the response status code should be 204