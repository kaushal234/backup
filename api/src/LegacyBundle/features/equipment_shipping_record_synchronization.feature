Feature: Test ESR double write API

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create an ESR in API database triggers double-write on legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "sales/equipment_shipping_records" with body:
    """
    {
      "sso": "\/locations\/23",
      "modality": "AIR",
      "customer": "\/sales\/customers\/1",
      "incoterm": "\/sales\/incoterms\/3",
      "loadingPlace": "T es pas là !",
      "departurePlace": "Mais t es ou",
      "arrivalPlace": "Pas là !",
      "forwarder": "\/freight_forwarders\/1",
      "carrier": "\/freight_forwarders\/2",
      "notes": "Moi, dans la vie, je fais des tests. Et les tests des fois, ça doit consister en des longues chaines de caractères. Du coup, là, je fais une longue chaine de caractères. Si tu as lu ça jusqu au bout, j te dis coucou !",
      "shipAuthorization": true,
      "equipmentShippingRecordLines": [
        {
           "equipmentRecord": "/equipment_records/8",
           "estimatedPickUpDate": "2024-08-24T00:00:00-0400",
           "vesselLoadingDate": "2024-08-25T00:00:00-0400",
           "estimatedArrivalDate": "2024-08-25T00:00:00-0400",
           "actualArrivalDate": "2024-08-25T00:00:00-0400"
        }
      ]
    }
    """
    Then the response status code should be 201
    Then a new row has been inserted in the legacy table "esr"
    And the column "sso_id" from the "esr" legacy table has been inserted with integer 67
    Then the column "modality" from the "esr" legacy table has been inserted with string "AIR"
    Then the column cuid from the esr legacy table has been inserted with integer 4074
    Then the column inco from the esr legacy table has been inserted with string FAS
    Then the column arrival from the esr legacy table has been inserted with string "Pas là !"
    Then the column departure from the esr legacy table has been inserted with string "Mais t es ou"
    Then the column notes from the esr legacy table has been inserted with string "Moi, dans la vie, je fais des tests. Et les tests des fois, ça doit consister en des longues chaines de caractères. Du coup, là, je fais une longue chaine de caractères. Si tu as lu ça jusqu au bout, j te dis coucou !"
    Then the column load_place from the esr legacy table has been inserted with string "T es pas là !"
    Then a new row has been inserted in the legacy table "esrl"
    And the column "erid" from the "esrl" legacy table has been inserted with integer 37462
    And the column "dt_arrived" from the "esrl" legacy table has been inserted
    And the column "esrId" from the "service" legacy table has been updated

  Scenario: Update an ESR in API database triggers double-write on legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/equipment_shipping_records/5" with body:
    """
    {
      "sso": "/locations/28",
      "modality": "ROAD",
      "incoterm": "/sales/incoterms/2",
      "loadingPlace": "Not here t fou ou koa",
      "departurePlace": "mais quoi !",
      "arrivalPlace": "Koucoubé Sénégal",
      "forwarder": "/freight_forwarders/2",
      "carrier": "/freight_forwarders/1",
      "notes": "Cette fois faut pas perdre le coli",
      "shipAuthorization": false,
      "equipmentShippingRecordLines": [
        {
           "@id": "/sales/equipment_shipping_record_lines/4",
           "@type": "EquipmentShippingRecordLine",
           "equipmentRecord": "/equipment_records/7",
           "estimatedPickUpDate": "2024-08-24T00:00:00-0400",
           "vesselLoadingDate": "2024-08-25T00:00:00-0400",
           "estimatedArrivalDate": "2024-08-25T00:00:00-0400",
           "actualArrivalDate": "2024-08-01T00:00:00-0400"
        }
      ]
    }
    """
    Then the response status code should be 200
    And the column "sso_id" from the "esr" legacy table has been updated with integer 72
    Then the column "modality" from the "esr" legacy table has been updated with string "ROAD"
    Then the column inco from the esr legacy table has been updated with string FCA
    Then the column load_place from the esr legacy table has been updated with string "Not here t fou ou koa"
    Then the column departure from the esr legacy table has been updated with string "mais quoi !"
    Then the column arrival from the esr legacy table has been updated with string "Koucoubé Sénégal"
    Then the column notes from the esr legacy table has been updated with string "Cette fois faut pas perdre le coli"
    And the column "erid" from the "esrl" legacy table has been updated with integer 37461
    And the column "dt_arrived" from the "esrl" legacy table has been updated
    And the column "esrId" from the "service" legacy table has been updated

  Scenario: Delete an ESR in API database triggers double-write on legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "Delete" request to "/sales/equipment_shipping_records/3"
    Then the response status code should be 204
    Then a row has been deleted in the legacy table esr