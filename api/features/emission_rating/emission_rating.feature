Feature: Test Emission ratings API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\EmissionRating" should only be available for intranet user

  Scenario: Request all non-obsolete emission ratings
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/emission_ratings"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/emission_rating/schemas/emission_ratings.json"
    And the JSON node "hydra:totalItems" should be equal to 4

  Scenario: Request all emission ratings
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/emission_ratings?normalizationGroups[]=show_obsolete"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/emission_rating/schemas/emission_ratings.json"
    And the JSON node "hydra:totalItems" should be equal to 5

  Scenario: Request a single emission rating
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/emission_ratings/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/emission_rating/schemas/emission_rating.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\EmissionRating" is exposed on the API
    Then the filter "order[id]" should be available and its type should be "string"
    Then the filter "name" should be available and its type should be "string"

  Scenario: Update an emission rating should not be possible without elevated privilege
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/emission_ratings/1" with the body "tests/fixtures/json/emission_rating/dummies/put.json"
    Then the response status code should be 403

  Scenario: Update an emission rating should be possible with elevated privilege
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/emission_ratings/1" with the body "tests/fixtures/json/emission_rating/dummies/put.json"
    Then the response status code should be 200
    Then the JSON node "name" should be equal to the string "Tier 1"
    Then the JSON node "obsolete" should be true

  Scenario: Create an emission rating should not be possible without elevated privilege
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/emission_ratings" with the body "tests/fixtures/json/emission_rating/dummies/post.json"
    Then the response status code should be 403

  Scenario: Create an emission rating should possible with elevated privilege
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/emission_ratings" with the body "tests/fixtures/json/emission_rating/dummies/post.json"
    Then the response status code should be 201
    Then the JSON node "name" should be equal to the string "Tier evenu ?"
    Then the JSON node "obsolete" should be false

  Scenario: Update the emission rating name should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/emission_ratings/5" with body:
    """
    {
        "name": "hardcoded values are evil"
    }
    """
    Then the response status code should be 200
    Then the JSON node "name" should be equal to the string "iBS"
