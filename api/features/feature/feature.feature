Feature: Test Features API

  Scenario: Call with right privileges
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/business_units/3" with body:
    """
    {
      "name": "this is my other business unit"
    }
    """
    Then the response status code should be 200

  Scenario: Call with bad privileges
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/business_units/3" with body:
    """
    {
      "name": "this is my other business unit"
    }
    """
    Then the response status code should be 403

  Scenario: Check grant with right privileges
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/grants?resource=/business_units/1&attributes=FEATURE_BUSINESS_UNIT_WRITE"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "grant": "GRANTED"
    }
    """

  Scenario: Check grant throws errors
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/grants"
    Then the response should be an error stating "Missing filter attributes."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/grants?attributes[]=FOO&attributes[]=BAR"
    Then the response should be an error stating "Passing several attribute is not permitted, you should make several api calls instead."

  Scenario: Check grant with right privileges
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/grants?resource=/business_units/1&attributes=FEATURE_BUSINESS_UNIT_WRITE"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "grant": "DENIED"
    }
    """

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Feature" should only be available for intranet user

  Scenario: Fetch all features
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/features"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/features/schemas/features.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Feature" is exposed on the API
    Then the filter "order[name]" should be available and its type should be "string"
    And the filter "groups.acls.user" should be available and its type should be "string"
    And the filter "groups.acls.location" should be available and its type should be "string"
    And the filter "groups" should be available and its type should be "string"

  Scenario: Fetch all features with restricted serialization
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/features?normalization_groups_override[]=feature_list"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/features/schemas/features_override.json"

  Scenario: Fetch a single feature
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/features/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/features/schemas/feature.json"
