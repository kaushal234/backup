Feature: Parts can be fetched through the Sage API

  Scenario: Request a Sage item
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sage/sage_parts/1008905-P5"
    Then the response status code should be 200
    And the JSON node "alvestId" should be equal to the string "1008905-P5"
    And the JSON node "sageId" should be equal to the string "442251SEAL"
    And the JSON node "description" should be equal to the string "SEAL, GREASE 42 X 65 X 10"
    And the JSON node "sageUID" should be equal to "109305"
    And the JSON node "locations" should have 27 elements
    And the JSON node "locations[0].name" should be equal to "100001"
    And the JSON node "locations[0].onHand.unit" should be equal to the string "EACH"
    And the JSON node "locations[0].onHand.quantity" should be equal to the number 0
    And the JSON node "locations[0].onOrder.unit" should be equal to the string "EACH"
    And the JSON node "locations[0].onOrder.quantity" should be equal to the number 0
    And the JSON node "locations[0].unitPrice.currency" should be equal to the string "USD"
    And the JSON node "locations[0].unitPrice.price" should be equal to the number 5.27
    And the JSON should be valid according to the schema "tests/fixtures/json/sage/schemas/sage.json"

  Scenario: Request a not existing Sage item
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sage/sage_parts/not-exist"
    Then the response status code should be 404