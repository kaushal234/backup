Feature: Content Negotiation

  Scenario: Accept Unknown mimetype returns a Not Acceptable response
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/en-fine-couche"
    When I send a "GET" request to "/locations"
    Then the response status code should be 406

  Scenario: Accept JSON mimetype returns a JSON format
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/locations"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/json; charset=utf-8"

  Scenario: Accept JSON-LD mimetype returns a JSON-LD format
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/locations"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"

  Scenario: Accept CSV mimetype returns a CSV format
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "text/csv"
    When I send a "GET" request to "/locations"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "text/csv; charset=utf-8"

  Scenario: Accept XLSX mimetype returns a XLSX format
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/locations"
    Then the response status code should be 406

