Feature: Test the strict search filter

  Scenario: Search an acronym by acronym
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acronyms?acronym=MIS"
    Then the response status code should be 200
    # /acronyms/1 is MIS
    And the JSON node "hydra:totalItems" should be equal to "1"

  Scenario: Search an acronym with an invalid filter
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acronyms?invalid.nested[filter.name][complicated]=MIS"
    Then the response should be an error stating 'The filter "invalid.nested[filter.name][complicated]" is not available for the resource "App\Entity\Acronym".'

  Scenario: Search an acronym by category
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acronyms?categories[]=/acronym_categories/5"
    Then the response status code should be 200
    # /acronyms/2 is PDC is linked to /acronym_categories/5 and /acronym_categories/6
    And the JSON node "hydra:totalItems" should be equal to "1"

  Scenario: Search on numeric key array is allowed
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acronyms?categories[156]=/acronym_categories/5"
    Then the response status code should be 200
    # /acronyms/2 is PDC is linked to /acronym_categories/5 and /acronym_categories/6
    And the JSON node "hydra:totalItems" should be equal to "1"

  Scenario: Search embedded property on locations
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/locations?capability.sso=1"
    Then the response status code should be 200

  Scenario: Page parameter is not filtered
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/locations?page=1"
    Then the response status code should be 200

  Scenario: Items number per page parameter is not filtered
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/locations?itemsPerPage=1"
    Then the response status code should be 200
