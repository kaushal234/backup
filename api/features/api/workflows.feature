Feature: Test workflows
  Scenario: Workflows should not be accessible to extranet user
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/workflows/resource=/sales/demos/1;name="
    Then the response status code should be 403

  Scenario: Request an invalid workflow with an invalid resource
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/scrogneugneu;name="
    Then the response status code should be 404
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/parts/quotations/1;name=foobar"
    Then the response status code should be 404

  Scenario: Request all places of a valid workflow config of a resource
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/places/mis/trouble_tickets"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to "11"
    And the JSON node "hydra:member[0]" should be equal to the string "PENDING"

  Scenario: Request all places of a non existing workflow
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/places/equipment_records"
    Then the response status code should be 404

  Scenario: Request all places of a non existing resource
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/places/not_existing"
    Then the response status code should be 404