Feature: Test qhse progress

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Quality\QualityHealthSafetyEnvironmentProgress" should only be available for intranet user

  Scenario: Request all notes
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/qhse_progress"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/quality/qhse_progress/schemas/qhse_progress_collection.json"
    And the JSON node "hydra:member" should have 60 elements

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Quality\QualityHealthSafetyEnvironmentProgress" is exposed on the API
    Then the filter "location" should be available and its type should be "string"
    And the filter "date[after]" should be available and its type should be "DateTimeInterface"
    And the filter "date[before]" should be available and its type should be "DateTimeInterface"
    And the filter "location.capability.factory" should be available and its type should be "bool"

  Scenario: Request a single note
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/qhse_progress/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/quality/qhse_progress/schemas/qhse_progress.json"

  Scenario: As basic user I am not allowed to post a note
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/qhse_progress" with body:
    """
    {
      "location": "/locations/7",
      "rating": 53,
      "date": "2000-01-01T00:00:00-04:00"
    }
    """
    Then the response status code should be 403

  Scenario: As superuser I can post a note
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/qhse_progress" with body:
    """
    {
      "location": "/locations/7",
      "rating": 53,
      "date": "2000-01-01T00:00:00-04:00"
    }
    """
    Then the response status code should be 201
    And the JSON node "poster.username" should be equal to "user-superuser@tld.fr"

  Scenario: As basic user I am not allowed to edit a note
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/qhse_progress/1" with body:
    """
    {
      "rating": 72
    }
    """
    Then the response status code should be 403

  Scenario: As superuser I can edit a note
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/qhse_progress/1" with body:
    """
    {
      "rating": 72
    }
    """
    Then the response status code should be 200
    And the JSON node "rating" should be equal to 72
    And the JSON node "poster.username" should be equal to "user-superuser@tld.fr"

  Scenario: I can edit a note as a QAM
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/qhse_progress/2" with body:
    """
    {
      "rating": 0
    }
    """
    Then the response status code should be 200
    And the JSON node "rating" should be equal to 0
    And the JSON node "poster.username" should be equal to "user-qam@tld.fr"

  Scenario: I can edit a note as a MPE
    Given I authenticate as the intranet user "user-mpe@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/qhse_progress/2" with body:
    """
    {
      "rating": 1
    }
    """
    Then the response status code should be 200
    And the JSON node "rating" should be equal to 1
    And the JSON node "poster.username" should be equal to "user-mpe@tld.fr"

  Scenario: I can edit a note as a COO
    Given I authenticate as the intranet user "user-coo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/qhse_progress/2" with body:
    """
    {
      "rating": 2
    }
    """
    Then the response status code should be 200
    And the JSON node "rating" should be equal to 2
    And the JSON node "poster.username" should be equal to "user-coo@tld.fr"

  Scenario: I can edit a note as a CMO
    Given I authenticate as the intranet user "user-cmo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/qhse_progress/2" with body:
    """
    {
      "rating": 3
    }
    """
    Then the response status code should be 200
    And the JSON node "rating" should be equal to 3
    And the JSON node "poster.username" should be equal to "user-cmo@tld.fr"

  Scenario: I can edit a note as a SPM
    Given I authenticate as the intranet user "user-spm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/qhse_progress/2" with body:
    """
    {
      "rating": 4
    }
    """
    Then the response status code should be 200
    And the JSON node "rating" should be equal to 4
    And the JSON node "poster.username" should be equal to "user-spm@tld.fr"

  Scenario: I can edit a note as a CEO
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/qhse_progress/2" with body:
    """
    {
      "rating": 5
    }
    """
    Then the response status code should be 200
    And the JSON node "rating" should be equal to 5
    And the JSON node "poster.username" should be equal to "user-ceo@tld.fr"

  Scenario: I can edit a note as a PM
    Given I authenticate as the intranet user "user-pm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/qhse_progress/2" with body:
    """
    {
      "rating": 6
    }
    """
    Then the response status code should be 200
    And the JSON node "rating" should be equal to 6
    And the JSON node "poster.username" should be equal to "user-pm@tld.fr"

  Scenario: I can edit a note as a EVP
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/qhse_progress/2" with body:
    """
    {
      "rating": 7
    }
    """
    Then the response status code should be 200
    And the JSON node "rating" should be equal to 7
    And the JSON node "poster.username" should be equal to "user-evp@tld.fr"

  Scenario: Delete a note - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/qhse_progress/1"
    Then the response status code should be 403

  Scenario: I can delete a note
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/qhse_progress/1"
    Then the response status code should be 204
