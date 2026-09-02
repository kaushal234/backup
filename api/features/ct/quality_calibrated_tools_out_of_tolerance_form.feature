Feature: Test tool types API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Quality\CalibratedTools\OutOfToleranceForm" should only be available for intranet user

  Scenario: Request all out of tolerance forms
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/calibrated_tools/out_of_tolerance_forms"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/quality/calibrated_tools/out_of_tolerance_form/schemas/out_of_tolerance_forms.json"

  Scenario: Request a single out of tolerance form
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/calibrated_tools/out_of_tolerance_forms/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/quality/calibrated_tools/out_of_tolerance_form/schemas/out_of_tolerance_form.json"

  Scenario: Update a given out of tolerance form - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/calibrated_tools/out_of_tolerance_forms/3" with the body "tests/fixtures/json/quality/calibrated_tools/out_of_tolerance_form/dummies/put.json"
    Then the response status code should be 403

  Scenario: Update a given out of tolerance form - permissions OK
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/calibrated_tools/out_of_tolerance_forms/1" with the body "tests/fixtures/json/quality/calibrated_tools/out_of_tolerance_form/dummies/put.json"
    Then the response status code should be 200
    And the JSON node "status" should be equal to "IN_PROGRESS"

  Scenario: Create a out of tolerance form - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/calibrated_tools/tools/1/out_of_tolerance_forms" with the body "tests/fixtures/json/quality/calibrated_tools/out_of_tolerance_form/dummies/post.json"
    Then the response status code should be 403

  Scenario: Create a out of tolerance form - permissions OK
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/calibrated_tools/tools/23/out_of_tolerance_forms" with the body "tests/fixtures/json/quality/calibrated_tools/out_of_tolerance_form/dummies/post.json"
    Then the response status code should be 201
    And the JSON nodes should be equal to:
      | status | IN_PROGRESS |
      | id | 19 |
      | impactAnalysis | Major |
      | correctiveMeasures | swapping with a new one |
    When I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/calibrated_tools/calibration_logs/24"
    Then the response status code should be 200
    And the JSON node "outOfToleranceForm" should be equal to "/quality/calibrated_tools/out_of_tolerance_forms/19"

  Scenario: Delete a out of tolerance form - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/calibrated_tools/out_of_tolerance_forms/1"
    Then the response status code should be 403

  Scenario: Delete a out of tolerance form - permissions OK
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/calibrated_tools/out_of_tolerance_forms/2"
    Then the response status code should be 204

  @resetFileTable
  Scenario: Upload a file attached to an out of tolerance form
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/calibrated_tools/out_of_tolerance_forms/3/files" with file "file" "file.pdf"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: Try to reset the files collection
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/calibrated_tools/out_of_tolerance_forms/3" with body:
    """
    {
      "files": []
    }
    """
    Then the response status code should be 200
    And the JSON node "files[0]" should exist

  Scenario: Upload an invalid file attached to an out of tolerance form
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/calibrated_tools/out_of_tolerance_forms/3/files" with file "file" "image.gif"
    Then the response status code should be 422

  Scenario: Download an attached to an out of tolerance form
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/calibrated_tools/out_of_tolerance_forms/3/files/1"
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/calibrated_tools/out_of_tolerance_forms/40/files/1"
    Then the response status code should be 404

  Scenario: Delete a nonexistent file attached to an out of tolerance form
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/calibrated_tools/out_of_tolerance_forms/40/files/1"
    Then the response status code should be 404

  Scenario: Delete file attached to an out of tolerance form
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/calibrated_tools/out_of_tolerance_forms/3/files/1"
    Then the response status code should be 204

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Quality\CalibratedTools\OutOfToleranceForm" is exposed on the API
    Then the filter "order[status]" should be available and its type should be "string"
    And the filter "status" should be available and its type should be "string"
    And the filter "impactAnalysis" should be available and its type should be "string"
    And the filter "correctiveMeasures" should be available and its type should be "string"
    And the filter "analysisBy" should be available and its type should be "string"
    And the filter "calibrationLog.tool.createdBy" should be available and its type should be "string"
    And the filter "calibrationLog.tool.toolType" should be available and its type should be "string"
    And the filter "calibrationLog.tool.locationArea" should be available and its type should be "string"
    And the filter "calibrationLog.tool.locationArea.factory" should be available and its type should be "string"
