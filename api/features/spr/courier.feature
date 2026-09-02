Feature: Test courier

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Parts\Courier" should only be available for intranet user

  Scenario: Request all couriers
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/couriers?order[name]=asc"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/courier/schemas/couriers.json"

  Scenario: Request a single courier
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/couriers/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/courier/schemas/courier.json"

  Scenario: As basic user, I should not be able to create a courier
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/parts/couriers" with body:
    """
    {
      "name": "test",
      "url": "https://test.fr?pouet=tagadatsointsoin",
      "parameterName": "test_parameter"
    }
    """
    Then the response status code should be 403

  Scenario: As spare parts manager, I should be able to create a courier
    Given I authenticate as the intranet user "user-spm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/parts/couriers" with body:
    """
    {
      "name": "test",
      "url": "https://test.fr?pouet=tagadatsointsoin",
      "parameterName": "test_parameter"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/courier/schemas/courier.json"
    And the JSON node "name" should be equal to the string "test"
    And the JSON node "url" should be equal to the string "https://test.fr?pouet=tagadatsointsoin"
    And the JSON node "parameterName" should be equal to the string "test_parameter"

  Scenario: As spare parts manager, I should not be able to create a courier without valid url
    Given I authenticate as the intranet user "user-spm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/parts/couriers" with body:
    """
    {
      "name": "dhl",
      "url": "thisisnotavalidurl.fr",
      "parameterName": "test_parameter"
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to the string "url: This value is not a valid URL."

  Scenario: As spare parts manager, I should not be able to create a courier if the name already exists
    Given I authenticate as the intranet user "user-spm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/parts/couriers" with body:
    """
    {
      "name": "test",
      "url": "https://test.fr?pouet=tagadatsointsoin",
      "parameterName": "test_parameter"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string "This value is already used."

  Scenario: As basic user, I should not be able to update a courier
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/couriers/1" with body:
    """
    {
      "name": "fedex"
    }
    """
    Then the response status code should be 403

  Scenario: As spare parts manager, I should be able to update a courier
    Given I authenticate as the intranet user "user-spm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/couriers/2" with body:
    """
    {
      "name": "OUPS",
      "url": "https://oups.fr?pouet=tagadatsointsoin",
      "parameterName": "no_tracking"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/courier/schemas/courier.json"
    And the JSON node "name" should be equal to the string "OUPS"
    And the JSON node "url" should be equal to the string "https://oups.fr?pouet=tagadatsointsoin"
    And the JSON node "parameterName" should be equal to the string "no_tracking"

  Scenario: As basic user, I should not be able to delete a courier
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/parts/couriers/3"
    Then the response status code should be 403

  Scenario: As spare parts manager, I should not be able to delete a used courier
    Given I authenticate as the intranet user "user-spm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/parts/couriers/1"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The courier '1' is not deletable because it is used by 1 Tracking (1)"

  Scenario: As spare parts manager, I should be able to delete a courier
    Given I authenticate as the intranet user "user-spm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/parts/couriers/2"
    Then the response status code should be 204
