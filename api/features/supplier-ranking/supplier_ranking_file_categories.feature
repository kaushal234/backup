Feature: Test Supplier Rankings file_categories

  Scenario: Request all file category for basic user should not be allowed
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/file_categories"
    Then the response status code should be 403

    Scenario: Request a file category shouldn't be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/file_categories/1"
    Then the response status code should be 403

  Scenario: add an file category shouldn't be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/supplier_ranking/file_categories" with body:
    """
    {
        "name": "ERROR"
    }
    """
    Then the response status code should be 403

  Scenario: edit an file category shouldn't be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/supplier_ranking/file_categories/1" with body:
    """
    {
        "name": "ERROR INCOMING"
    }
    """
    Then the response status code should be 403

  Scenario: As a basic user, I can't transfer a file category
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "PUT" request to "/purchasing/supplier_ranking/file_categories/1/transfer" with body:
    """
    {
        "target": "/purchasing/supplier_ranking/file_categories/2"
    }
    """
    Then the response status code should be 403

  Scenario: delete an file category shouldn't be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/purchasing/supplier_ranking/file_categories/1"
    Then the response status code should be 403

  Scenario: Request all file category for CPO User
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/file_categories"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/file_categories.json"

  Scenario: Request a single file category for CPO user
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/file_categories/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/file_category.json"

  Scenario: As an allowed user, I can add a file category
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/supplier_ranking/file_categories" with body:
    """
    {
        "name": "X Files"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/file_category.json"
    And the JSON node "@id" should be equal to the string "/purchasing/supplier_ranking/file_categories/10"
    And the JSON node "name" should be equal to the string "X Files"

  Scenario: As an allowed user, I can edit a file category
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/supplier_ranking/file_categories/9" with body:
    """
    {
        "name": "CONFIDENTIAL"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/file_category.json"
    And the JSON node "name" should be equal to the string "CONFIDENTIAL"

  Scenario: As a superuser, I can transfer a file category
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "PUT" request to "/purchasing/supplier_ranking/file_categories/10/transfer" with body:
    """
    {
        "target": "/purchasing/supplier_ranking/file_categories/1"
    }
    """
    Then the response status code should be 200

  Scenario: add an file category should be accessible to basic user
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/purchasing/supplier_ranking/file_categories/10"
    Then the response status code should be 204


