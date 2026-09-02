Feature: Quality / Calibrated Tools / Tool

  Scenario: As an anonymous user, test that i'm not allowed to see tools pages
    When I go to "/quality/calibrated-tools/tools"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/quality/calibrated-tools/tools/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a user, I can see a dashboard without a 500 - this is great
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/calibrated-tools/dashboard"
    Then the response status code should be 200
    And I should be on "/quality/calibrated-tools/dashboard"

  Scenario: As a basic user, test that i'm allowed to see tools
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/calibrated-tools/tools"
    Then the response status code should be 200
    And The module should be "CT"
    And I should see "TLD CALIBRATED TOOLS"
    And I should not see an "a[href$='/quality/calibrated-tools/tools/add']" element
    When I go to "/quality/calibrated-tools/tools/1/show"
    Then the response status code should be 200
    And I should not see an "a[href$='/quality/calibrated-tools/tools/1/edit']" element
    And I should not see an "a[href$='/quality/calibrated-tools/tools/1/delete']" element

  Scenario: As a basic user, test that i'm allowed to filter tools
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/calibrated-tools/tools?filter[factory]=&filter[supervisor]=&filter[toolType]=&filter[status]=ACTIVE&filter[serialNumber]=&filter[description]=&filter[vendorId]=&filter[vendorName]=&filter[vendorErp]=&filter[itemsPerPage]="
    Then the response status code should be 200
    And I should see "TLD CALIBRATED TOOLS"
    And I should see "Total number of records:"

  Scenario: As a superuser, test that i'm allowed to see tools
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/calibrated-tools/tools"
    And I should see "Tools"
    And I should see "Location areas"
    And I should see "Tool types"
    And I should see "Out of tolerance forms"
    And I should see "FILTER"
    And I should see "RESET"
    And I should see 1 "table" element
    And I should see 12 "tr" elements
    When I go to "/quality/calibrated-tools/tools/1/show"
    And I should see "TLD Calibrated Tools :"
    And I should see an "a[href$='/quality/calibrated-tools/tools/1/edit']" element
    And I should see an "a[href$='/quality/calibrated-tools/tools/1/delete']" element
    And press "Tools actions"
    And wait until I see "Out of tolerance forms for this tool"

  Scenario: As a superuser, test that i can send a tool to calibration
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/calibrated-tools/tools/1/show"
    Then the response status code should be 200
    And I should see "Take Out Of Service"
    And I should see "Send to calibration"
    And I should not see "Activate and send a certificate"

  Scenario: As a superuser, test that i can send a tool to calibration
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/calibrated-tools/tools/1/status/UNDER_CALIBRATION"
    Then the response status code should be 200

  Scenario: As a superuser, test that i can activate a tool
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I am on "/quality/calibrated-tools/tools/1/show"
    Then I should not see "Send To Calibration"
    And I should see "Activate and send a certificate"
    When I follow "Activate and send a certificate"
    Then I should be on "/quality/calibrated-tools/tools/1/activate"

  Scenario: As a superuser, test that i put out of service a tool
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/calibrated-tools/tools/16/status/OUT_OF_SERVICE"
    Then the response status code should be 200
    And I should see "Out of service comment"
    And I fill in "tool_comment[message]" with "This tool is lost"
    And I press "tool_comment[submit]"
    Then the response status code should be 200
    And I should be on "/quality/calibrated-tools/tools/16/show"

  Scenario: As a superuser, test that i can activate a tool with a file
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I am on "/quality/calibrated-tools/tools/1/activate"
    Then I fill in the following:
      | calibration_tool_activation[calibrationDate] | 02/25/2017 |
      | calibration_tool_activation[nextCalibrationDate] | 01/12/2099 |
    And I attach the file "file.pdf" to "calibration_tool_activation[certificationFile]"
    And press "Submit"
    Then I should be on "/quality/calibrated-tools/tools/1/show"
    Then I should see "Send to calibration"
    And I should see "Feb 25, 2017"
    And I should see "Jan 12, 2099"
    And I should see an "a[href$='/quality/calibrated-tools/tools/1/logs/25/delete']" element

  Scenario: As a basicuser, I can't delete a calibration log
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I am on "/quality/calibrated-tools/tools/1/show"
    Then I should see an "a.disabled" element
    And I should not see an "a[href$='/quality/calibrated-tools/tools/1/logs/25/delete']" element
    When I am on "/quality/calibrated-tools/tools/1/logs/25/delete"
    Then I should be on "/quality/calibrated-tools/tools/1/show"
    And I should see "You do not have permissions"



  Scenario: As a superuser, I can delete a calibration log
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I am on "/quality/calibrated-tools/tools/1/show"
    Then I should see an "a[href$='/quality/calibrated-tools/tools/1/logs/25/delete']" element
    When I follow "delete-log"
    Then I should be on "/quality/calibrated-tools/tools/1/show"
    And I should see "Calibration event successfully deleted"

  Scenario: As a superuser, test that i can add a tool
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/calibrated-tools/tools/add"
    Then the response status code should be 200
    When I fill in the following:
      | calibration_tool[serialNumber]   | newSerialNumber2222 |
      | calibration_tool[description] | Lorem ipsum dolor sit amet |
      | calibration_tool[calibrationInterval] | 30 |
      | calibration_tool[calibrationIntervalUnit] | days |
      | calibration_tool[calibrationNotice] | 10 |
      | calibration_tool[nextCalibrationDate] | 04/19/2099 |
      | calibration_tool[vendorId] | 88 |
      | calibration_tool[vendorErp] | StringVendorErp |
      | calibration_tool[vendorName] | StringVendorName |
    And I select "/quality/calibrated_tools/tool_types/1" from "calibration_tool[toolType]"
    And I select "/location_areas/1" from "calibration_tool[locationArea]"
    And press "Submit"
    When the response status code should be 200
    And I should be on "/quality/calibrated-tools/tools/24/show"
    And I should see "The tool has been added successfully."

  Scenario: As a superuser, test that i can edit a tool
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/calibrated-tools/tools/1/edit"
    Then the response status code should be 200
    And press "Submit"
    Then the response status code should be 200
    And I should be on "/quality/calibrated-tools/tools/1/show"
    And I should see "The tool has been modified successfully."
    And I should see "299d9982cc80dcce661d"

  Scenario: As a superuser, test that i can delete a tool
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/calibrated-tools/tools/9/delete"
    Then the response status code should be 200
    And I should be on "/quality/calibrated-tools/tools"
    And I should see "The tool has been removed."

  Scenario: Tools can be exported as csv files
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/calibrated-tools/tools"
    And I press "csv"
    Then the response status code should be 200
    Then I should see response headers "content-type" with "text/csv; charset=utf-8"
    Then I should see response headers "content-disposition" with 'inline; filename=data.csv'

