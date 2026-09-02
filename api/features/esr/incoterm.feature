Feature: Test Incoterm API

  Scenario: Request all Equipment Shipping Records
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/incoterms"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/incoterm/incoterms.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\Incoterm" is exposed on the API
    Then the filter "order[code]" should be available and its type should be "string"

  Scenario: Filter on 'EXW' return 200
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/incoterms?code=EXW"
    Then the response status code should be 200
