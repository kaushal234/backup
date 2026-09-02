Feature: Test Supplier entity

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Purchasing\Supplier\Supplier" should only be available for intranet user

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Purchasing\Supplier\Supplier" is exposed on the API
    And the filter "name" should be available and its type should be "string"
    And the filter "code" should be available and its type should be "string"
    And the filter "buyFrom.buyer" should be available and its type should be "string"
    And the filter "location" should be available and its type should be "string"
    And the filter "country" should be available and its type should be "string"
    And the filter "status" should be available and its type should be "string"
    And the filter "currency" should be available and its type should be "string"
    And the filter "buyFrom.location" should be available and its type should be "string"
    And the filter "order[name]" should be available and its type should be "string"
    And the filter "order[code]" should be available and its type should be "string"
    And the filter "order[location.name]" should be available and its type should be "string"
    And the filter "order[country.name]" should be available and its type should be "string"
    And the filter "order[status]" should be available and its type should be "string"
    And the filter "order[currency.name]" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"

  Scenario: Resource should be accessible for basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/suppliers"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier/schemas/suppliers.json"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/suppliers/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier/schemas/supplier.json"

  Scenario: Create a supplier should not be possible for superuser
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/suppliers" with body:
    """
    {
      "name": "AMAZON",
      "code": "AMA001"
    }
    """
    Then the response status code should be 403

  Scenario: Create a supplier should be possible for authorized application ION
    Given I authenticate as the authorized application "PIO"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/suppliers" with body:
    """
    {
      "name": "AMAZON",
      "code": "AMA001"
    }
    """
    Then the response status code should be 403
    Given I authenticate as the authorized application "ION"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/suppliers" with body:
    """
    {
      "name": "AMAZON",
      "code": "AMA001",
      "masterBusinessUnit": "680",
      "country": "FR",
      "status": "ACTIVE",
      "currency": "usd",
      "buyFrom": [
        {
          "locationCode": "680",
          "buyerId": 60
        }
      ]
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier/schemas/supplier.json"
    And the JSON node "name" should be equal to the string "AMAZON"
    And the JSON node "code" should be equal to the string "AMA001"
    And the JSON node "status" should be equal to the string "ACTIVE"
    And the JSON node "currency.@id" should be equal to the string "/finance/currencies/1"
    And the JSON node "buyFrom[0].buyer.@id" should be equal to the string "/people/60"
    And the JSON node "buyFrom[0].location.@id" should be equal to the string "/locations/1"
    And the JSON node "location.@id" should be equal to the string "/locations/1"
    And the JSON node "country.@id" should be equal to the string "/countries/1"

  Scenario: Update a supplier should be possible for authorized application ION
    Given I authenticate as the authorized application "ION"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/suppliers" with body:
    """
    {
      "name": "SUPPLIER",
      "code": "9ADHFRA",
      "masterBusinessUnit": "420",
      "country": "KD",
      "status": "INACTIVE",
      "currency": "eur",
      "buyFrom": [
        {
          "locationCode": "220",
          "buyerId": 31
        }
      ]
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier/schemas/supplier.json"
    And the JSON node "@id" should be equal to the string "/purchasing/suppliers/2"
    And the JSON node "name" should be equal to the string "SUPPLIER"
    And the JSON node "code" should be equal to the string "9ADHFRA"
    And the JSON node "status" should be equal to the string "INACTIVE"
    And the JSON node "currency.@id" should be equal to the string "/finance/currencies/6"
    And the JSON node "buyFrom[0].buyer.@id" should be equal to the string "/people/31"
    And the JSON node "buyFrom[0].location.@id" should be equal to the string "/locations/15"
    And the JSON node "location.@id" should be equal to the string "/locations/31"
    And the JSON node "country.@id" should be equal to the string "/countries/11"

  Scenario: Put request is not allowed
    Given I authenticate as the authorized application "ION"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/suppliers"
    Then the response status code should be 405

  Scenario: Delete request is not allowed
    Given I authenticate as the authorized application "ION"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/purchasing/suppliers"
    Then the response status code should be 405