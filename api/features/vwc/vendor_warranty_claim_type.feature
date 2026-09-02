Feature: Test Vendor Warranty Claim Type Entity

  Scenario: Request all vendor warranty claim types without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claim_types"
    Then the response status code should be 401

  Scenario: Request a single vendor warranty claim type without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claim_types/1"
    Then the response status code should be 401

  Scenario: Request all vendor warranty claim types as intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claim_types"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim_type/schemas/vendor_warranty_claim_types.json"

  Scenario: Request a vendor warranty claim type as intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claim_types/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim_type/schemas/vendor_warranty_claim_type.json"

  Scenario: Request all vendor warranty claim types as extranet user should not be permitted
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claim_types"
    Then the response status code should be 403

  Scenario: Request a vendor warranty claim type as extranet user should not be permitted
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claim_types/1"
    Then the response status code should be 403

  Scenario: Request all vendor warranty claim types as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claim_types"
    Then the response status code should be 403

  Scenario: Request a vendor warranty claim type as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claim_types/1"
    Then the response status code should be 403