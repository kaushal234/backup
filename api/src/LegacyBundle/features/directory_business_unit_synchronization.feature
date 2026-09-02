Feature: Test business unit double write

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create a business unit in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/business_units" with body:
    """
    {
      "name": "Test double write",
      "representative": "/users/11",
      "location": "/locations/13",
      "region": "/regions/2"
    }
    """
    Then the response status code should be 201
    And the column "business_unit" from the "locations" legacy table has been updated with string "Test double write"

  Scenario: Update a business unit in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/business_units/3" with body:
    """
    {
      "name": "updated",
      "representative": "/users/11",
      "location": "/locations/22",
      "region": "/regions/4"
    }
    """
    Then the response status code should be 200
    And the column "business_unit" from the "locations" legacy table has been updated with string "updated"
    And the column "repid" from the "locations" legacy table has been updated
    And the column "region_id" from the "locations" legacy table has been updated with integer 9


