Feature: Test Power BI Report API

  Scenario: Filters are declared on resource
    Given the class "App\Entity\PowerBI\Report" is exposed on the API
    Then the filter "title" should be available and its type should be "string"
    And the filter "description" should be available and its type should be "string"
    And the filter "category" should be available and its type should be "string"
    And the filter "subCategories" should be available and its type should be "string"
    And the filter "id" should be available and its type should be "int"
    And the filter "order[category]" should be available and its type should be "string"
    And the filter "order[description]" should be available and its type should be "string"
    And the filter "order[title]" should be available and its type should be "string"
    And the filter "order[id]" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"

  Scenario: User with no access to power BI reports
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/power_bi/reports"
    Then the response status code should be 403

  Scenario: Request all power bi reports
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/power_bi/reports"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/power_bi/schemas/reports.json"

  Scenario: Request power bi reports with filters
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/power_bi/reports?description=Forecast"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/power_bi/schemas/reports.json"
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0].description" should be equal to the string "Inventory Forecast"
    And the JSON node "hydra:member[0].category" should be equal to the string "MATERIALS"
    And the JSON node "hydra:member[0].powerBiUuid" should be equal to the string "b6105b5b-3441-4cc5-8a4e-54361a9d964b"

  Scenario: I shouldn't see restricted reports in the collection
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/power_bi/reports?description=New part numbers creation in LN"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/power_bi/schemas/reports.json"
    And the JSON node "hydra:totalItems" should be equal to 0

  Scenario: I should see restricted reports in the collection with power bi role
    Given I authenticate as the intranet user "user-powerbi@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/power_bi/reports?description=New part numbers creation in LN"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/power_bi/schemas/reports.json"
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0].description" should be equal to the string "New part numbers creation in LN"
    And the JSON node "hydra:member[0].category" should be equal to the string "ENGINEERING"
    And the JSON node "hydra:member[0].powerBiUuid" should be equal to the string "8f5a692d-eab5-4b76-8a45-ac54783fffdc"

  Scenario: Request one power bi report
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/power_bi/reports/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/power_bi/schemas/report.json"
    And the JSON node "@id" should be equal to the string "/power_bi/reports/2"
    And the JSON node "id" should be equal to the string "2"
    And the JSON node "description" should be equal to the string "Inventory by Site"
    And the JSON node "category" should be equal to the string "MATERIALS"
    And the JSON node "powerBiUuid" should be equal to the string "22439092-57de-4442-aee5-85c028283283"

  Scenario: Report with restricted access
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/power_bi/reports/3"
    Then the response status code should be 403

  Scenario: Report with restricted access should be accessible for power bi
    Given I authenticate as the intranet user "user-powerbi@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/power_bi/reports/3"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/power_bi/schemas/report.json"
    And the JSON node "@id" should be equal to the string "/power_bi/reports/3"
    And the JSON node "id" should be equal to the string "3"
    And the JSON node "description" should be equal to the string "New part numbers creation in LN"
    And the JSON node "category" should be equal to the string "ENGINEERING"
    And the JSON node "powerBiUuid" should be equal to the string "8f5a692d-eab5-4b76-8a45-ac54783fffdc"

  Scenario: As a superuser, I can create a power bi report
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/power_bi/reports" with body:
    """
    {
      "title": "test title",
      "description": "Test new report",
      "category": "ENGINEERING",
      "powerBiUuid": "3ceca246-e496-4bde-be8f-a30a14a2318a"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/power_bi/schemas/report.json"
    And the JSON node "title" should be equal to the string "test title"
    And the JSON node "description" should be equal to the string "Test new report"
    And the JSON node "category" should be equal to the string "ENGINEERING"
    And the JSON node "powerBiUuid" should be equal to the string "3ceca246-e496-4bde-be8f-a30a14a2318a"

  Scenario: As a superuser, I can update a power bi report
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/power_bi/reports/6" with body:
    """
    {
      "title": "title update",
      "description": "description update",
      "category": "FINANCE",
      "subCategories": ["SSO CONTROLLING"],
      "powerBiUuid": "3ceca246-e496-4bde-be8f-a30a14a2317a"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/power_bi/schemas/report.json"
    And the JSON node "title" should be equal to the string "title update"
    And the JSON node "description" should be equal to the string "description update"
    And the JSON node "category" should be equal to the string "FINANCE"
    And the JSON node "subCategories[0]" should be equal to the string "SSO CONTROLLING"
    And the JSON node "powerBiUuid" should be equal to the string "3ceca246-e496-4bde-be8f-a30a14a2317a"

  Scenario: As a superuser, I can remove a power bi report
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/power_bi/reports/7"
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/power_bi/reports/7"
    Then the response status code should be 404

  Scenario: As a basic user, I am not allow to create a power bi report
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/power_bi/reports"
    Then the response status code should be 403

  Scenario: As a basic user, I am not allow to update a power bi report
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/power_bi/reports/1"
    Then the response status code should be 403

  Scenario: As a basic user, I am not allow to delete a power bi report
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/power_bi/reports/1"
    Then the response status code should be 403

  Scenario: As a superuser, I can't create a report with invalid Uuid 1
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/power_bi/reports" with body:
    """
    {
      "title": "test invalid",
      "description": "test invalid",
      "category": "ENGINEERING",
      "powerBiUuid": "3ceca246-e496-4bde-be8f-a30a14a2318"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "powerBiUuid"
    And the JSON node "violations[0].message" should contain "This value is not a valid UUID."

  Scenario: As a superuser, I can't create a report with invalid Uuid 2
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/power_bi/reports" with body:
    """
    {
      "title": "test invalid",
      "description": "test invalid",
      "category": "ENGINEERING",
      "powerBiUuid": "3ceca246-e496-4bde-5e8f-a30a14a23189"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "powerBiUuid"
    And the JSON node "violations[0].message" should contain "This value is not a valid UUID."