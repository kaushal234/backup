Feature: Test Supplier Rankings expertise levels

  Scenario: Request all expertise levels for MLM User
    Given I authenticate as the intranet user "user-mlm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/expertise_levels"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/expertise_levels.json"

  Scenario: Request all expertise levels for Buyer User
    Given I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/expertise_levels"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/expertise_levels.json"

  Scenario: Request all expertise levels for CPO User
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/expertise_levels"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/expertise_levels.json"

  Scenario: Request a single expertise level
    Given I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/expertise_levels/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/expertise_level.json"
    And the JSON node "@id" should be equal to the string "/purchasing/supplier_ranking/expertise_levels/1"
    And the JSON node "@type" should be equal to the string "ExpertiseLevel"
    And the JSON node "name" should be equal to the string "OCM"

  Scenario: As an allowed user, I can add a expertise level
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/supplier_ranking/expertise_levels" with body:
    """
    {
        "name": "ZE BEST",
        "description": "He is the very best like no one ever was"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/expertise_level.json"
    And the JSON node "@id" should be equal to the string "/purchasing/supplier_ranking/expertise_levels/4"
    And the JSON node "name" should be equal to the string "ZE BEST"
    And the JSON node "description" should be equal to the string "He is the very best like no one ever was"

  Scenario: As an allowed user, I can edit a expertise level
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/supplier_ranking/expertise_levels/4" with body:
    """
    {
        "name": "GARBAGE",
        "description": "23/0, c'est la piquette JACK, tu sais pas jouer JACK, tu es mauvais"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/expertise_level.json"
    And the JSON node "name" should be equal to the string "GARBAGE"
    And the JSON node "description" should be equal to the string "23/0, c'est la piquette JACK, tu sais pas jouer JACK, tu es mauvais"

  Scenario: Request all expertise levels shouldn't be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/expertise_levels"
    Then the response status code should be 403

  Scenario: Request a expertise level shouldn't be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/expertise_levels/1"
    Then the response status code should be 403

  Scenario: add an expertise level shouldn't be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/supplier_ranking/expertise_levels" with body:
    """
    {
        "name": "ZE BEST",
        "description": "He is the very best like no one ever was"
    }
    """
    Then the response status code should be 403

  Scenario: edit an expertise level shouldn't be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/supplier_ranking/expertise_levels/4" with body:
    """
    {
        "name": "ZE BEST",
        "description": "He is the very best like no one ever was"
    }
    """
    Then the response status code should be 403

  Scenario: As a basic user, I can't transfer a expertise level
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "PUT" request to "/purchasing/supplier_ranking/expertise_levels/4/transfer" with body:
    """
    {
        "target": "/purchasing/supplier_ranking/expertise_levels/1"
    }
    """
    Then the response status code should be 403

  Scenario: As a superuser, I can transfer a expertise level
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "PUT" request to "/purchasing/supplier_ranking/expertise_levels/4/transfer" with body:
    """
    {
        "target": "/purchasing/supplier_ranking/expertise_levels/1"
    }
    """
    Then the response status code should be 200

  Scenario: delete an expertise level shouldn't be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/purchasing/supplier_ranking/expertise_levels/4"
    Then the response status code should be 403

  Scenario: add an expertise level should be accessible to basic user
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/purchasing/supplier_ranking/expertise_levels/4"
    Then the response status code should be 204


