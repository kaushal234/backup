Feature: Test customers relationship team API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Sales\CustomerRelationshipTeam" should only be available for intranet user

  Scenario: Request all customer relationship teams
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customer_relationship_teams"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer_relationship_team/schemas/customer_relationship_teams.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\CustomerRelationshipTeam" is exposed on the API
    Then the filter "legacyId" should be available and its type should be "int"
    And the filter "order[id]" should be available and its type should be "string"
    And the filter "customer" should be available and its type should be "string"
    And the filter "partsLocation.capability.sparePartsHub" should be available and its type should be "bool"

  Scenario: Request a single customer relationship team
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customer_relationship_teams/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer_relationship_team/schemas/customer_relationship_team.json"

  Scenario: basic users can't create a customer relationship team
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/customer_relationship_teams" with the body "tests/fixtures/json/sales/customer_relationship_team/dummies/post.json"
    Then the response status code should be 403

  Scenario: superuser can create a customer relationship team
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/customer_relationship_teams" with the body "tests/fixtures/json/sales/customer_relationship_team/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer_relationship_team/schemas/customer_relationship_team.json"
    And the JSON node "taskId" should not be null
    And an email should have been sent asynchronously with subject matching pattern "/Tasks, New: #\S+ opened for SAM user by SUPERUSER user/"
    And this asynchronous email should be sent to "user-sam@tld.fr"

  Scenario: superuser can't create a duplicated customer relationship team
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/customer_relationship_teams" with the body "tests/fixtures/json/sales/customer_relationship_team/dummies/post.json"
    Then the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string "This combination eCustomer + Business Partner Code + ERP Location is already used by another CRT"

  Scenario: basic users can't update a customer relationship team
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customer_relationship_teams/1" with the body "tests/fixtures/json/sales/customer_relationship_team/dummies/put.json"
    Then the response status code should be 403

  Scenario: superuser can update a customer relationship team
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customer_relationship_teams/1" with the body "tests/fixtures/json/sales/customer_relationship_team/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer_relationship_team/schemas/customer_relationship_team.json"
    And an update log should have been inserted on resource "/sales/customer_relationship_teams/1" with a changeset on the property "customerBusinessPartnerCode"
    Then I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customer_relationship_teams/1" with body:
    """
    {
      "customer": "/sales/customers/31",
      "erpLocation": "/locations/23"
    }
    """
    Then the response status code should be 200

  Scenario: User Sales Agents can update a customer relationship team
    Given I authenticate as the intranet user "user-sales-agents@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customer_relationship_teams/5" with the body "tests/fixtures/json/sales/customer_relationship_team/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer_relationship_team/schemas/customer_relationship_team.json"
    And an update log should have been inserted on resource "/sales/customer_relationship_teams/5" with a changeset on the property "customerBusinessPartnerCode"

  Scenario: Delete a crt - permissions OK but crt used
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/customer_relationship_teams/6"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The customer relationship team '6' is not deletable because it is used by 1 XU ACL (1)"

  Scenario: Delete a crt - permissions OK and crt not used
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/customer_relationship_teams/3"
    Then the response status code should be 204
