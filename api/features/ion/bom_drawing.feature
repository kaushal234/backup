Feature: A CBOM Materials can be fetched through the API

  Scenario: Request a single CBOM without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/drawings/site=640;project=;product=1152805"
    Then the response status code should be 401

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\Drawing" is exposed on the API
    Then the filter "date" should be available and its type should be "string"

  Scenario: Request a single CBOM
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/drawings/site=500;project=;product=1051213?date=2023-01-18T23:59:59-05:00"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txDrawing" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "",
          "product": "1051213",
          "date": "2023-01-18"
        }
      }
    }
    """
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/pdf"

  Scenario: As a vendor user i can request a 3D files drawing on a zip, if no zip i have a 404 error
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/drawing_3d_files/site=400;project=;product=1049951?date=2022-11-02T17:13:00"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txDrawing" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 400,
          "project": "",
          "product": "1049951",
          "date": "2022-11-02T17:13:00"
        }
      }
    }
    """
    Then the response status code should be 404
    And the JSON node "hydra:description" should be equal to "Not Found"

  Scenario: As a vendor user i can request a 3D drawing zip file
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/drawing_3d_files/site=500;project=;product=6500171?date=1989-12-31T23:00:00+00:00"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txDrawing" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "",
          "product": "6500171",
          "date": "1989-12-31T23:00:00 00:00"
        }
      }
    }
    """
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/zip"

  Scenario: As a intranet user i can request a 3D drawing zip file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/drawing_3d_files/site=500;project=;product=6500171?date=1989-12-31T23:00:00+00:00"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txDrawing" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "",
          "product": "6500171",
          "date": "1989-12-31T23:00:00 00:00"
        }
      }
    }
    """
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/zip"

  Scenario: As a extranet user i can request a schematics
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/extranet_drawings/T44981/ESC/site=500;project=;product=1123043"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txDrawing" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "project": "",
          "signalCode": "ESC",
          "site": 500,
          "product": "1123043"
        }
      }
    }
    """
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/pdf"

  Scenario: As a intranet user i can t request a schematics
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/extranet_drawings/T44981/ESC/site=500;project=;product=1123043"
    Then the response status code should be 403

  Scenario: As a vendor user i can t request a schematics
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/extranet_drawings/T44981/ESC/site=500;project=;product=1123043"
    Then the response status code should be 403

  Scenario: As a extranet user i can not request a schematics i m not concerned
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/extranet_drawings/T44981/ESC/site=500;project=;product=11230"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    Then the response status code should be 403

  Scenario: As a extranet user i can request a schematics, if no file i have a 404 error
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/extranet_drawings/T44981/RTD/site=500;project=;product=1122953"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txDrawing" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "project": "",
          "signalCode": "RTD",
          "site": 500,
          "product": "1122953"
        }
      }
    }
    """
    Then the response status code should be 404
    And the JSON node "hydra:description" should be equal to "Not Found"
