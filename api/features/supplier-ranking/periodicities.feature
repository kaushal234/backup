Feature: Test Supplier Rankings periodicities

  Scenario: Request all periodicities for basic User
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/periodicities"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/periodicities.json"

  Scenario: Request a single periodicity level
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/periodicities/expertiseLevel=1;classification=1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/periodicity.json"
    And the JSON node "@id" should be equal to the string "/purchasing/supplier_ranking/periodicities/expertiseLevel=1;classification=1"
    And the JSON node "@type" should be equal to the string "Periodicity"
    And the JSON node "expertiseLevel.name" should be equal to the string "OCM"
    And the JSON node "classification.name" should be equal to the string "Unrestricted"
    And the JSON node "months" should be equal to the number 36

  Scenario: As an allowed user, I can add a expertise level
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/supplier_ranking/periodicities" with body:
    """
    {
        "expertiseLevel": "/purchasing/supplier_ranking/expertise_levels/1",
        "classification": "/purchasing/supplier_ranking/classifications/4",
        "months": 13
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/periodicity.json"
    And the JSON node "@id" should be equal to the string "/purchasing/supplier_ranking/periodicities/expertiseLevel=1;classification=4"
    And the JSON node "expertiseLevel.name" should be equal to the string "OCM"
    And the JSON node "classification.name" should be equal to the string "Locked"
    And the JSON node "months" should be equal to the number 13

  Scenario: As an allowed user, I can edit a expertise level
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/supplier_ranking/periodicities/expertiseLevel=1;classification=4" with body:
    """
    {
        "months": 14
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/periodicity.json"
    And the JSON node "months" should be equal to the number 14

  Scenario: add an expertise level shouldn't be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/supplier_ranking/periodicities" with body:
    """
    {
        "expertiseLevel": "/purchasing/supplier_ranking/expertise_levels/1",
        "classification": "/purchasing/supplier_ranking/classifications/5",
        "months": 69
    }
    """
    Then the response status code should be 403

  Scenario: edit an expertise level shouldn't be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/supplier_ranking/periodicities/expertiseLevel=1;classification=4" with body:
    """
    {
        "months": 78
    }
    """
    Then the response status code should be 403

  Scenario: delete an expertise level shouldn't be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/purchasing/supplier_ranking/periodicities/expertiseLevel=1;classification=4"
    Then the response status code should be 403

  Scenario: add an expertise level should be accessible to basic user
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/purchasing/supplier_ranking/periodicities/expertiseLevel=1;classification=4"
    Then the response status code should be 204


