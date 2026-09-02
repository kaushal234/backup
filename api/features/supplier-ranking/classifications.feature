Feature: Test Supplier Rankings classifications

  Scenario: Request all classifications for MLM User
    Given I authenticate as the intranet user "user-mlm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/classifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/classifications.json"

  Scenario: Request all classifications for Buyer User
    Given I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/classifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/classifications.json"

  Scenario: Request all classifications for CPO User
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/classifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/classifications.json"

  Scenario: Request a single classification
    Given I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/classifications/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/classification.json"
    And the JSON node "@id" should be equal to the string "/purchasing/supplier_ranking/classifications/1"
    And the JSON node "@type" should be equal to the string "Classification"
    And the JSON node "name" should be equal to the string "Unrestricted"

  Scenario: As an allowed user, I can add a classification
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/supplier_ranking/classifications" with body:
    """
    {
      "name": "La classe satanass",
      "description": "You are full of swag",
      "isSupplierApproved": true,
      "workflowLevel": 69,
      "color": "blaune",
      "targetClassifications": [
        "/purchasing/supplier_ranking/classifications/1",
        "/purchasing/supplier_ranking/classifications/2"
      ]
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/classification.json"
    And the JSON node "@id" should be equal to the string "/purchasing/supplier_ranking/classifications/6"
    And the JSON node "name" should be equal to the string "La classe satanass"
    And the JSON node "description" should be equal to the string "You are full of swag"
    And the JSON node "isSupplierApproved" should be true
    And the JSON node "workflowLevel" should be equal to 69
    And the JSON node "color" should be equal to the string "blaune"
    And the JSON node "targetClassifications" should have 2 element

  Scenario: As an allowed user, I can edit a classification
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/supplier_ranking/classifications/6" with body:
    """
    {
      "name": "P I",
      "description": "un pauvre surveste lacoste",
      "isSupplierApproved": false,
      "workflowLevel": 96,
      "color": "revert",
      "targetClassifications": []
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/classification.json"
    And the JSON node "@id" should be equal to the string "/purchasing/supplier_ranking/classifications/6"
    And the JSON node "name" should be equal to the string "P I"
    And the JSON node "description" should be equal to the string "un pauvre surveste lacoste"
    And the JSON node "isSupplierApproved" should be false
    And the JSON node "workflowLevel" should be equal to 96
    And the JSON node "color" should be equal to the string "revert"

  Scenario: Request all classifications shouldn't be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/classifications"
    Then the response status code should be 403

  Scenario: Request a classification shouldn't be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/classifications/1"
    Then the response status code should be 403

  Scenario: add an classification shouldn't be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/supplier_ranking/classifications" with body:
    """
    {
      "name": "echec",
      "description": "du post taledroa",
      "isSupplierApproved": true,
      "workflowLevel": 33,
      "color": "rouge",
      "targetClassifications": [
        "/purchasing/supplier_ranking/classifications/1"
      ]
    }
    """
    Then the response status code should be 403

  Scenario: add an classification shouldn't be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/supplier_ranking/classifications/6" with body:
    """
    {
      "name": "failure",
      "description": "du put taledroadutout",
      "isSupplierApproved": true,
      "workflowLevel": 22,
      "color": "violai",
      "targetClassifications": [
        "/purchasing/supplier_ranking/classifications/2"
      ]
    }
    """
    Then the response status code should be 403

  Scenario: As a basic user, I can't transfer a classification
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "PUT" request to "/purchasing/supplier_ranking/classifications/6/transfer" with body:
    """
    {
        "target": "/purchasing/supplier_ranking/classifications/1"
    }
    """
    Then the response status code should be 403

  Scenario: As a superuser, I can transfer a classification
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "PUT" request to "/purchasing/supplier_ranking/classifications/6/transfer" with body:
    """
    {
        "target": "/purchasing/supplier_ranking/classifications/1"
    }
    """
    Then the response status code should be 200

  Scenario: Delete an classification shouldn't be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/purchasing/supplier_ranking/classifications/6"
    Then the response status code should be 403

  Scenario: Delete an classification should be accessible to basic user
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/purchasing/supplier_ranking/classifications/6"
    Then the response status code should be 204

