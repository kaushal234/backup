Feature: Test tools state machine

  Scenario: Updating a tool nextCalibrationDate should change its status
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/calibrated_tools/tools/14"
    Then the response status code should be 200
    And the JSON node "status" should be equal to "EXPIRED"
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/calibrated_tools/tools/14" with body and replace "nextCalibrationDate" by the date "+3 days":
    """
    {
      "nextCalibrationDate": "1970-01-01",
      "statusUpdatedAt": "1970-01-01",
      "calibrationNotice": 5
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to "CALIBRATION_DUE_SOON"
    # Test that is it updated on change status and not taken from the payload
    And the JSON node "statusUpdatedAt" should be newer than 1 minute ago
    And an email should have been sent asynchronously with subject "Quality / Calibrated Tools Notifications"
    And this asynchronous email should be sent only to "user-superuser@tld.fr"
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/calibrated_tools/tools/14" with body and replace "nextCalibrationDate" by the date "+30 days":
    """
    {
      "nextCalibrationDate": "1970-01-01",
      "calibrationNotice": 10
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to "ACTIVE"

  Scenario: Updating a expired tool should be ok for OUT_OF_SERVICE
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/calibrated_tools/tools/15/status" with body:
    """
    {
      "status": "OUT_OF_SERVICE"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to "OUT_OF_SERVICE"

  Scenario: Sending an expired tool under calibration should update its status and create a new calibration log
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/calibrated_tools/tools/16/status" with body:
    """
    {
      "status": "UNDER_CALIBRATION"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to "UNDER_CALIBRATION"
    And the JSON node "calibrationLogs" should have 2 elements

  Scenario: Sending an active tool under calibration should update its status and create a new calibration log
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/calibrated_tools/tools/21/status" with body:
    """
    {
      "status": "UNDER_CALIBRATION"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to "UNDER_CALIBRATION"
    And the JSON node "calibrationLogs" should have 2 elements

  Scenario: Sending a tool which should be calibrated soon under calibration should update its status and create a new calibration log
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/calibrated_tools/tools/20/status" with body:
    """
    {
      "status": "UNDER_CALIBRATION"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to "UNDER_CALIBRATION"
    And the JSON node "calibrationLogs" should have 2 elements

  Scenario: Sending a tool out of service under calibration should update its status and create a new calibration log
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/calibrated_tools/tools/22/status" with body:
    """
    {
      "status": "UNDER_CALIBRATION"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to "UNDER_CALIBRATION"
    And the JSON node "calibrationLogs" should have 2 elements

  Scenario: Updating a expired tool should be ok for SCRAPPED
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/calibrated_tools/tools/17/status" with body:
    """
    {
      "status": "SCRAPPED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to "SCRAPPED"

