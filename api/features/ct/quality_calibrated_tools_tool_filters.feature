Feature: Test tools API filters

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Quality\CalibratedTools\Tool" is exposed on the API
    Then the filter "order[description]" should be available and its type should be "string"
    And the filter "description" should be available and its type should be "string"
    And the filter "serialNumber" should be available and its type should be "string"
    And the filter "locationArea" should be available and its type should be "string"
    And the filter "locationArea.supervisor" should be available and its type should be "string"
    And the filter "locationArea.factory" should be available and its type should be "string"
    And the filter "calibrationInterval" should be available and its type should be "int"
    And the filter "calibrationNotice" should be available and its type should be "int"
    And the filter "createdBy" should be available and its type should be "string"
    And the filter "vendorId" should be available and its type should be "string"
    And the filter "vendorErp" should be available and its type should be "string"
    And the filter "vendorName" should be available and its type should be "string"
    And the filter "status" should be available and its type should be "string"
    And the filter "toolType" should be available and its type should be "string"
    And the filter "calibrationLogs" should be available and its type should be "string"

  Scenario: Request all tools - filter 'normalizationGroupsOverride'
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "text/csv"
    When I send a "GET" request to "/quality/calibrated_tools/tools?normalizationGroupsOverride[]=tool_export"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "text/csv; charset=utf-8"
    Then the header "Content-Disposition" should be equal to "inline; filename=data.csv"
    And the csv file headers are:
      | serialNumber | description | status | nextCalibrationDate | type | area | supervisor | statusUpdatedAt |
