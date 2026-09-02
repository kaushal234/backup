Feature: Test Vendor Warranty Claim status Entity

  Scenario: Request all vendor warranty claim statuses without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claim_statuses"
    Then the response status code should be 401

  Scenario: Request a single vendor warranty claim type without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claim_statuses/1"
    Then the response status code should be 401

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Purchasing\VendorWarrantyClaimStatus" is exposed on the API
    Then the filter "order[position]" should be available and its type should be "string"
    Then the filter "name" should be available and its type should be "string"

  Scenario: Request all vendor warranty claim statuses as intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claim_statuses"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim_status/schemas/vendor_warranty_claim_statuses.json"

  Scenario: Request a vendor warranty claim status as intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claim_statuses/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim_status/schemas/vendor_warranty_claim_status.json"

  Scenario: Request all vendor warranty claim statuses as extranet user should not be permitted
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claim_statuses"
    Then the response status code should be 403

  Scenario: Request a vendor warranty claim status as extranet user should not be permitted
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claim_statuses/1"
    Then the response status code should be 403

  Scenario: Request all vendor warranty claim statuses as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claim_statuses"
    Then the response status code should be 403

  Scenario: Request a vendor warranty claim status as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claim_statuses/1"
    Then the response status code should be 403
