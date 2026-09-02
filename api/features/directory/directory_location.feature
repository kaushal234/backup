Feature: Test Directory Location API

  Scenario: Resource should be accessible to intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/locations"
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/locations/1"
    Then the response status code should be 200

  Scenario: Resource should not be accessible to extranet user
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/locations"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/locations/1"
    Then the response status code should be 403

  Scenario: vendor user should be allowed to get collection of locations and item
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/locations"
    Then the response status code should be 200
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/locations/1"
    Then the response status code should be 200

  Scenario: Request all locations
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/locations"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_location/schemas/directory_locations.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Directory\Location" is exposed on the API
    Then the filter "order[name]" should be available and its type should be "string"
    And the filter "capability.sso" should be available and its type should be "bool"
    And the filter "capability.factory" should be available and its type should be "bool"
    And the filter "capability.warehouse" should be available and its type should be "bool"
    And the filter "capability.sparePartsHub" should be available and its type should be "bool"
    And the filter "capability.headQuarter" should be available and its type should be "bool"
    And the filter "state.public" should be available and its type should be "bool"
    And the filter "state.hidden" should be available and its type should be "bool"
    And the filter "state.disabled" should be available and its type should be "bool"
    And the filter "name" should be available and its type should be "string"
    And the filter "network" should be available and its type should be "string"
    And the filter "erp" should be available and its type should be "int"
    And the filter "juridicalLocation" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "id" should be available and its type should be "int"
    And the filter "exists[contact.serviceHubTelephone]" should be available and its type should be "bool"
    And the filter "businessUnit.region.subDivision.division.name" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"

  Scenario: Request locations for list
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/locations?normalizationGroupsOverride[]=location_public"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_location/schemas/directory_location_publics.json"

  Scenario: Request a given location
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/locations/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_location/schemas/directory_location.json"

  Scenario: Update a given location with permission OK and an invalid phone number
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    # Testing the phone cleaning listener response
    When I send a "PUT" request to "/locations/1" with the body "tests/fixtures/json/directory_location/dummies/put_wrong.json"
    Then the response status code should be 422

  Scenario: Update a given location with permission OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/locations/1" with the body "tests/fixtures/json/directory_location/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_location/schemas/directory_location.json"
    # Testing the phone cleaning listener
    And the JSON node "contact.telephone" should be equal to the string "+852 2692 2181"
    And the JSON node "erpInLN" should be false
    And the JSON node "publicWebsite" should be equal to the string "http://www.alvest.com"

  Scenario: Public website of location should be a valid URL
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/locations/1" with body:
    """
    {
      "publicWebsite": "toto"
    }
    """
    Then the response status code should be 422
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/locations/1" with body:
    """
    {
      "name": "test location",
      "company": "Acme",
      "role": "SSO",
      "capability": {
        "factory": true,
        "warehouse": true,
        "sparePartsHub": true,
        "serviceHub": true,
        "headQuarter": true
      },
      "contact": {
        "telephone": "+33 1 23 45 67 89",
        "fax": "+33 1 23 45 67 90",
        "sparePartsEmail": "spart@acme.com",
        "partsCustomerSupportEmail": "partSupport@acme.com",
        "sparePartsTelephone": "+33 1 23 45 67 92",
        "sparePartsFax": "+33 1 23 45 67 93",
        "serviceHubEmail": "hub@acme.com",
        "serviceHubTelephone": "+33 1 23 45 67 95"
      },
      "state": {
        "public": true,
        "hidden": false,
        "disabled": false
      },
      "domain": "acme.com",
      "internalNetworkAddress": "192.168.0.0/16",
      "address": {
        "street1": "Avenue du pré",
        "street2": "BP 213456",
        "postalCode": "75001",
        "city": "Paris",
        "town": null,
        "state": null,
        "country": "FR"
      },
      "erp": 600,
      "erpInLN": true,
      "businessUnit": "/business_units/1",
      "juridicalLocation": "/juridical_locations/1",
      "representative": "/users/11",
      "currency": "/finance/currencies/6",
      "timeZone": "Europe/Paris",
      "network": "/networks/1",
      "publicWebsite": "toto"
    }
    """
    Then the response status code should be 422

  Scenario: Update a given location with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/locations/1" with the body "tests/fixtures/json/directory_location/dummies/put.json"
    Then the response status code should be 403

  Scenario: Create a location with permission OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/locations" with the body "tests/fixtures/json/directory_location/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_location/schemas/directory_location.json"
    And the JSON node "erpInLN" should be true
    And the JSON node "publicWebsite" should be equal to the string "http://www.tld-gse.com"

  Scenario: Create a location without permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/locations" with the body "tests/fixtures/json/directory_location/dummies/post.json"
    Then the response status code should be 403

  Scenario: Create a SSO without currency
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/locations" with the body "tests/fixtures/json/directory_location/dummies/post_sso_no_currency.json"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "currency: Currency is mandatory for SSO"
    And the JSON node "violations[0].propertyPath" should be equal to "currency"
    And the JSON node "violations[0].message" should contain "Currency is mandatory for SSO"

  Scenario: Delete a location with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/locations/2"
    Then the response status code should be 403

  Scenario: Delete a location with permission OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/locations/22"
    Then the response status code should be 204
