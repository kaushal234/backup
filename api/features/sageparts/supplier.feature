Feature: Parts can be fetched through the Sage P21 API

  Scenario: Request Sage suppliers
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sageparts/suppliers"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "At least one filter is needed."

  Scenario: Request a Sage supplier
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sageparts/suppliers/XXX"
    Then the response status code should be 404

  Scenario: Request Sage suppliers with filter
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sageparts/suppliers?contains[name]=AIRLINES"
    Then the response status code should be 200
