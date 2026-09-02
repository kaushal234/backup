Feature: Test sales forecast closure files

  @resetFileTable
  Scenario: As a basic user, I can't upload a file to a sales forecast closure
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/forecast_closures/1/files" with file "file" "file.doc"
    Then the response status code should be 403

  Scenario: As a superuser, I can upload a file to an sales forecast closure
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/forecast_closures/1/files" with file "file" "file.doc"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
    And an update log should have been inserted on resource "/sales/forecast_closures/1" with a changeset on the property "forecastClosureFiles"

  Scenario: Upload an invalid file to a sales forecast closure
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/forecast_closures/1/files" with file "file" "image.gif"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "forecastClosureFiles: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "forecastClosureFiles"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: Download a sales forecast closure attached file as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/forecast_closures/1/files/1"
    Then the response status code should be 200

  Scenario: As a basic user, I can't delete a file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/forecast_closures/1/files/1"
    Then the response status code should be 403

  Scenario: As a superuser, I can delete a file
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/forecast_closures/1/files/1"
    Then the response status code should be 204
    And an update log should have been inserted on resource "/sales/forecast_closures/1" with a changeset on the property "forecastClosureFiles"
