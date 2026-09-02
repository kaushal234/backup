Feature: Test Application Entity

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\MIS\TroubleTicket\Application" should only be available for intranet user

  Scenario: Request all applications as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/applications"
    Then the response status code should be 403

  Scenario: Request a single application as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/applications/1"
    Then the response status code should be 403

  Scenario: Filters are declared on resource
    Given the class "App\Entity\MIS\TroubleTicket\Application" is exposed on the API
    Then the filter "order[name]" should be available and its type should be "string"

  Scenario: Request all applications as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/applications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/application/schemas/applications.json"

  Scenario: Request a single application as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/applications/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/application/schemas/application.json"

  Scenario: Update an application should not be possible as user superuser
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/applications/1" with body:
    """
    {}
    """
    Then the response status code should be 403

  Scenario: Update an application should be possible as user mism
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/applications/1" with body:
    """
    {
      "name": "Test Application"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/application/schemas/application.json"
    And the JSON node "name" should be equal to the string "Test Application"

  Scenario: Create an application should not be possible for user superuser
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/applications" with body:
    """
    {}
    """
    Then the response status code should be 403

  Scenario: Create an application should be possible for user mism but not without name, or already existing name
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/applications" with body:
   """
    {
      "name": ""
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to the string "name"
    And the JSON node "violations[0].message" should be equal to the string "This value should not be blank."
    And the JSON node "violations[1].propertyPath" should be equal to the string "jiraProjectId"
    And the JSON node "violations[1].message" should be equal to the string "This value should not be blank."
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/applications" with body:
   """
    {
      "name": "Test Application",
      "jiraProjectId": 0
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to the string "name"
    And the JSON node "violations[0].message" should be equal to the string "This value is already used."
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/applications" with body:
   """
    {
      "name": "New Application",
      "jiraProjectId": 123654
    }
    """
    Then the response status code should be 201
    And the JSON node "name" should be equal to the string "New Application"
    And the JSON node "jiraProjectId" should be equal to the number 123654
