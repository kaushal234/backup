Feature: Test acronym double write API

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create a TOC SPR in legacy database
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "POST" request to "/parts/toc_spare_parts_requests" with body:
    """
    {
      "technicianOnCall": "/service/technician_on_calls/16",
      "activity": "Troubleshooting",
      "type": "Payable Services",
      "sph": "/locations/27",
      "sso": "/locations/28",
      "factory": "/locations/29",
      "erpLocation": "/locations/29",
      "customer": "/sales/customers/31",
      "airport": "/airports/62",
      "equipmentRecords": ["/equipment_records/1"],
      "parts": [
        {
          "partNumber": "WW2006",
          "description": "Willy Waller",
          "unitOfMeasure": "BTC",
          "quantity": 42
        },
        {
          "partNumber": "LN2022",
          "description": "Challenger",
          "unitOfMeasure": "TBD",
          "quantity": 12
        }
      ],
      "estimatedShippingDate": "2083-04-19",
      "deliveryAddress": "/parts/spare_parts_request_delivery_addresses/1"
    }
    """
    Then the response status code should be 201
    And the JSON node "legacyId" should be equal to the number 9007
    And the JSON node "parts[0].@id" should be equal to the string "/parts/spare_parts_request_parts/11"
    And the JSON node "parts[0].partNumber" should be equal to the string "WW2006"
    And the JSON node "parts[1].@id" should be equal to the string "/parts/spare_parts_request_parts/12"
    And the JSON node "parts[1].partNumber" should be equal to the string "LN2022"
    Then a new row has been inserted in the legacy table "spr"
    Then 2 new rows have been inserted in the legacy table "spr_lines"
    And the column "dt" from the "spr" legacy table has been inserted with a string containing today's date
    And the column "parent_id" from the "spr" legacy table has been inserted with integer 0
    And the column "status" from the "spr" legacy table has been inserted with string "PENDING"
    And the column "entered_by" from the "spr" legacy table has been inserted with integer 2359
    And the column "assignor" from the "spr" legacy table has been inserted with integer 2359
    And the column "sso_id" from the "spr" legacy table has been inserted with integer 72
    And the column "psr_id" from the "spr" legacy table has been inserted with integer 0
    And the column "sph_id" from the "spr" legacy table has been inserted with integer 71
    And the column "cust_nama" from the "spr" legacy table has been inserted with string "customer_for_er"
    And the column "estimated_shipping_date" from the "spr" legacy table has been inserted with string "2083-04-19"
    And the column "item" from the "spr_lines" legacy table has been inserted with string "WW2006"
    And the column "dsca" from the "spr_lines" legacy table has been inserted with string "Willy Waller"
    And the column "oqua" from the "spr_lines" legacy table has been inserted with integer 42
    And the column "um" from the "spr_lines" legacy table has been inserted with string "BTC"
    And the column "parent_id" from the "spr_lines" legacy table has been inserted with integer 9007
    And the column "item" from the "spr_lines" legacy table has been inserted with string "LN2022"
    And the column "dsca" from the "spr_lines" legacy table has been inserted with string "Challenger"
    And the column "oqua" from the "spr_lines" legacy table has been inserted with integer 12
    And the column "um" from the "spr_lines" legacy table has been inserted with string "TBD"
    And the column "parent_id" from the "spr_lines" legacy table has been inserted with integer 9007
    And the column "parts_added" from the "toc" legacy table has been updated with integer 1

  Scenario: Create a SB SPR in legacy database
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "POST" request to "/parts/sb_spare_parts_requests" with body:
    """
    {
      "sbId": 58,
      "activity": "Troubleshooting",
      "type": "Payable Services",
      "sph": "/locations/27",
      "sso": "/locations/28",
      "factory": "/locations/29",
      "erpLocation": "/locations/29",
      "customer": "/sales/customers/31",
      "airport": "/airports/62",
      "equipmentRecords": ["/equipment_records/1", "/equipment_records/9"],
      "parts": [
        {
          "partNumber": "WW2006",
          "description": "Willy Waller",
          "unitOfMeasure": "BTC",
          "quantity": 42
        },
        {
          "partNumber": "LN2022",
          "description": "Challenger",
          "unitOfMeasure": "TBD",
          "quantity": 12
        }
      ],
      "deliveryAddress": "/parts/spare_parts_request_delivery_addresses/1"
    }
    """
    Then the response status code should be 201
    And the JSON node "legacyId" should be equal to the number 9008
    And the JSON node "equipmentRecords[0].@id" should be equal to the string "/equipment_records/1"
    And the JSON node "equipmentRecords[0].legacyId" should be equal to the number 37455
    And the JSON node "equipmentRecords[1].@id" should be equal to the string "/equipment_records/9"
    And the JSON node "equipmentRecords[1].legacyId" should be equal to the number 37463
    Then a new row has been inserted in the legacy table "spr"
    Then 2 new rows have been inserted in the legacy table "spr_lines"
    And the column "spr_id" from the "sb_lines" legacy table has been updated with integer 9008

  Scenario: Update a SPR in legacy database
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/toc_spare_parts_requests/6" with body:
    """
    {
      "salesOrder": "PV0000011",
      "parts": [
        {
          "@id": "/parts/spare_parts_request_parts/11",
          "partNumber": "LN2022_FAIL"
        },
        {
          "partNumber": "LN2028",
          "description": "Something went wrong",
          "unitOfMeasure": "KO",
          "quantity": 666
        }
      ]
    }
    """
    Then the response status code should be 200
    Then a new row has been inserted in the legacy table "spr_lines"
    And the column "status" from the "spr" legacy table has been updated with string "OPEN"
    And the column "item" from the "spr_lines" legacy table has been inserted with string "LN2028"
    And the column "dsca" from the "spr_lines" legacy table has been inserted with string "Something went wrong"
    And the column "oqua" from the "spr_lines" legacy table has been inserted with integer 666
    And the column "um" from the "spr_lines" legacy table has been inserted with string "KO"
    And the column "item" from the "spr_lines" legacy table has been updated with string "LN2022_FAIL"
    And the column "oqua" from the "spr_lines" legacy table has been updated with integer 0

  Scenario: Update a SPR in legacy database
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/toc_spare_parts_requests/6" with body:
    """
    {
      "parts": [
        {
          "@id": "/parts/spare_parts_request_parts/11"
        }
      ]
    }
    """
    Then the response status code should be 200
    And the column "oqua" from the "spr_lines" legacy table has been updated with integer 0

  Scenario: Update a SPR status
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/spare_parts_requests/6/status" with body:
    """
    {
       "status": "SHIPPED"
    }
    """
    Then the response status code should be 200
    And the column "status" from the "spr" legacy table has been updated with string "SHIPPED"
    And the column "dt_ship" from the "spr" legacy table has been updated


  Scenario: Create a TOC Warranty SPR should double write parts in the legacy database
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "POST" request to "/parts/toc_spare_parts_requests" with body:
    """
    {
      "technicianOnCall": "/service/technician_on_calls/16",
      "activity": "Troubleshooting",
      "type": "Warranty",
      "sph": "/locations/27",
      "sso": "/locations/28",
      "factory": "/locations/29",
      "erpLocation": "/locations/29",
      "customer": "/sales/customers/31",
      "airport": "/airports/62",
      "equipmentRecords": ["/equipment_records/1"],
      "parts": [
        {
          "partNumber": "WW2006",
          "description": "Willy Waller",
          "unitOfMeasure": "BTC",
          "quantity": 42
        },
        {
          "partNumber": "LN2022",
          "description": "Challenger",
          "unitOfMeasure": "TBD",
          "quantity": 12
        }
      ],
      "estimatedShippingDate": "2083-04-19",
      "deliveryAddress": "/parts/spare_parts_request_delivery_addresses/1"
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to the string "/parts/toc_spare_parts_requests/8"
    And the JSON node "legacyId" should be equal to the number 9009
    Then a new row has been inserted in the legacy table "spr"
    Then 2 new rows have been inserted in the legacy table "spr_lines"
    # TODO : Re-implement warranty double write when Migrated TOC is linked to a WC
    # Then 2 new rows have been inserted in the legacy table "warranty_parts"

  Scenario: Update a TOC Warranty SPR should double write parts in the legacy database
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "PUT" request to "/parts/toc_spare_parts_requests/8" with body:
    """
    {
      "parts": [
        {
          "partNumber": "WW2006",
          "description": "NEW Willy Waller",
          "unitOfMeasure": "BTC",
          "quantity": 42
        },
        {
          "partNumber": "LN2022",
          "description": "Challenger II the rising",
          "unitOfMeasure": "TBD",
          "quantity": 12
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON node "legacyId" should be equal to the number 9009
    # TODO : Re-implement warranty double write when Migrated TOC is linked to a WC
    #And the column "part_description" from the "warranty_parts" legacy table has been updated with string "NEW Willy Waller"
    #And the column "part_description" from the "warranty_parts" legacy table has been updated with string "Challenger II the rising"

  Scenario: Change SB lines status to TLD_TO_IMPLEMENT
    # CLOSED by comment
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/sb_spare_parts_requests/7" with body:
    """
    {
      "salesOrder": "PV0000011"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/spare_parts_request/schemas/spare_parts_request.json"
    And the JSON node "status" should be equal to the string "OPEN"
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/spare_parts_requests/7/status" with body:
    """
    {
       "status": "SHIPPED"
    }
    """
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/spare_parts_requests/7/status" with body:
    """
    {
       "status": "CLOSED"
    }
    """
    Then the response status code should be 200
    And the column "status" from the "sb_lines" legacy table has been updated with string "TLD_TO_IMPLEMENT"
    # CLOSED by proof of delivery
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/parts/spare_parts_requests/3/proof_of_delivery" with file "file" "file.pdf"
    Then the response status code should be 201
    And the column "status" from the "sb_lines" legacy table has been updated with string "TLD_TO_IMPLEMENT"
