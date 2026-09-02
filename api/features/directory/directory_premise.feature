Feature: Test Premise API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Directory\Premise" should only be available for intranet user

  Scenario: Request all Premises
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/premises"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/premise/schemas/premises.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Directory\Premise" is exposed on the API
    Then the filter "archived" should be available and its type should be "bool"
    And the filter "order[name]" should be available and its type should be "string"
    And the filter "order[description]" should be available and its type should be "string"
    And the filter "columns" should be available and its type should be "string"

  Scenario: Premises can be downloaded as an Excel file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/premises?columns=id,name,description,latitude,longitude,supportTeam,address,tags,count"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Name | Description| Latitude | Longitude | Support Team | Address | Tags | Count |


  Scenario: Premises can be downloaded as a csv file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "text/csv"
    When I send a "GET" request to "/premises"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "text/csv; charset=utf-8"

  Scenario: As a basic user I can filter Premises by users BU
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/premises?directoryEntity=/business_units/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/premise/schemas/premises.json"

  Scenario: As a basic user I can filter Premises by users Region
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/premises?directoryEntity=/regions/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/premise/schemas/premises.json"

  Scenario: As a basic user I can filter Premises by users sub division
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/premises?directoryEntity=/sub_divisions/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/premise/schemas/premises.json"

  Scenario: As a basic user I can filter Premises by users division
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/premises?directoryEntity=/divisions/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/premise/schemas/premises.json"

  Scenario: As a basic user I can't filter Premises by a directory entity if the iri class is not valid
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/premises?directoryEntity=/contract_types/1"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "The value passed to the DirectoryEntityFilter should be a businessUnit, a region, a subDivision or a division"

  Scenario: Request not my Premise and a home office
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/premises/3"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/premise/schemas/premise.json"
    And the JSON node "address.street1" should be equal to the string "***"
    And the JSON node "address.street2" should be equal to the string "***"
    And the JSON node "address.postalCode" should be equal to the string "***"

  Scenario: Create a Premise with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/premises" with body:
    """
    {
      "name": "Des-trois",
      "description": "Flat on the 2nd floor of the bouilding",
      "address": {
        "street1": "rue Du Poulet",
        "street2": "Barbie girl street",
        "town": "Saint Stade toulousain ",
        "postalCode": "CP 31000",
        "city": "CinquièmeTitre",
        "state": "Lauragais",
        "country": "SudOuest"
      },
      "tags": [
        "/premise_tags/18"
      ],
      "supportTeam": "/mis/support_teams/1"
    }
    """
    Then the response status code should be 403

  Scenario: Can't create a Premise if name already used
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/premises" with body:
    """
    {
      "name": "Bamacoco",
      "description": "fail",
      "address": {
        "street1": "rue Du Poulet",
        "street2": "Barbie girl street",
        "town": "Saint Stade toulousain ",
        "postalCode": "CP 31000",
        "city": "CinquièmeTitre",
        "state": "Lauragais",
        "country": "US"
      },
      "tags": [
        "/premise_tags/23"
      ],
      "supportTeam": "/mis/support_teams/1"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "name"
    And the JSON node "violations[0].message" should contain "This value is already used by another Premise."

  Scenario: Create a Premise with permission OK with latitude and longitude
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/premises" with body:
    """
    {
      "name": "Des-trois",
      "description": "Flat on the 2nd floor of the bouilding",
      "address": {
        "street1": "rue Du Poulet",
        "street2": "Barbie girl street",
        "town": "Saint Stade toulousain",
        "postalCode": "CP 31000",
        "city": "CinquièmeTitre",
        "state": "Lauragais",
        "country": "US"
      },
      "latitude": 45.6,
      "longitude": 56.8,
      "tags": [
        "/premise_tags/23"
      ],
      "supportTeam": "/mis/support_teams/1"
    }
    """
    Then the response status code should be 201
    And the JSON node "name" should be equal to "Des-trois"
    And the JSON node "description" should be equal to "Flat on the 2nd floor of the bouilding"
    And the JSON node "latitude" should be equal to 45.6
    And the JSON node "longitude" should be equal to 56.8
    And the JSON node "address.street1" should be equal to "rue Du Poulet"
    And the JSON node "address.street2" should be equal to "Barbie girl street"
    And the JSON node "address.town" should be equal to "Saint Stade toulousain"
    And the JSON node "address.city" should be equal to "CinquièmeTitre"
    And the JSON node "address.postalCode" should be equal to "CP 31000"
    And the JSON node "address.state" should be equal to "Lauragais"
    And the JSON node "address.country" should be equal to "US"
    And the JSON node "tags[0].@id" should be equal to "/premise_tags/23"
    And the JSON node "supportTeam.@id" should be equal to "/mis/support_teams/1"
    And the JSON should be valid according to the schema "tests/fixtures/json/premise/schemas/premise.json"

  Scenario: Update a given Premise with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/premises/1" with body:
    """
    {
      "name": "Alibarbar",
      "description": "Not an office with alien",
      "supportTeam": "/mis/support_teams/1"
    }
    """
    Then the response status code should be 403

  Scenario: Update a given Premise with permission OK (superuser)
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/premises/1" with body:
    """
    {
      "name": "Alibarbar",
      "description": "Not an office with alien",
      "address": {
        "street1": "Rue Guiguette",
        "street2": "Barbie girl street 2",
        "town": "Midi",
        "postalCode": "CP URSS",
        "city": "20titre",
        "state": "Texxxas",
        "country": "FR"
      },
      "latitude": 45.6,
      "longitude": 56.8,
      "tags": [
        "/premise_tags/23"
      ],
      "supportTeam": "/mis/support_teams/2"
    }
    """
    Then the response status code should be 200
    And the JSON node "name" should be equal to "Alibarbar"
    And the JSON node "description" should be equal to "Not an office with alien"
    And the JSON node "latitude" should be equal to 45.6
    And the JSON node "longitude" should be equal to 56.8
    And the JSON node "address.street1" should be equal to "Rue Guiguette"
    And the JSON node "address.street2" should be equal to "Barbie girl street 2"
    And the JSON node "address.town" should be equal to "Midi"
    And the JSON node "address.city" should be equal to "20titre"
    And the JSON node "address.postalCode" should be equal to "CP URSS"
    And the JSON node "address.state" should be equal to "Texxxas"
    And the JSON node "address.country" should be equal to "FR"
    And the JSON node "tags[0].@id" should be equal to "/premise_tags/23"
    And the JSON node "supportTeam.@id" should be equal to "/mis/support_teams/2"

  Scenario: Can't update a Premise with people on it
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/premises/1" with body:
    """
    {
      "archived": true
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "You can't archived premise if there is still people on it"

  Scenario: As a basic user, I can't transfer a premise
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "PUT" request to "/premises/4/transfer" with body:
    """
    {
        "target": "/premises/1"
    }
    """
    Then the response status code should be 403

  Scenario: As a superuser, I can transfer a premise
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "GET" request to "/people/28"
    Then the JSON node "premise.@id" should be equal to the string "/premises/4"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "PUT" request to "/premises/4/transfer" with body:
    """
    {
        "target": "/premises/1"
    }
    """
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "GET" request to "/people/28"
    Then the JSON node "premise.@id" should be equal to the string "/premises/1"

  Scenario: Create a given Premise type with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/premise_tags" with body:
    """
    {
      "name": "Bouilding"
    }
    """
    Then the response status code should be 403

  Scenario: Create a given Premise type with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/premise_tags" with body:
    """
    {
      "name": "Bouilding"
    }
    """
    Then the response status code should be 201
    And the JSON node "name" should be equal to the string "Bouilding"

  Scenario: Update a given Premise type with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/premise_tags/23" with body:
    """
    {
      "name": "Bouilding en feu"
    }
    """
    Then the response status code should be 200
    And the JSON node "name" should be equal to the string "Bouilding en feu"

  Scenario: Can't update home office (restricted) Premise type
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/premise_tags/24" with body:
    """
    {
      "name": "Error can t change it"
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should contain "name: Value 'Home Office (restricted)' is locked and can't be updated."
    And the JSON node "violations[0].propertyPath" should be equal to "name"
    And the JSON node "violations[0].message" should contain "Value 'Home Office (restricted)' is locked and can't be updated."

  Scenario: Can't update home office Premise type
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/premise_tags/25" with body:
    """
    {
      "name": "Error can t change it either"
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should contain "name: Value 'Home Office' is locked and can't be updated."
    And the JSON node "violations[0].propertyPath" should be equal to "name"
    And the JSON node "violations[0].message" should contain "Value 'Home Office' is locked and can't be updated."

  Scenario: Delete a given Premise type with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/premise_tags/22"
    Then the response status code should be 204
