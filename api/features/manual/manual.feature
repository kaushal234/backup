Feature: Test Manual API

  Scenario: Request all Manuals without being authenticated
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manuals"
    Then the response status code should be 401

  Scenario: Request all Manuals
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manuals"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manuals/schemas/manuals.json"

  Scenario: Filters are declared on resource Manual
    Given the class "App\Entity\Support\Manual" is exposed on the API
    And the filter "id" should be available and its type should be "int"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "equipmentRecord.serialNumber" should be available and its type should be "string"
    And the filter "documents.parts.partNumber" should be available and its type should be "string"
    And the filter "equipmentRecord.manufacturerLocation" should be available and its type should be "string"
    And the filter "equipmentRecord.product.family" should be available and its type should be "string"
    And the filter "description" should be available and its type should be "string"
    And the filter "order[id]" should be available and its type should be "string"
    And the filter "order[createdAt]" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"

  Scenario: Filters are declared on resource ManualDocument
    Given the class "App\Entity\Support\ManualDocument" is exposed on the API
    And the filter "id" should be available and its type should be "int"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "manual" should be available and its type should be "string"
    And the filter "parts.partNumber" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"

  Scenario: Filters are declared on resource ManualPart
    Given the class "App\Entity\Support\ManualPart" is exposed on the API
    Then the filter "order[document.category.name]" should be available and its type should be "string"
    And the filter "order[document.id]" should be available and its type should be "string"
    And the filter "order[partNumber]" should be available and its type should be "string"
    And the filter "order[position]" should be available and its type should be "string"
    And the filter "preventive" should be available and its type should be "bool"
    And the filter "maintenance" should be available and its type should be "bool"
    And the filter "overhaul" should be available and its type should be "bool"
    And the filter "critical" should be available and its type should be "bool"
    And the filter "q" should be available and its type should be "string"

  Scenario: Manuals collection route should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manuals"
    Then the response status code should be 403

  Scenario: Manuals on item route should be accessible for extranet users if they are authorized on ER (buyer, end user or maintainer)
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manuals/1"
    Then the response status code should be 200
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manuals/4"
    Then the response status code should be 403

  Scenario: Manual Document on item route should be accessible for extranet users if they are authorized on ER (buyer, end user or maintainer)
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manual_documents/4"
    Then the response status code should be 200
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manual_documents/5"
    Then the response status code should be 403

  Scenario: Request a Manual
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manuals/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manuals/schemas/manual.json"

  Scenario: Request a recommended spare Manual Parts list (RSPL)
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manual_parts?document.manual=1&q=1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manual_parts/schemas/manual_parts_with_document_category.json"

  Scenario: Request a list of Manuals filtered by manualParts descriptions (2 fields are matching)
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manuals?normalizationGroups[]=manual_search&itemsPerPage=10&description=TRAY"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manuals/schemas/manuals_search.json"
    And the JSON node "hydra:member[0].id" should be equal to 1
    And the JSON node "hydra:member" should have 1 element
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manuals?normalizationGroups[]=manual_search&itemsPerPage=10&description=LONG"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manuals/schemas/manuals_search.json"
    And the JSON node "hydra:member[0].id" should be equal to 1
    And the JSON node "hydra:member" should have 1 element

  Scenario: Download Manual Chapter4 pdf
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/pdf"
    When I send a "GET" request to "/support/manuals/1/pdf/chapter4"
    Then the response status code should be 200
    Then the header "Content-Type" should be equal to "application/pdf"

  Scenario: Download Manual Chapter4 pdf as extranet user should depend on manual access
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/pdf"
    When I send a "GET" request to "/support/manuals/1/pdf/chapter4"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/pdf"
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/pdf"
    When I send a "GET" request to "/support/manuals/4/pdf/chapter4"
    Then the response status code should be 403

  Scenario: Download Manual Parts list pdf
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/pdf"
    When I send a "GET" request to "/support/manuals/1/pdf/parts_list"
    Then the response status code should be 200
    Then the header "Content-Type" should be equal to "application/pdf"

  Scenario: Download Manual Document pdf
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/pdf"
    When I send a "GET" request to "/support/manual_documents/1/pdf/document"
    Then the response status code should be 200
    Then the header "Content-Type" should be equal to "application/pdf"

  Scenario: Download Manual Document pdf as extranet user should be possible if access to manual is granted
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/pdf"
    When I send a "GET" request to "/support/manual_documents/1/pdf/document"
    Then the response status code should be 200
    Then the header "Content-Type" should be equal to "application/pdf"
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/pdf"
    When I send a "GET" request to "/support/manual_documents/5/pdf/document"
    Then the response status code should be 403

  Scenario: Download a Manual Zip
    Given I authenticate as the intranet user "user-basic@tld.fr"
    Given I add "Accept" header equal to "application/zip"
    When I send a "GET" request to "/support/manuals/1/zip"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/zip"

  Scenario: As a basic user, creating a Manual should not be allowed
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/manuals"
    Then the response status code should be 403

  Scenario: As a basic user, updating a Manual should not be allowed
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/support/manuals/1"
    Then the response status code should be 403

  Scenario: As a basic user, delete a Manual should not be allowed
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/support/manuals/1"
    Then the response status code should be 403

  Scenario: Requesting the creation of a Manual from an ER without project number and parameter force false should fail
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/manuals" with body:
    """
    {
      "mainEquipmentRecord": "/equipment_records/16"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should contain "Equipment project number is missing"

  Scenario: Requesting the creation of a Manual from an ER without project number and parameter force true should fail
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/manuals" with body:
    """
    {
      "mainEquipmentRecord": "/equipment_records/16",
      "force": true
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should contain "Equipment project number is missing"

  Scenario: Trying to force create a Manual without previously requesting a dry run which didn't return any critical violations, should fail
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/manuals" with body:
    """
    {
      "mainEquipmentRecord": "/equipment_records/18",
      "force": true,
      "language": "en"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "equipmentRecord.publishable"
    And the JSON node "violations[0].message" should contain "This value should be true"

#  Scenario: Requesting the creation of a Manual from a valid ER without PDF on the vault should return critical violation and fail
#    Given I authenticate as the intranet user "user-superuser@tld.fr"
#    And I add "Accept" header equal to "application/ld+json"
#    And I add "Content-type" header equal to "application/ld+json"
#    When I send a "POST" request to "/support/manuals" with body:
#    """
#    {
#      "mainEquipmentRecord": "/equipment_records/15"
#    }
#    """
#    Then the response status code should be 400
#    And the JSON node "@type" should be equal to "ConstraintViolation"
#    And the JSON node "violations[0].propertyPath" should be equal to "documents[0]"
#    And the JSON node "violations[0].message" should contain "PDF file not found for this item"
#    And the JSON node "violations[0].payload.severity" should be equal to "critical"

  Scenario: Requesting the creation of a Manual from a valid ER without setting the force parameter to true, should return a 204.
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/manuals" with body:
    """
    {
      "mainEquipmentRecord": "/equipment_records/18",
      "language": "en"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "documents[29]"
    And the JSON node "violations[0].message" should contain "JPG file not found for this item"
    And the JSON node "violations[0].payload.severity" should be equal to "warning"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txCBOMManual" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "T70021",
          "date": "{{ today }}",
          "signalCodeFilter": "CH0|CH1|CH2|CH3|CH5",
          "signalCodeFilterMethod": "Equals",
          "signalCodeAttribute": "engineeringSignalCode",
          "otherLanguage": "en"
        }
      }
    }
    """
    And this client has been called on the operation "txCBOMManual" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "T70021",
          "date": "2022-03-03",
          "signalCodeFilter": "SPA|SPB|SPC|SPD|SPE|SPF|SPG|SPH|SPI|SPJ|SPK|SPL|SPM|SPN|IGA|IGB|IGC|IGD|IGE|IGF|IGG|IGH|IGI|IGJ|IGK|IGL|IGM|IGN|IEA|IEB|IEC|IED|IEE|IEF|IEG|IEH|IEI|IEJ|IEK|IEL|IEM|IEN|IHA|IHB|IHC|IHD|IHE|IHF|IHG|IHH|IHI|IHJ|IHK|IHL|IHM|IHN|IMA|IMB|IMC|IMD|IME|IMF|IMG|IMH|IMI|IMJ|IMK|IML|IMM|IMN",
          "signalCodeFilterMethod": "Equals",
          "signalCodeAttribute": "engineeringSignalCode",
          "otherLanguage": "en"
        }
      }
    }
    """
    And a total of 3 request has been sent to ION
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records/18"
    Then the response status code should be 200
    And the JSON node "publishable" should be true

  Scenario: The values passed when trying to create a manual must be valid.
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/manuals" with body:
    """
    {
      "language": "ZUT",
      "status": "3eme"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "mainEquipmentRecord"
    And the JSON node "violations[0].message" should contain "This value should not be null."
    And the JSON node "violations[1].propertyPath" should be equal to "language"
    And the JSON node "violations[1].message" should contain "ZUT is not a valid language"
    And the JSON node "violations[2].propertyPath" should be equal to "status"
    And the JSON node "violations[2].message" should contain "The value you selected is not a valid choice."

  Scenario: Force create a Manual from a valid ER should return a 201
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/manuals" with body:
    """
    {
      "mainEquipmentRecord": "/equipment_records/18",
      "force": true,
      "language": "en"
    }
    """
    Then the response status code should be 201
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txCBOMManual" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "T70021",
          "date": "2022-03-03",
          "signalCodeFilter": "ESC|HSC|PSC|BSC|FLD|RTD|PPD|PRG|PRM|GAD",
          "signalCodeFilterMethod": "Equals",
          "signalCodeAttribute": "engineeringSignalCode",
          "otherLanguage": "en"
        }
      }
    }
    """
    And the JSON node "documents" should have 157 elements
    And the JSON node "documents[0].category.name" should contain "Chapter 0"
    And the JSON node "documents[0].parts" should have 0 elements
    And the JSON node "documents[43].factoryNumber" should contain "1122572"
    And the JSON node "documents[43].parts" should have 3 elements
    And the JSON node "documents[43].position" should be equal to the number 44
    And the JSON node "documents[1].document" should not be null
    And the JSON node "documents[2].document" should not be null
#    And the JSON node "documents[3].document" should not exist
#    And the JSON node "documents[12].document" should not exist
#    And the JSON node "documents[19].document" should not exist
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records/18"
    And the response status code should be 200
    And the JSON node "manuals" should have 1 elements

  Scenario: Force create a Manual from a valid ER with status PRELIMINARY should return manual only with chapters
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/manuals" with body:
    """
    {
      "mainEquipmentRecord": "/equipment_records/18",
      "force": true,
      "language": "en",
      "status": "PRELIMINARY"
    }
    """
    Then the response status code should be 201
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txCBOMManual" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 500,
          "project": "T70021",
          "date": "2022-03-03",
          "signalCodeFilter": "ESC|HSC|PSC|BSC|FLD|RTD|PPD|PRG|PRM|GAD",
          "signalCodeFilterMethod": "Equals",
          "signalCodeAttribute": "engineeringSignalCode",
          "otherLanguage": "en"
        }
      }
    }
    """
    And the JSON node "documents" should have 29 elements
    And the JSON node "documents[0].category.name" should contain "Chapter 0"
    And the JSON node "documents[0].parts" should have 0 elements
    And the JSON node "documents[43].factoryNumber" should not exist
    And the JSON node "documents[1].document" should not be null
    And the JSON node "documents[2].document" should not be null
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records/18"
    And the response status code should be 200
    And the JSON node "manuals" should have 2 elements

  Scenario: Force create a new Manual from a valid ER and add secondary ER should return a 201
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/manuals" with body:
    """
    {
      "mainEquipmentRecord": "/equipment_records/18",
      "force": true,
      "secondaryEquipmentRecords": [
          "/equipment_records/17"
      ]
    }
    """
    Then the response status code should be 201
    And a message of class "App\Message\Support\ManualDuplication" should have been sent in the bus
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records/18"
    And the response status code should be 200
    And the JSON node "manuals" should have 3 elements

  Scenario: Force a second Manual should not duplicate equipment record serials
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records/18"
    And the response status code should be 200
    And the JSON node "serials" should have 9 elements
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/manuals" with body:
    """
    {
      "mainEquipmentRecord": "/equipment_records/18",
      "force": true,
      "secondaryEquipmentRecords": [
          "/equipment_records/17"
      ]
    }
    """
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records/18"
    And the response status code should be 200
    # A new serial is created for the Manual, but not the schematics
    And the JSON node "serials" should have 10 elements

  Scenario: Trying create a Manual on an ER that has no CBOM, should fail
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/manuals" with body:
    """
    {
      "mainEquipmentRecord": "/equipment_records/7",
    }
    """
    Then the response status code should be 400
    And the JSON node "@type" should be equal to "hydra:Error"

  Scenario: Update a Manual and populate collections should return a 200
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/support/manuals/4" with body:
    """
    {
      "description": "this is my long description",
      "features": "Can you realy see the feature ?",
      "status": "RELEASED",
      "documents": [
        {
          "position": 3,
          "factoryNumber": "fn-00003",
          "revision": "B",
          "type": "MANUAL SECTION",
          "category": "/support/manual_document_categories/1",
          "description": "I like to ",
          "otherDescription": "move it move it",
          "parts": [
            {
              "position": 1,
              "partNumber": "numeroUno1",
              "quantity": 20,
              "preventive": true
            },
            {
              "position": 2,
              "partNumber": "numeroUnoDos",
              "quantity": 21.2,
              "maintenance": true
            }
          ]
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manuals/schemas/manual.json"
    And the JSON node "description" should be equal to "this is my long description"
    And the JSON node "features" should be equal to "Can you realy see the feature ?"
    And the JSON node "documents" should have 1 element
    And the JSON node "documents[0].position" should be equal to 3
    And the JSON node "documents[0].type" should be equal to "MANUAL SECTION"
    And the JSON node "documents[0].parts" should have 2 elements
    And the JSON node "documents[0].parts[0].position" should be equal to 1
    And the JSON node "documents[0].parts[0].partNumber" should be equal to "numeroUno1"
    And the JSON node "documents[0].parts[0].quantity" should be equal to 20
    And the JSON node "documents[0].parts[0].preventive" should be true
    And the JSON node "documents[0].parts[0].maintenance" should be false
    And the JSON node "documents[0].parts[1].position" should be equal to 2
    And the JSON node "documents[0].parts[1].partNumber" should be equal to "numeroUnoDos"
    And the JSON node "documents[0].parts[1].quantity" should be equal to 21.2
    And the JSON node "documents[0].parts[1].preventive" should be false
    And the JSON node "documents[0].parts[1].maintenance" should be true

  Scenario: Create a Manual without EquipmentRecord should be denied
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/manuals" with body:
    """
    {
      "description": "this is my long description",
      "features": "Can you realy see the feature ?",
      "status": "RELEASED"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "mainEquipmentRecord"
    And the JSON node "violations[0].message" should contain "This value should not be null"

  Scenario: When updating a Manual, only some authorized properties should be available for update
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/fields?iri=/support/manuals/1&method=PUT"
    Then the response status code should be 200
    Then the JSON should be equal to:
    """
    [
      "description",
      "features",
      "language",
      "status",
      "documents",
      "prints"
    ]
    """

  Scenario: Update a Manual, add document to its collection, and update an element by adding a part to it
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/support/manuals/4" with body:
    """
    {
      "status": "PRELIMINARY",
      "documents": [
        {
          "@id": "/support/manual_documents/475",
          "revision": "new",
          "parts": [
            {
              "position": 6,
              "partNumber": "numeroTres3",
              "quantity": 14,
              "preventive": true
            }
          ]
        },
        {
          "position": 8,
          "factoryNumber": "gate C",
          "revision": "C",
          "type": "MANUAL SECTION",
          "category": "/support/manual_document_categories/1"
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manuals/schemas/manual.json"
    And the JSON node "status" should be equal to "PRELIMINARY"
    And the JSON node "documents" should have 2 elements
    And the JSON node "documents[0].revision" should be equal to "new"
    And the JSON node "documents[0].parts" should have 1 element
    And the JSON node "documents[0].parts[0].position" should be equal to 6
    And the JSON node "documents[0].parts[0].partNumber" should be equal to "numeroTres3"
    And the JSON node "documents[0].parts[0].quantity" should be equal to 14
    And the JSON node "documents[0].parts[0].preventive" should be true
    And the JSON node "documents[1].position" should be equal to 8
    And the JSON node "documents[1].factoryNumber" should be equal to "gate C"
    And the JSON node "documents[1].revision" should be equal to "C"
    And the JSON node "documents[1].type" should be equal to "MANUAL SECTION"

  Scenario: Update a Manual by updating one of its document and delete its collection of parts
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/support/manuals/4" with body:
    """
    {
      "status": "PRELIMINARY",
      "documents": [
        {
          "@id": "/support/manual_documents/475",
          "parts": []
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manuals/schemas/manual.json"
    And the JSON node "status" should be equal to "PRELIMINARY"
    And the JSON node "documents[0].parts" should have 0 element
    And the JSON node "documents" should have 1 element

  Scenario: Update a Manual and delete its collection of documents
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/support/manuals/4" with body:
    """
    {
      "status": "PRELIMINARY",
      "documents": []
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manuals/schemas/manual.json"
    And the JSON node "status" should be equal to "PRELIMINARY"
    And the JSON node "documents" should have 0 element

  Scenario: Delete a Manual
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/support/manuals/3"
    Then the response status code should be 204

  @resetFileTable
  Scenario: Basic users can't upload a manual document file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/support/manual_documents/1/files" with file "file" "file.pdf"
    Then the response status code should be 403

  Scenario: Extranet users can't upload a manual document file
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/support/manual_documents/1/files" with file "file" "file.pdf"
    Then the response status code should be 403

  Scenario: Uploading a PDF file on a part diagram manual document should fail
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/support/manual_documents/1/files" with file "file" "file.pdf"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "document: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "document"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: Upload a manual document
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/support/manual_documents/1/files" with file "file" "image_1200x1200.jpeg"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: Upload a manual document
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/support/manual_documents/2/files" with file "file" "image_1200x1200.jpeg"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: As a basic user, I can download an manual document file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manual_documents/1/files/1"
    Then the response status code should be 200

  Scenario: As a authorized application, I can download an manual document file with categoryName : Chapter 1
    Given I authenticate as the authorized application "EXTRANET"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manual_documents/1/files/1"
    Then the response status code should be 200

  Scenario: As a authorized application, I can't download an manual document file with categoryName : Chapter 2
    Given I authenticate as the authorized application "EXTRANET"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manual_documents/2/files/2"
    Then the response status code should be 403

  Scenario: As an extranet user, I can download an manual document file I have access to
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/support/manual_documents/6/files" with file "file" "file.pdf"
    Then the response status code should be 201
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manual_documents/6/files/3"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/support/manual_documents/2/files" with file "file" "image_1200x1200.jpeg"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manual_documents/2/files/4"
    Then the response status code should be 200

  Scenario: Basic users can't delete a manual document file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/support/manual_documents/1/files/1"
    Then the response status code should be 403

  Scenario: Delete a manual document file
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I send a "DELETE" request to "/support/manual_documents/1/files/1"
    Then the response status code should be 204
    And I add "Accept" header equal to "application/ld+json"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manuals/1"
    And the response status code should be 200
    And the JSON node "documents[0].document" should have 0 element

  Scenario: On delete document, this file must be deleted
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/support/manuals/4" with body:
    """
    {
      "documents": []
    }
    """
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manual_documents/1/files/2"
    And the response status code should be 404

  Scenario: generated files must be cleaned after tests
    Then I delete all the files created during test

  Scenario: Basic user can access to manual with no equipment record
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/manuals/5"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manuals/schemas/manual.json"

  Scenario: Normally allowed user should not be able to edit a manual without equipment record
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/support/manuals/5" with body:
    """
    {
      "description": "this is my long description"
    }
    """
    Then the response status code should be 403

  Scenario: Only MOO of PUBS should be allowed to update a manual with no equipment record
    Given I authenticate as the intranet user "user-moo-cat@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/support/manuals/5" with body:
    """
    {
      "description": "this is my long description"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manuals/schemas/manual.json"
    And the JSON node "description" should be equal to "this is my long description"