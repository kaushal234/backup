Feature: Test tool types API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Quality\CalibratedTools\ToolType" should only be available for intranet user

  Scenario: Request all tool types
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/calibrated_tools/tool_types"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/quality/calibrated_tools/tool_type/schemas/tool_types.json"

  Scenario: Request a single tool type
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/calibrated_tools/tool_types/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/quality/calibrated_tools/tool_type/schemas/tool_type.json"

  Scenario: Update a given tool type - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/calibrated_tools/tool_types/3" with the body "tests/fixtures/json/quality/calibrated_tools/tool_type/dummies/put.json"
    Then the response status code should be 403

  Scenario: Update a given tool type - permissions OK
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/calibrated_tools/tool_types/1" with the body "tests/fixtures/json/quality/calibrated_tools/tool_type/dummies/put.json"
    Then the response status code should be 200
    And the JSON node "description" should be equal to "hammers"

  Scenario: Create a tool type - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/calibrated_tools/tool_types" with the body "tests/fixtures/json/quality/calibrated_tools/tool_type/dummies/post.json"
    Then the response status code should be 403

  Scenario: Create a tool type - permissions OK
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/calibrated_tools/tool_types" with the body "tests/fixtures/json/quality/calibrated_tools/tool_type/dummies/post.json"
    Then the response status code should be 201
    And the JSON nodes should be equal to:
          | description | hammers |

  Scenario: Delete a tool type - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/calibrated_tools/tool_types/1"
    Then the response status code should be 403

  Scenario: Delete a tool type - permissions OK
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/calibrated_tools/tool_types/5"
    Then the response status code should be 204

  Scenario: Delete a used ToolType
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/calibrated_tools/tool_types/1"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The Tool type 'hammers' is not deletable because it is used by 11 Tools (1, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27)"
