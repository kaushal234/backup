Feature: Test quality cleanliness API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Quality\LocationCleanliness" should only be available for intranet user

  Scenario: Request all notes
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/cleanliness"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/quality/location_cleanliness/schemas/location_cleanliness_collection.json"
    And the JSON node "hydra:member" should have 60 elements

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Quality\LocationCleanliness" is exposed on the API
    Then the filter "location" should be available and its type should be "string"
    And the filter "date[after]" should be available and its type should be "DateTimeInterface"
    And the filter "date[before]" should be available and its type should be "DateTimeInterface"
    And the filter "location.capability.factory" should be available and its type should be "bool"

  Scenario: Request a single note
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/cleanliness/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/quality/location_cleanliness/schemas/location_cleanliness_single.json"

  Scenario: I am not allowed to post a note
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/cleanliness" with the body "tests/fixtures/json/quality/location_cleanliness/dummies/post.json"
    Then the response status code should be 403

  Scenario: I can post a note
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/cleanliness" with the body "tests/fixtures/json/quality/location_cleanliness/dummies/post.json"
    Then the response status code should be 201
    And the JSON node "user.username" should be equal to "user-superuser@tld.fr"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/cleanliness?date[after]=2000-01-01&date[before]=2000-01-31"
    Then the JSON node "hydra:member" should have 1 element

  Scenario: I am not allowed to edit a note
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/cleanliness/1" with the body "tests/fixtures/json/quality/location_cleanliness/dummies/put.json"
    Then the response status code should be 403

  Scenario: I can edit a note
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/cleanliness/2" with the body "tests/fixtures/json/quality/location_cleanliness/dummies/put.json"
    Then the response status code should be 200
    And the JSON node "rating" should be equal to "4.72"
    And the JSON node "user.username" should be equal to "user-superuser@tld.fr"

  Scenario: I can edit a note as a QAM
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/cleanliness/2" with body:
    """
    {
      "@id": "/quality/cleanliness/2",
      "rating": 4.00
    }
    """
    Then the response status code should be 200
    And the JSON node "rating" should be equal to "4.00"
    And the JSON node "user.username" should be equal to "user-qam@tld.fr"

  Scenario: I can edit a note as a MPE
    Given I authenticate as the intranet user "user-mpe@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/cleanliness/2" with body:
    """
    {
      "@id": "/quality/cleanliness/2",
      "rating": 4.01
    }
    """
    Then the response status code should be 200
    And the JSON node "rating" should be equal to "4.01"
    And the JSON node "user.username" should be equal to "user-mpe@tld.fr"

  Scenario: I can edit a note as a COO
    Given I authenticate as the intranet user "user-coo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/cleanliness/2" with body:
    """
    {
      "@id": "/quality/cleanliness/2",
      "rating": 4.02
    }
    """
    Then the response status code should be 200
    And the JSON node "rating" should be equal to "4.02"
    And the JSON node "user.username" should be equal to "user-coo@tld.fr"

  Scenario: I can edit a note as a CMO
    Given I authenticate as the intranet user "user-cmo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/cleanliness/2" with body:
    """
    {
      "@id": "/quality/cleanliness/2",
      "rating": 4.03
    }
    """
    Then the response status code should be 200
    And the JSON node "rating" should be equal to "4.03"
    And the JSON node "user.username" should be equal to "user-cmo@tld.fr"

  Scenario: I can edit a note as a SPM
    Given I authenticate as the intranet user "user-spm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/cleanliness/2" with body:
    """
    {
      "@id": "/quality/cleanliness/2",
      "rating": 4.04
    }
    """
    Then the response status code should be 200
    And the JSON node "rating" should be equal to "4.04"
    And the JSON node "user.username" should be equal to "user-spm@tld.fr"

  Scenario: I can edit a note as a CEO
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/cleanliness/2" with body:
    """
    {
      "@id": "/quality/cleanliness/2",
      "rating": 4.05
    }
    """
    Then the response status code should be 200
    And the JSON node "rating" should be equal to "4.05"
    And the JSON node "user.username" should be equal to "user-ceo@tld.fr"

  Scenario: I can edit a note as a PM
    Given I authenticate as the intranet user "user-pm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/cleanliness/2" with body:
    """
    {
      "@id": "/quality/cleanliness/2",
      "rating": 4.06
    }
    """
    Then the response status code should be 200
    And the JSON node "rating" should be equal to "4.06"
    And the JSON node "user.username" should be equal to "user-pm@tld.fr"

  Scenario: I can edit a note as a EVP
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/cleanliness/2" with body:
    """
    {
      "@id": "/quality/cleanliness/2",
      "rating": 4.07
    }
    """
    Then the response status code should be 200
    And the JSON node "rating" should be equal to "4.07"
    And the JSON node "user.username" should be equal to "user-evp@tld.fr"

  Scenario: Delete a note - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/cleanliness/1"
    Then the response status code should be 403

  Scenario: I can delete a note
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/cleanliness/1"
    Then the response status code should be 204
