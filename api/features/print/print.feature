Feature: Test Manual Print API

  Scenario: Request all prints without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manual_prints"
    Then the response status code should be 401

  Scenario: Request all Prints
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manual_prints"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manual_prints/schemas/manual_prints.json"

  Scenario: Request a single print without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manual_prints/e99e8e95-0245-4dc7-ace0-749c6838991c"
    Then the response status code should be 401

  Scenario: Request a given Print
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manual_prints/e99e8e95-0245-4dc7-ace0-749c6838991c"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manual_prints/schemas/manual_print.json"

  Scenario: Download a ManualPrint Zip without being authenticated should be permitted on the public dedicated route, an populate its downloadtedAt property
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manual_prints/e99e8e95-0245-4dc7-ace0-749c6838991c"
    Then the JSON node "downloadedAt" should be null
    Given I add "Accept" header equal to "application/zip"
    When I send a "GET" request to "/public/support/manual_prints/e99e8e95-0245-4dc7-ace0-749c6838991c"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/zip"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manual_prints/e99e8e95-0245-4dc7-ace0-749c6838991c"
    Then the JSON node "downloadedAt" should not be null

  Scenario: Trying to download a second time this ManualPrint Zip without being authenticated should throw an exception
    Given I add "Accept" header equal to "application/zip"
    When I send a "GET" request to "/public/support/manual_prints/e99e8e95-0245-4dc7-ace0-749c6838991c"
    Then the response status code should be 400
    And the header "Content-Type" should be equal to "application/problem+json; charset=utf-8"

  Scenario: Update a given Print is not possible, wrong method
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/support/manual_prints/e99e8e95-0245-4dc7-ace0-749c6838991c" with body:
    """
    {
      "companyName": "YAPAS",
      "firstname": "DE",
      "email": "PANNEAUX@notauthorized.com"
    }
    """
    Then the response status code should be 405

  Scenario: Create a Print with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/manual_prints" with body:
    """
    {
      "manual": "/support/manuals/1",
      "manualPrinter": "/support/manual_printers/1",
      "requestedDeliveryDate": "2021-12-30 06:59:00",
      "standard": 0,
      "full": 0,
      "extra": 0,
      "chapter5": 1,
      "comment": "zerzer"
    }
    """
    Then the response status code should be 403

  Scenario: Create a Print with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/manual_prints" with body:
    """
    {
      "manual": "/support/manuals/1",
      "manualPrinter": "/support/manual_printers/1",
      "requestedDeliveryDate": "2021-12-30 06:59:00",
      "standard": 2,
      "full": 0,
      "extra": 0,
      "chapter5": 1,
      "comment": "printerest"
    }
    """
    Then the response status code should be 201
    And the JSON node "requestedDeliveryDate" should be equal to "2021-12-30T06:59:00-05:00"
    And the JSON node "standard" should be equal to 2
    And the JSON node "full" should be equal to 0
    And the JSON node "extra" should be equal to 0
    And the JSON node "chapter5" should be equal to 1
    And the JSON node "manual.id" should be equal to 1
    And the JSON node "manualPrinter.id" should be equal to 1
    And an email should have been sent asynchronously with subject "New Manual Print request notification"
    And this asynchronous email should be sent to "guthunberbe@1492.com"
    And this asynchronous email should be sent as cc to "user-superuser@tld.fr"

  Scenario: Delete a Printer is not possible, wrong method
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/support/manual_prints/e99e8e95-0245-4dc7-ace0-749c6838991c"
    Then the response status code should be 405

  Scenario: Only some properties of a ManualPrint are populated when it is created
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "fields?iri=/support/manual_prints&method=POST"
    Then the response status code should be 200
    Then the JSON should be equal to:
    """
    [
      "manual",
      "manualPrinter",
      "standard",
      "full",
      "extra",
      "chapter5",
      "comment",
      "requestedDeliveryDate"
    ]
    """
