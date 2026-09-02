Feature: Test tools API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Quality\CalibratedTools\Tool" should only be available for intranet user

  Scenario: Request all tools
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/calibrated_tools/tools"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/quality/calibrated_tools/tool/schemas/tools.json"

  Scenario: Request a single tool
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/calibrated_tools/tools/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/quality/calibrated_tools/tool/schemas/tool.json"

  Scenario: Update a given tool - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/calibrated_tools/tools/1" with the body "tests/fixtures/json/quality/calibrated_tools/tool/dummies/put.json"
    Then the response status code should be 403

  Scenario: Update a given tool - permissions for QAM
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/calibrated_tools/tools/1" with the body "tests/fixtures/json/quality/calibrated_tools/tool/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/quality/calibrated_tools/tool/schemas/tool.json"
    And the JSON node "serialNumber" should be equal to "1234567"
    And the JSON node "nextCalibrationDate" should be equal to "2099-12-01T08:35:05-05:00"

  Scenario: Update a given tool - invalid next calibration date
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/calibrated_tools/tools/1" with body:
    """
    {
      "nextCalibrationDate": "1998-05-07T08:35:05"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "nextCalibrationDate: This value should be in the future"
    And the JSON node "violations[0].propertyPath" should be equal to "nextCalibrationDate"
    And the JSON node "violations[0].message" should contain "This value should be in the future"

  Scenario: Create a tool - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/calibrated_tools/tools" with the body "tests/fixtures/json/quality/calibrated_tools/tool/dummies/post.json"
    Then the response status code should be 403

  Scenario: Create a tool - permissions for QAM
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/calibrated_tools/tools" with the body "tests/fixtures/json/quality/calibrated_tools/tool/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/quality/calibrated_tools/tool/schemas/tool.json"
    And the JSON nodes should be equal to:
      | serialNumber | 123456789 |
      | description | description tool |
      | calibrationInterval | 11 |
      | calibrationNotice | 20 |
      | purchasingDate | 2016-12-02T20:30:05-05:00 |
      | vendorErp | Erp |
      | vendorName | Name |
      | status | OUT_OF_SERVICE |
      | vendorId | 123nnn |
      | toolType.@id | /quality/calibrated_tools/tool_types/1 |
      | locationArea.@id | /location_areas/2 |
      | createdBy.@id | /people/29 |

  Scenario: Create a tool - permissions for MPE
    Given I authenticate as the intranet user "user-mpe@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/calibrated_tools/tools" with the body "tests/fixtures/json/quality/calibrated_tools/tool/dummies/post2.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/quality/calibrated_tools/tool/schemas/tool.json"
    And the JSON nodes should be equal to:
      | serialNumber | 987654321 |
      | description | description tool for MPE |
      | calibrationInterval | 11 |
      | calibrationNotice | 20 |
      | purchasingDate | 2017-04-26T20:30:05-04:00 |
      | vendorErp | Erp |
      | vendorName | Name |
      | status | OUT_OF_SERVICE |
      | vendorId | 123nnn |
      | toolType.@id | /quality/calibrated_tools/tool_types/1 |
      | locationArea.@id | /location_areas/2 |
      | createdBy.@id | /people/30 |

  Scenario: Create a tool - permissions for QE
    Given I authenticate as the intranet user "user-qe@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/calibrated_tools/tools" with the body "tests/fixtures/json/quality/calibrated_tools/tool/dummies/postQE.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/quality/calibrated_tools/tool/schemas/tool.json"
    And the JSON nodes should be equal to:
      | serialNumber | 02468642 |
      | description | description tool for QE |
      | calibrationInterval | 11 |
      | calibrationNotice | 20 |
      | purchasingDate | 2017-11-25T20:30:05-05:00 |
      | vendorErp | Erp |
      | vendorName | Name |
      | status | OUT_OF_SERVICE |
      | vendorId | 123nnn |
      | toolType.@id | /quality/calibrated_tools/tool_types/1 |
      | locationArea.@id | /location_areas/2 |
      | createdBy.@id | /people/43 |

  Scenario: Create a tool - permissions for PS
    Given I authenticate as the intranet user "user-ps@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/calibrated_tools/tools" with the body "tests/fixtures/json/quality/calibrated_tools/tool/dummies/postPS.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/quality/calibrated_tools/tool/schemas/tool.json"
    And the JSON nodes should be equal to:
      | serialNumber | 24686420 |
      | description | description tool for PS |
      | calibrationInterval | 11 |
      | calibrationNotice | 20 |
      | purchasingDate | 2017-11-25T20:30:05-05:00 |
      | vendorErp | Erp |
      | vendorName | Name |
      | status | OUT_OF_SERVICE |
      | vendorId | 123nnn |
      | toolType.@id | /quality/calibrated_tools/tool_types/1 |
      | locationArea.@id | /location_areas/2 |
      | createdBy.@id | /people/44 |

  Scenario: Delete a tool- insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/calibrated_tools/tools/1"
    Then the response status code should be 403

  Scenario: Delete a tool- permissions OK
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/calibrated_tools/tools/3"
    Then the response status code should be 204

  Scenario: Email a given tool
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I send a "POST" request to "/mailer" with body:
    """
    {
        "iri": "/quality/calibrated_tools/tools/1",
        "to": ["to1@chuck.norris", "to2@chuck.norris"],
        "cc": ["cc@chuck.norris"],
        "bcc": ["bcc@chuck.norris"]
    }
    """
    Then the response status code should be 204
    And an email should have been sent asynchronously with subject "Tool subject"
    And this asynchronous email should be sent to "to1@chuck.norris"
    And this asynchronous email should be sent to "to2@chuck.norris"
    And this asynchronous email should be sent as cc to "cc@chuck.norris"
    And this asynchronous email should be sent as bcc to "bcc@chuck.norris"
    And this asynchronous email should contain "first test tool from fixtures"

  Scenario: Email a tool and add the link
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I send a "POST" request to "/mailer" with body:
    """
    {
        "iri": "/quality/calibrated_tools/tools/1",
        "to": ["to1@chuck.norris"],
        "link": "https://www.tld-gse.com/en/private/quality/calibrated-tools/tools/1/show"
    }
    """
    Then the response status code should be 204
    And an email should have been sent asynchronously with subject "Tool subject"
    And this asynchronous email should contain 'href="https://www.tld-gse.com/en/private/quality/calibrated-tools/tools/1/show"'
    And this asynchronous email body should contain a link to "https://www.tld-gse.com/en/private/quality/calibrated-tools/tools/1/show"
