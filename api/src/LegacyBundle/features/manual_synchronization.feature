Feature: Test Manual double writing

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Trying to generate a manual that is publishable should double write a property publishable of ER in the legacy database
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "POST" request to "/support/manuals" with body:
    """
    {
      "mainEquipmentRecord": "/equipment_records/18"
    }
    """
    Then the response status code should be 422
    And the column "publishable" from the "service" legacy table has been updated with string "Y"

  Scenario: Update a manual should double write in the legacy database
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "PUT" request to "/support/manuals/2" with body:
    """
    {
      "equipmentRecord": "/equipment_records/2",
      "equipmentSerial": "/equipment_serials/2",
      "description": "this is my very long description2",
      "features": "Can you realy see the future",
      "language": "fr",
      "status": "PRELIMINARY",
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
              "position": 4,
              "partNumber": "numeroUno1",
              "quantity": 20,
              "preventive": true
            },
            {
              "position": 5,
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
    And the JSON node "@id" should be equal to the string "/support/manuals/2"
    And the JSON node "legacyId" should be equal to the number 17328
    And the JSON node "description" should be equal to the string "this is my very long description2"
    And the JSON node "features" should be equal to the string "Can you realy see the future"
    And the JSON node "language" should be equal to the string "fr"
    And the JSON node "status" should be equal to the string "PRELIMINARY"
    And the column "description" from the "manuals" legacy table has been updated with string "this is my very long description2"
    And the column "features" from the "manuals" legacy table has been updated with string "Can you realy see the future"
    And the column "lang" from the "manuals" legacy table has been updated with string "fr"
    And the column "status" from the "manuals" legacy table has been updated with string "PRELIMINARY"
    And a new row has been inserted in the legacy table "manuals_diag"
    And the column "factory_num" from the "manuals_diag" legacy table has been inserted with string "fn-00003"
    And the column "rev" from the "manuals_diag" legacy table has been inserted with string "B"
    And the column "category" from the "manuals_diag" legacy table has been inserted with string "Chapter 1"
    And the column "doc_type" from the "manuals_diag" legacy table has been inserted with string "MANUAL SECTION"
    And the column "endescription" from the "manuals_diag" legacy table has been inserted with string "I like to "
    And the column "frdescription" from the "manuals_diag" legacy table has been inserted with string "move it move it"
    And 2 new rows have been inserted in the legacy table "manuals_parts"
    And the column "item" from the "manuals_parts" legacy table has been inserted with string "4"
    And the column "pn" from the "manuals_parts" legacy table has been inserted with string "numeroUno1"
    And the column "qty" from the "manuals_parts" legacy table has been inserted with string "20"
    And the column "group_p" from the "manuals_parts" legacy table has been inserted with string "P"

  Scenario: Update a Manual should double write in the legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/support/manuals/2" with body:
    """
    {
      "equipmentRecord": "/equipment_records/8",
      "documents": [
        {
          "@id": "/support/manual_documents/6",
          "revision": "L",
          "position": 23,
          "parts": [
            {
              "@id": "/support/manual_parts/6"
            }
          ]
        },
        {
          "position": 28,
          "factoryNumber": "stargate",
          "revision": "G",
          "type": "MANUAL SECTION",
          "category": "/support/manual_document_categories/1"
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manuals/schemas/manual.json"
    And the JSON node "equipmentRecord.@id" should be equal to "/equipment_records/2"
    And the column "rev" from the "manuals_diag" legacy table has been updated with string "L"
    And the column "item" from the "manuals_docs" legacy table has been updated with string "23"
    And the column "item" from the "manuals_docs" legacy table has been inserted with string "28"
    And the column "factory_num" from the "manuals_diag" legacy table has been inserted with string "stargate"
    And the column "rev" from the "manuals_diag" legacy table has been inserted with string "G"
    And table "manual_parts" has not been inserted
    Then a row has been deleted in the legacy table "manuals_parts"

  Scenario: Force create a Manual from a valid ER and on secondary ERs should return a 201
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "POST" request to "/support/manuals" with body:
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
    And 1 new rows have been inserted in the legacy table "manuals"
    And 157 new rows have been inserted in the legacy table "manuals_docs"
    And 157 new rows have been inserted in the legacy table "manuals_diag"
    And 1202 new rows have been inserted in the legacy table "manuals_parts"
    And 7 new rows have been inserted in the legacy table "service_serials"
    And the column "component" from the "service_serials" legacy table has been inserted with string "MANUAL"
    And the column "serial" from the "service_serials" legacy table has been inserted with string "17332"

  @resetFileTable
  Scenario: Update Manual Document file
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/manual_documents/1/files" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 201
    And the column "diagram_filename" from the "manuals_diag" legacy table has been updated

  Scenario: Delete a Manual should double write in the legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/support/manuals/6"
    Then the response status code should be 204
    Then a row has been deleted in the legacy table "service_serials"
    And 157 rows have been deleted in the legacy table "manuals_docs"
    And 1202 row have been deleted in the legacy table "manuals_parts"

  Scenario: Delete Manual Document file
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/support/manual_documents/1/files/1"
    Then the response status code should be 204
    And the column "diagram_filename" from the "manuals_diag" legacy table has been updated with string ""

  Scenario: Create a ManualPrint should double write in the legacy database
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/manual_prints" with body:
    """
    {
      "manual": "/support/manuals/1",
      "manualPrinter": "/support/manual_printers/1",
      "requestedDeliveryDate": "2022-02-15 07:59:00",
      "standard": 1,
      "full": 2,
      "extra": 3,
      "chapter5": 14,
      "comment": "double printerest me in the legacy please"
    }
    """
    Then the response status code should be 201
    And the JSON node "legacyId" should be equal to the number 165
    And the JSON node "manual.@id" should be equal to the string "/support/manuals/1"
    And the JSON node "manualPrinter.id" should be equal to 1
    And the JSON node "requestedDeliveryDate" should be equal to "2022-02-15T07:59:00-05:00"
    And the JSON node "standard" should be equal to 1
    And the JSON node "full" should be equal to 2
    And the JSON node "extra" should be equal to 3
    And the JSON node "chapter5" should be equal to 14
    And a new row has been inserted in the legacy table "manuals_downloads"
    And the column "parent_id" from the "manuals_downloads" legacy table has been inserted with integer 17327
    And the column "comment" from the "manuals_downloads" legacy table has been inserted with string "double printerest me in the legacy please"
    And the column "std_manual" from the "manuals_downloads" legacy table has been inserted with integer 1
    And the column "full_manual" from the "manuals_downloads" legacy table has been inserted with integer 2
    And the column "extra_cd" from the "manuals_downloads" legacy table has been inserted with integer 3
    And the column "chapter_5" from the "manuals_downloads" legacy table has been inserted with integer 14
    And the column "dt_delivery" from the "manuals_downloads" legacy table has been inserted with string "2022-02-15"
    And the column "dt_entered" from the "manuals_downloads" legacy table has been inserted with a string containing today's date
    And the column "poster_id" from the "manuals_downloads" legacy table has been inserted with integer 2359
    And the column "er_id" from the "manuals_downloads" legacy table has been inserted with integer 37455

  # TODO , update and delete, if needed
