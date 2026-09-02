Feature: Test Planning daily limit

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\EquipmentShippingRecord\PlanningDailyLimit" is exposed on the API
    Then the filter "factory" should be available and its type should be "string"

  Scenario: Request all planning daily limit is possible for basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/planning_daily_limits"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/planning_daily_limit/schemas/planning_daily_limits.json"

  Scenario: Get a planning daily limit is not possible for not allowed user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/planning_daily_limits/1"
    Then the response status code should be 403

  Scenario: Get a planning daily limit should be possible for allowed user
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/planning_daily_limits/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/planning_daily_limit/schemas/planning_daily_limit.json"

  Scenario: Create a planning daily limit is not possible for not allowed user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/planning_daily_limits" with body:
    """
    {
      "days": 6,
      "factory": "/locations/30",
      "comment": "just 1 big camion"
    }
    """
    Then the response status code should be 403

  Scenario: Create a planning daily limit should be possible for allowed user
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/planning_daily_limits" with body:
    """
    {
      "days": 3,
      "factory": "/locations/29",
      "comment": "just 1 big camion"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/planning_daily_limit/schemas/planning_daily_limit.json"

  Scenario: Update a planning daily limit is not possible for not allowed user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/planning_daily_limits/3" with body:
    """
    {
      "days": 2,
      "comment": "just 1 big camion"
    }
    """
    Then the response status code should be 403

  Scenario: Update a planning daily limit should be possible for allowed user
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/planning_daily_limits/3" with body:
    """
    {
      "days": 2,
      "comment": "just 1 big camion"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/planning_daily_limit/schemas/planning_daily_limit.json"

  Scenario: I can not create an ESR Line if planning limit is reached
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/equipment_shipping_records/4" with body:
    """
    {
      "equipmentShippingRecordLines": [
        {
           "equipmentRecord": "/equipment_records/1",
           "estimatedPickUpDate": "2024-08-24T00:00:00-0400",
           "vesselLoadingDate": "2024-08-25T00:00:00-0400",
           "estimatedArrivalDate": "2024-08-25T00:00:00-0400",
           "actualArrivalDate": "2024-08-25T00:00:00-0400",
           "truckType": "ECHEC TRUCK"
        }
      ]
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string "The factory location_factory limit (2) has been reached for 2024-08-24, already 2 ESR line are booked and you try to add 1"
    And the JSON node "violations[0].propertyPath" should be equal to the string "equipmentShippingRecordLines"

  Scenario: As a basic user, I can upload a file to an equipment shipping record even if the limit is reached
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/equipment_shipping_records/4/files" with file "file" "file.doc"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: Delete a planning daily limit is not possible for not allowed user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/planning_daily_limits/2"
    Then the response status code should be 403

  Scenario: Delete a planning daily limit should be possible for allowed basic
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/planning_daily_limits/3"
    Then the response status code should be 204

  Scenario: Create a planning daily limit should follow constraint
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/planning_daily_limits" with body:
    """
    {
      "days": 6,
      "factory": "/locations/30"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string "The limit of this factory has been already set"
    And the JSON node "violations[0].propertyPath" should be equal to the string "factory"
