Feature: Test calibration logs API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Quality\CalibratedTools\CalibrationLog" should only be available for intranet user

  Scenario: Request all calibration logs
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/calibrated_tools/calibration_logs"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/quality/calibrated_tools/calibration_log/schemas/calibration_logs.json"

  Scenario: Request a single tool
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/calibrated_tools/calibration_logs/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/quality/calibrated_tools/calibration_log/schemas/calibration_log.json"

  Scenario: Update a given calibration log - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/calibrated_tools/calibration_logs/3" with the body "tests/fixtures/json/quality/calibrated_tools/calibration_log/dummies/put.json"
    Then the response status code should be 403

  Scenario: Update a given calibration log - permissions OK
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/calibrated_tools/calibration_logs/1" with the body "tests/fixtures/json/quality/calibrated_tools/calibration_log/dummies/put.json"
    Then the response status code should be 200
    And the JSON node "startDate" should be equal to "2017-01-30T06:51:43-05:00"

  Scenario: Create a calibration log - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/calibrated_tools/calibration_logs" with the body "tests/fixtures/json/quality/calibrated_tools/calibration_log/dummies/post.json"
    Then the response status code should be 403

  Scenario: Create a calibration log - permissions OK
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/calibrated_tools/calibration_logs" with the body "tests/fixtures/json/quality/calibrated_tools/calibration_log/dummies/post.json"
    Then the response status code should be 201
    And the JSON nodes should be equal to:
      | startDate | 2017-01-30T06:51:43-05:00 |
      | endDate | 2017-02-22T01:09:47-05:00 |
      | tool | /quality/calibrated_tools/tools/1 |

  Scenario: Delete a calibration log - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/calibrated_tools/calibration_logs/1"
    Then the response status code should be 403

  Scenario: Delete a calibration log - permissions OK
    And I add "Accept" header equal to "application/ld+json"
    Given I authenticate as the intranet user "user-qam@tld.fr"
    When I send a "DELETE" request to "/quality/calibrated_tools/calibration_logs/2"
    Then the response status code should be 204

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Quality\CalibratedTools\CalibrationLog" is exposed on the API
    Then the filter "order[startDate]" should be available and its type should be "string"
    And the filter "startDate[after]" should be available and its type should be "DateTimeInterface"
    And the filter "endDate[before]" should be available and its type should be "DateTimeInterface"
    And the filter "outOfToleranceForm" should be available and its type should be "string"
    And the filter "tool" should be available and its type should be "string"

  @resetFileTable
  Scenario: Upload an invalid certificate file to a log
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/calibrated_tools/calibration_logs/1/file" with file "file" "file.txt"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "certificate: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "certificate"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: Upload a certificate file to a log
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/calibrated_tools/calibration_logs/1/file" with file "file" "file.docx"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
