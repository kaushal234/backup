Feature: Vendor item costs can be fetched from Snowflake through the API

  Scenario: Request vendor item costs without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/snowflake/vendor_item_costs"
    Then the response status code should be 401

  Scenario: Request vendor item costs filtered by site and pagination
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/snowflake/vendor_item_costs?site=300&page=1&itemsPerPage=10&order[item]=asc"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be superior to the number 10
    And the JSON node "hydra:member" should have 10 elements
    And the JSON node "hydra:member[0].site" should be equal to "300"
    And the JSON node "hydra:member[0].item" should be equal to "!1029TEMP"
    And the JSON node "hydra:member[0].description" should be equal to "SEAL"
    And the JSON node "hydra:member[0].currency" should be equal to "USD"
    And the JSON node "hydra:member[0].unit" should be equal to "EA"
    And the JSON node "hydra:member[0].price" should be equal to the number 37.49
