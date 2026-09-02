Feature: Test Manual Printer API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Support\ManualPrinter" should only be available for intranet user

  Scenario: Request all printers without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manual_printers"
    Then the response status code should be 401

  Scenario: Request a single printer without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manual_printers/1"
    Then the response status code should be 401

  Scenario: Request all Printers
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manual_printers"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/printer/schemas/printers.json"

  Scenario: Request a given Printer
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manual_printers/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/printer/schemas/printer.json"

  Scenario: Update a given Printer with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/support/manual_printers/1" with body:
    """
    {
      "companyName": "YAPAS",
      "firstname": "DE",
      "email": "PANNEAUX@notauthorized.com"
    }
    """
    Then the response status code should be 403

  Scenario: Update a given Printer with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/support/manual_printers/1" with body:
    """
    {
      "companyName": "DexterCompany",
      "lastname": "Goutte",
      "firstname": "Imberbe",
      "email": "putput@authorized.com",
      "address": {
        "street1": "rue des PUT",
        "street2": "PUT 1",
        "town": "PUTown",
        "postalCode": "PUT 41000",
        "city": "PUTinCity",
        "state": "PUTier",
        "country": "FR"
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "companyName" should be equal to "DexterCompany"
    And the JSON node "lastname" should be equal to "Goutte"
    And the JSON node "firstname" should be equal to "Imberbe"
    And the JSON node "email" should be equal to "putput@authorized.com"
    And the JSON node "address.street1" should be equal to "rue des PUT"
    And the JSON node "address.street2" should be equal to "PUT 1"
    And the JSON node "address.town" should be equal to "PUTown"
    And the JSON node "address.postalCode" should be equal to "PUT 41000"
    And the JSON node "address.city" should be equal to "PUTinCity"
    And the JSON node "address.state" should be equal to "PUTier"
    And the JSON node "address.country" should be equal to "FR"

  Scenario: Create a Printer with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/manual_printers" with body:
    """
    {
      "companyName": "NOTE",
      "firstname": "AUTO",
      "lastname": "RIZ",
      "email": "Z@notauthorized.com",
      "address": {
        "street1": "rue de la soif",
        "street2": "débauche street",
        "town": "Rennes",
        "postalCode": "CP 41000",
        "city": "MaximatorCity",
        "state": "Avignon",
        "country": "US"
      }
    }
    """
    Then the response status code should be 403

  Scenario: Create a Printer with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/manual_printers" with body:
    """
    {
      "companyName": "Gorafi",
      "firstname": "Jean michelle",
      "lastname": "Blagueur",
      "email": "DabEnragé@authorizedPost.com",
      "address": {
        "street1": "rue de la soif",
        "street2": "débauche street",
        "town": "Rennes",
        "postalCode": "CP 41000",
        "city": "MaximatorCity",
        "state": "Avignon",
        "country": "US"
      }
    }
    """
    Then the response status code should be 201
    And the JSON node "companyName" should be equal to "Gorafi"
    And the JSON node "firstname" should be equal to "Jean michelle"
    And the JSON node "lastname" should be equal to "Blagueur"
    And the JSON node "email" should be equal to "DabEnragé@authorizedPost.com"
    And the JSON node "address.street1" should be equal to "rue de la soif"
    And the JSON node "address.street2" should be equal to "débauche street"
    And the JSON node "address.town" should be equal to "Rennes"
    And the JSON node "address.postalCode" should be equal to "CP 41000"
    And the JSON node "address.city" should be equal to "MaximatorCity"
    And the JSON node "address.state" should be equal to "Avignon"
    And the JSON node "address.country" should be equal to "US"
    And the JSON should be valid according to the schema "tests/fixtures/json/printer/schemas/printer.json"

  Scenario: Delete a Printer with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/support/manual_printers/2"
    Then the response status code should be 403

  Scenario: Delete a Printer with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/support/manual_printers/2"
    Then the response status code should be 204

  Scenario: Only some properties of a ManualPrinter are populated when it is created
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "fields?iri=/support/manual_printers&method=POST"
    Then the response status code should be 200
    Then the JSON should be equal to:
    """
    [
      "companyName",
      "firstname",
      "lastname",
      "email",
      "address",
      "prints"
    ]
    """

  Scenario: Only some properties of a ManualPrinter can be edited
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "fields?iri=/support/manual_printers/1&method=PUT"
    Then the response status code should be 200
    Then the JSON should be equal to:
    """
    [
      "companyName",
      "firstname",
      "lastname",
      "email",
      "address",
      "prints"
    ]
    """

#  Scenario: Filters are declared on resource Manual
#    Given the class "App\Entity\Support\ManualPrinter" is exposed on the API

