Feature: Test sales forecast files

  @resetFileTable
  Scenario: As a basic user, I can't upload a file to an opened sales forecast nor a closed one
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/sales_forecasts/1/files" with file "file" "file.doc"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/sales_forecasts/4/files" with file "file" "file.doc"
    Then the response status code should be 403

  Scenario: As a superuser, I can upload a file to an opened sales forecast
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/sales_forecasts/1/files" with file "file" "file.doc"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
    And an update log should have been inserted on resource "/sales/sales_forecasts/1" with a changeset on the property "salesForecastFiles"

  Scenario: As a superuser, I can upload a file to a closed sales forecast
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/sales_forecasts/4/files" with file "file" "file.doc"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: Upload an invalid file to a sales forecast
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/sales_forecasts/1/files" with file "file" "image.gif"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "salesForecastFiles: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "salesForecastFiles"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: Download a sales forecast attached file as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/1/files/1"
    Then the response status code should be 403

  Scenario: Download a sales forecast attached file from a sales forecast I own as an ASM
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/1/files/1"
    Then the response status code should be 200

  Scenario: As a basic user, I can't delete a file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/sales_forecasts/1/files/1"
    Then the response status code should be 403

  Scenario: As a superuser, I can delete a file
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/sales_forecasts/1/files/1"
    Then the response status code should be 204
    And an update log should have been inserted on resource "/sales/sales_forecasts/1" with a changeset on the property "salesForecastFiles"
