Feature: Test Warranty Claim API

  Scenario: Request all Warranty Claims
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/warranty_claims"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 4
    And the JSON should be valid according to the schema "tests/LegacyBundle/fixtures/json/warranty_claim/schemas/warranty_claims_requests.json"

  Scenario: Request all Warranty Claims by a customer name
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/warranty_claims?customerName=customer_for_fur"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 2

  Scenario: Requesting a Warranty Claim without the required permissions should return a 404 code
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/warranty_claims/58"
    Then the response status code should be 404

  Scenario: Request one Warranty Claim with the required permissions should return a 200 code
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/warranty_claims/60"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/LegacyBundle/fixtures/json/warranty_claim/schemas/warranty_claim_request.json"
    And the JSON node "parts" should have 2 elements
    And the JSON node "parts[0].spr.status" should be equal to "SHIPPED"
    And the JSON node "parts[1].spr" should be null

  Scenario: Request one Warranty Claim
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/warranty_claims/58"
    Then the response status code should be 404

  Scenario: A warranty claim coming from a Commissioning TOC is hidden from extranet users
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/warranty_claims/63"
    Then the response status code should be 404

  Scenario: Filters are declared on warranty claims
    Given the class "LegacyBundle\Entity\Quality\WarrantyClaim" is exposed on the API
    Then the filter "customerName" should be available and its type should be "string"
    Then the filter "status" should be available and its type should be "string"
    Then the filter "type" should be available and its type should be "string"
    Then the filter "equipmentModel" should be available and its type should be "string"
    Then the filter "serialNumber" should be available and its type should be "string"
    Then the filter "equipmentLocation" should be available and its type should be "string"
    Then the filter "order[id]" should be available and its type should be "string"
    Then the filter "order[status]" should be available and its type should be "string"
    Then the filter "order[claimDate]" should be available and its type should be "string"
    Then the filter "order[type]" should be available and its type should be "string"
    Then the filter "order[equipmentModel]" should be available and its type should be "string"
    Then the filter "order[serialNumber]" should be available and its type should be "string"
    Then the filter "order[equipmentLocation]" should be available and its type should be "string"

  Scenario: A user can use the simple search on warranty claims
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/warranty_claims?q=T85401"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 2
    And the JSON node "hydra:member[0].serialNumber" should be equal to "T85401"
    And the JSON should be valid according to the schema "tests/LegacyBundle/fixtures/json/warranty_claim/schemas/warranty_claims_requests.json"
