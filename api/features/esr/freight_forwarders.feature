Feature: Test freight forwarders

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\FreightForwarder" should only be available for intranet user

  Scenario: Request all freight forwarders
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/freight_forwarders"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/freight_forwarder/schemas/freight_forwarders.json"

  Scenario: Request a single freight forwarder
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/freight_forwarders/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/freight_forwarder/schemas/freight_forwarder.json"

  Scenario: Create a freight forwarder should not be possible for user basic
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/freight_forwarders" with body:
    """
    {
      "name": "Geodis",
      "supplierNumber": "AMA0010",
      "language": "zh",
      "emails": ["test@geodis.fr","receiver@geodis.fr"]
    }
    """
    Then the response status code should be 403

  Scenario: Update a freight forwarder should not be possible for user basic
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/freight_forwarders/1" with body:
    """
    {
      "name": "Geoneuf",
      "supplierNumber": "AMA0010",
      "language": "zh",
      "emails": ["test@geoneuf.fr","receiver@geoneuf.fr"]
    }
    """
    Then the response status code should be 403

  Scenario: Delete a freight forwarder should not be possible for user basic
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/freight_forwarders/1"
    Then the response status code should be 403

  Scenario: Create a freight forwarder should follow constraint
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/freight_forwarders" with body:
    """
    {
      "name": "Geodis",
      "supplierNumber": "AMA0010",
      "language": "zh",
      "emails": ["test@geodis.fr","receiver@geodis.fr"]
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string "Supplier number and location are both mandatory if one is set."
    And the JSON node "violations[0].propertyPath" should be equal to the string "location"

  Scenario: Create a freight forwarder should be possible for user SA and user SAM
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/freight_forwarders" with body:
    """
    {
      "name": "Geodis",
      "supplierNumber": "AMA0010",
      "language": "zh",
      "location": "/locations/29",
      "emails": ["test@geodis.fr","receiver@geodis.fr"]
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/freight_forwarder/schemas/freight_forwarder.json"
    And the JSON node "name" should be equal to the string "AMAZON"
    And the JSON node "supplierNumber" should be equal to "AMA0010"
    And the JSON node "location.@id" should be equal to the string "/locations/29"
    And the JSON node "emails[0]" should be equal to the string "test@geodis.fr"
    And the JSON node "emails[1]" should be equal to the string "receiver@geodis.fr"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\FreightForwarder" is exposed on the API
    Then the filter "order[name]" should be available and its type should be "string"
    And the filter "location" should be available and its type should be "string"

  Scenario: Update a freight forwarder should be possible for user SA and user SAM
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/freight_forwarders/3" with body:
    """
    {
      "location": "/locations/30",
      "language": "en",
      "emails": ["test@geoneuf.fr","receiver@geoneuf.fr"]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/freight_forwarder/schemas/freight_forwarder.json"
    And the JSON node "location.@id" should be equal to the string "/locations/30"
    And the JSON node "emails[0]" should be equal to the string "test@geoneuf.fr"
    And the JSON node "emails[1]" should be equal to the string "receiver@geoneuf.fr"

  Scenario: Delete a freight forwarder should be possible for user SA
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/freight_forwarders/3"
    Then the response status code should be 204
