Feature: Test Vendor Warranty Claim Threshold Entity

  Scenario: Request all vendor warranty claim thresholds without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claim_thresholds"
    Then the response status code should be 401

  Scenario: Request all vendor warranty claims thresholds as extranet user should not be permitted
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claim_thresholds"
    Then the response status code should be 403

  Scenario: Request all vendor warranty claims thresholds as extranet user should not be permitted
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claim_thresholds"
    Then the response status code should be 403

  Scenario: Request vendor warranty claim thresholds item as intranet user is not permitted
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claim_thresholds/1"
    Then the response status code should be 404

  Scenario: Request all vendor warranty claim thresholds as intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claim_thresholds"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim_threshold/schemas/vendor_warranty_claim_thresholds.json"

  Scenario: As user user-basic I can not create Vendor Warranty Claim threshold
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/vendor_warranty_claim_thresholds" with body:
    """
    {
      "location": "/locations/12",
      "threshold": 1000
    }
    """
    Then the response status code should be 403

  Scenario: As user superUser (same for MOO) I can not create Vendor Warranty Claims Threshold on a location already used
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/vendor_warranty_claim_thresholds" with body:
    """
    {
      "location": "/locations/36",
      "threshold": 1000
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "location: This value is already used."

  Scenario: As user superUser (same for the MOO) I can create Vendor Warranty Claims Threshold on a location not already used
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/vendor_warranty_claim_thresholds" with body:
    """
    {
      "location": "/locations/12",
      "threshold": 1000
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim_threshold/schemas/vendor_warranty_claim_threshold.json"
    And the JSON node "location.@id" should be equal to the string "/locations/12"
    And the JSON node "threshold" should be equal to 1000

  Scenario: As user qam I can not create Vendor Warranty Claims Threshold on a location where i am not the qam
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/vendor_warranty_claim_thresholds" with body:
    """
    {
      "location": "/locations/23",
      "threshold": 1000
    }
    """
    Then the response status code should be 403

  Scenario: As user qam I can create Vendor Warranty Claims Threshold on a location where i am the qam
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/vendor_warranty_claim_thresholds" with body:
    """
    {
      "location": "/locations/29",
      "threshold": 1000
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim_threshold/schemas/vendor_warranty_claim_threshold.json"
    And the JSON node "location.@id" should be equal to the string "/locations/29"
    And the JSON node "threshold" should be equal to 1000

  Scenario: As user qam I can edit Vendor Warranty Claims Threshold on a location where i am the qam
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/vendor_warranty_claim_thresholds/4" with body:
    """
    {
      "location": "/locations/29",
      "threshold": 1500
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim_threshold/schemas/vendor_warranty_claim_threshold.json"
    And the JSON node "location.@id" should be equal to the string "/locations/29"
    And the JSON node "threshold" should be equal to 1500

  Scenario: As user qam I can not edit Vendor Warranty Claims Threshold on a location where i am not the qam
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/vendor_warranty_claim_thresholds/3" with body:
    """
    {
      "location": "/locations/23",
      "threshold": 1500
    }
    """
    Then the response status code should be 403

  Scenario: As user superUser (same for the MOO) I can edit Vendor Warranty Claims Threshold
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/vendor_warranty_claim_thresholds/3" with body:
    """
    {
      "location": "/locations/31",
      "threshold": 1500
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_warranty_claim_threshold/schemas/vendor_warranty_claim_threshold.json"
    And the JSON node "location.@id" should be equal to the string "/locations/31"
    And the JSON node "threshold" should be equal to 1500

  Scenario: As user user-basic@tld.fr I can not edit Vendor Warranty Claims
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/vendor_warranty_claim_thresholds/3" with body:
    """
    {
      "location": "/locations/31",
      "threshold": 1000
    }
    """
    Then the response status code should be 403

  Scenario: As user superUser (same for the MOO) I can not edit Vendor Warranty Claims Threshold location if location is already used
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/vendor_warranty_claim_thresholds/3" with body:
    """
    {
      "location": "/locations/29",
      "threshold": 1500
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "location: This value is already used."
