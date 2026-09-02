Feature: Events can be created and edited using the API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\HumanResources\Event" should only be available for intranet user

  Scenario: Filters are declared on resource
    Given the class "App\Entity\HumanResources\Event" is exposed on the API
    Then the filter "country" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"
    And the filter "order[name]" should be available and its type should be "string"
    And the filter "order[startedAt]" should be available and its type should be "string"
    And the filter "order[endedAt]" should be available and its type should be "string"
    And the filter "order[country.name]" should be available and its type should be "string"
    And the filter "startedAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "startedAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "endedAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "endedAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "columns" should be available and its type should be "string"

  Scenario: User basic should be able to list events
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/events"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/event/schemas/events.json"

  Scenario: Export XLS file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "events?columns=id,name&q=nationale"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Name |
    And the xlsx file should have 2 lines

  Scenario: User basic should be able to fetch an event
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/events/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/event/schemas/event.json"

  Scenario: User basic should not be allowed to create an event
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/events" with body:
    """
    {
      "name": "Carême",
      "startedAt": "2025-03-14",
      "endedAt": "2025-04-12",
      "country": "/countries/10",
      "state": "Texas",
      "dayOff": true
    }
    """
    Then the response status code should be 403

  Scenario: User HR should be allowed to create an event
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/events" with body:
    """
    {
      "name": "Carême",
      "startedAt": "2025-03-14",
      "endedAt": "2025-04-12",
      "country": "/countries/10",
      "state": "Texas",
      "dayOff": true
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/event/schemas/event.json"
    And the JSON node "name" should be equal to the string "Carême"
    And the JSON node "country.@id" should be equal to the string "/countries/10"
    And the JSON node "state" should be equal to the string "Texas"
    And the JSON node "startedAt" should be equal to the string "2025-03-14T00:00:00-04:00"
    And the JSON node "endedAt" should be equal to the string "2025-04-12T00:00:00-04:00"
    And the JSON node "dayOff" should be true

  Scenario: User HR should not be allowed to create an event with end date before started date
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/events" with body:
    """
    {
      "name": "Carême",
      "startedAt": "2025-03-14",
      "endedAt": "2024-04-12",
      "country": "/countries/10",
      "state": "Texas",
      "dayOff": true

    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string "This value should be greater than or equal to Mar 14, 2025, 12:00 AM."

  Scenario: User HR should not be allowed to create an event with a blank name
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/events" with body:
    """
    {
      "name": "",
      "startedAt": "2025-03-14",
      "endedAt": "2024-04-12",
      "country": "/countries/10",
      "state": "Texas",
      "dayOff": true

    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string "This value should not be blank."

  Scenario: User basic should not be allowed to update an event
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/events/3" with body:
    """
    {
      "name": "Carême",
      "startedAt": "2025-03-14",
      "endedAt": "2025-04-12",
      "country": "/countries/11",
      "state": "Arizona",
      "dayOff": true
    }
    """
    Then the response status code should be 403

  Scenario: User HR should be allowed to edit an event
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/events/3" with body:
    """
    {
      "name": "Ramadan",
      "startedAt": "2025-03-15",
      "endedAt": "2025-04-13",
      "country": "/countries/11",
      "state": "Arizona",
      "dayOff": true
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/event/schemas/event.json"
    And the JSON node "name" should be equal to the string "Ramadan"
    And the JSON node "country.@id" should be equal to the string "/countries/11"
    And the JSON node "state" should be equal to the string "Arizona"
    And the JSON node "startedAt" should be equal to the string "2025-03-15T00:00:00-04:00"
    And the JSON node "endedAt" should be equal to the string "2025-04-13T00:00:00-04:00"
    And the JSON node "dayOff" should be true

  Scenario: User basic should not be able to delete an event
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "DELETE" request to "/events/3"
    Then the response status code should be 403

  Scenario: User HR should be able to delete an event
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "DELETE" request to "/events/3"
    Then the response status code should be 204
