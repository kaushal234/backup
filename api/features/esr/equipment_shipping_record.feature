Feature: Test Equipment Shipping Records API

  Scenario: Request all Equipment Shipping Records
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/equipment_shipping_records"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/equipment_shipping_record/schemas/equipment_shipping_records.json"

  Scenario: Request one Equipment Shipping Records
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/equipment_shipping_records/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/equipment_shipping_record/schemas/equipment_shipping_record.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord" is exposed on the API
    Then the filter "order[id]" should be available and its type should be "string"
    And the filter "customer" should be available and its type should be "string"
    And the filter "status" should be available and its type should be "string"
    And the filter "incoterm" should be available and its type should be "string"
    And the filter "loadingPlace" should be available and its type should be "string"
    And the filter "departurePlace" should be available and its type should be "string"
    And the filter "arrivalPlace" should be available and its type should be "string"
    And the filter "forwarder" should be available and its type should be "string"
    And the filter "carrier" should be available and its type should be "string"
    And the filter "modality" should be available and its type should be "string"
    And the filter "shipAuthorization" should be available and its type should be "bool"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "equipmentShippingRecordLines.equipmentRecord.serialNumber" should be available and its type should be "string"
    And the filter "normalization_groups_override[]" should be available and its type should be "string"
    And the filter "equipmentShippingRecordLines.equipmentRecord.manufacturerLocation" should be available and its type should be "string"

  Scenario: I cannot create an ESR as user-basic
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/equipment_shipping_records" with body:
    """
    {
      "sso": "/locations/23",
      "modality": "AIR",
      "customer": "/sales/customers/1",
      "incoterm": "/sales/incoterms/3",
      "loadingPlace": "T'es pas là !",
      "departurePlace": "Mais t'es où ?",
      "arrivalPlace": "Pas là !",
      "forwarder": "/freight_forwarders/1",
      "carrier": "/freight_forwarders/2",
      "notes": "Avec une moyenne de 11/20 ce qui est bien, mais pas top.",
      "shipAuthorization": true,
      "equipmentShippingRecordLines": [
        {
           "equipmentRecord": "/equipment_records/8",
           "estimatedPickUpDate": "2024-08-24T00:00:00-0400",
           "vesselLoadingDate": "2024-08-25T00:00:00-0400",
           "estimatedArrivalDate": "2024-08-25T00:00:00-0400",
           "actualArrivalDate": "2024-08-25T00:00:00-0400",
           "truckType": "20 tonnes"
        }
      ],
     }
    """
    Then the response status code should be 403

  Scenario: I Cannot create ESR without sso
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/equipment_shipping_records" with body:
    """
    {
      "modality": "AIR",
      "customer": "/sales/customers/44",
      "incoterm": "/sales/incoterms/3",
      "loadingPlace": "T'es pas là !",
      "departurePlace": "Mais t'es où ?",
      "arrivalPlace": "Pas là !",
      "forwarder": "/freight_forwarders/1",
      "carrier": "/freight_forwarders/2",
      "notes": "Avec une moyenne de 11/20 ce qui est bien, mais pas top.",
      "shipAuthorization": true
    }
    """
    Then the response status code should be 422

  Scenario: I Cannot create ESR without customer
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/equipment_shipping_records" with body:
    """
    {
      "sso": "/locations/23",
      "modality": "AIR",
      "incoterm": "/sales/incoterms/3",
      "loadingPlace": "T'es pas là !",
      "departurePlace": "Mais t'es où ?",
      "arrivalPlace": "Pas là !",
      "forwarder": "/freight_forwarders/1",
      "carrier": "/freight_forwarders/2",
      "notes": "Avec une moyenne de 11/20 ce qui est bien, mais pas top.",
      "shipAuthorization": true
    }
    """
    Then the response status code should be 422

  Scenario: I Cannot create ESR without incoterm
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/equipment_shipping_records" with body:
    """
    {
      "sso": "/locations/23",
      "modality": "AIR",
      "customer": "/sales/customers/44",
      "loadingPlace": "T'es pas là !",
      "departurePlace": "Mais t'es où ?",
      "arrivalPlace": "Pas là !",
      "forwarder": "/freight_forwarders/1",
      "carrier": "/freight_forwarders/2",
      "notes": "Avec une moyenne de 11/20 ce qui est bien, mais pas top.",
      "shipAuthorization": true
    }
    """
    Then the response status code should be 422

  Scenario: I can create an ESR as allowed user
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/equipment_shipping_records" with body:
    """
    {
      "sso": "/locations/20",
      "modality": "AIR",
      "customer": "/sales/customers/1",
      "incoterm": "/sales/incoterms/3",
      "loadingPlace": "T'es pas là !",
      "departurePlace": "Mais t'es où ?",
      "arrivalPlace": "Pas là !",
      "forwarder": "/freight_forwarders/1",
      "carrier": "/freight_forwarders/2",
      "notes": "Avec une moyenne de 11/20 ce qui est bien, mais pas top.",
      "shipAuthorization": true,
      "equipmentShippingRecordLines": [
        {
           "equipmentRecord": "/equipment_records/8",
           "estimatedPickUpDate": "2024-08-24T00:00:00-0400",
           "vesselLoadingDate": "2024-08-25T00:00:00-0400",
           "estimatedArrivalDate": "2024-08-25T00:00:00-0400",
           "actualArrivalDate": "2024-08-25T00:00:00-0400",
           "truckType": "20 tonnes"
        }
      ]
    }
    """
    Then the response status code should be 201
    And the JSON node "sso.@id" should be equal to the string "/locations/20"
    And the JSON node "modality" should be equal to the string "AIR"
    And the JSON node "customer.@id" should be equal to the string "/sales/customers/1"
    And the JSON node "incoterm.@id" should be equal to the string "/sales/incoterms/3"
    And the JSON node "loadingPlace" should be equal to the string "T'es pas là !"
    And the JSON node "departurePlace" should be equal to the string "Mais t'es où ?"
    And the JSON node "arrivalPlace" should be equal to the string "Pas là !"
    And the JSON node "forwarder.@id" should be equal to the string "/freight_forwarders/1"
    And the JSON node "carrier.@id" should be equal to the string "/freight_forwarders/2"
    And the JSON node "notes" should be equal to the string "Avec une moyenne de 11/20 ce qui est bien, mais pas top."
    And the JSON node "shipAuthorization" should be true
    And the JSON node "equipmentShippingRecordLines" should have 1 element
    And the JSON node "equipmentShippingRecordLines[0].equipmentRecord.id" should be equal to 8
    And the JSON node "equipmentShippingRecordLines[0].estimatedPickUpDate" should be equal to "2024-08-24T00:00:00-04:00"
    And the JSON node "equipmentShippingRecordLines[0].truckType" should be equal to "20 tonnes"
    And an email should have been sent asynchronously with subject "New ESR (5) has been created"


  Scenario: I can not create an ESR as allowed user on ER not include in ESR Customer ER
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/equipment_shipping_records" with body:
    """
    {
      "sso": "/locations/23",
      "modality": "AIR",
      "customer": "/sales/customers/1",
      "incoterm": "/sales/incoterms/3",
      "loadingPlace": "T'es pas là !",
      "departurePlace": "Mais t'es où ?",
      "arrivalPlace": "Pas là !",
      "forwarder": "/freight_forwarders/1",
      "carrier": "/freight_forwarders/2",
      "notes": "Avec une moyenne de 11/20 ce qui est bien, mais pas top.",
      "shipAuthorization": true,
      "equipmentShippingRecordLines": [
        {
           "equipmentRecord": "/equipment_records/11",
           "estimatedPickUpDate": "2024-08-24T00:00:00-0400",
           "vesselLoadingDate": "2024-08-25T00:00:00-0400",
           "estimatedArrivalDate": "2024-08-25T00:00:00-0400",
           "actualArrivalDate": "2024-08-25T00:00:00-0400",
           "truckType": "Fail truck"
        }
      ]
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to the string "equipmentShippingRecordLines[0].equipmentRecord: <br>Serial number REMI cannot be assigned to customer AIR DE RIEN because this customer is neither the Buyer nor the End User of this Equipment Record.<br>"

  Scenario: Updating pickup confirmation is allowed for PSE users
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/equipment_shipping_records/5" with body:
    """
    {
        "equipmentShippingRecordLines": [
            {
                "@id": "/sales/equipment_shipping_record_lines/3",
                "estimatedPickUpDateConfirmation": true
            }
        ]
    }
    """
    Then the response status code should be 200
    Then the JSON node "equipmentShippingRecordLines[0].estimatedPickUpDateConfirmation" should be true

  Scenario: If user from PSM team update pickup date, confirmation will not turn to false
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/equipment_shipping_records/5" with body:
    """
    {
        "equipmentShippingRecordLines": [
            {
                "@id": "/sales/equipment_shipping_record_lines/3",
                "estimatedPickUpDate": "2024-08-25T00:00:00-0400"
            }
        ]
    }
    """
    Then the response status code should be 200
    And an email should have been sent asynchronously with subject matching pattern "/^Pick-Up information on an ESR line has been updated on ESR# \d+$/"
    Then the JSON node "equipmentShippingRecordLines[0].estimatedPickUpDateConfirmation" should be true


  Scenario: If another user from PSM team update pickup date, confirmation will turn to false
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/equipment_shipping_records/5" with body:
    """
    {
        "equipmentShippingRecordLines": [
            {
                "@id": "/sales/equipment_shipping_record_lines/3",
                "estimatedPickUpDate": "2024-08-30T00:00:00-0400"
            }
        ]
    }
    """
    Then the response status code should be 200
    And an email should have been sent asynchronously with subject matching pattern "/^Pick-Up information on an ESR line has been updated on ESR# \d+$/"
    Then the JSON node "equipmentShippingRecordLines[0].estimatedPickUpDateConfirmation" should be false

  Scenario: I can update an ESR when pickup confirmation is not modified
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/equipment_shipping_records/5" with body:
    """
    {
      "sso": "/locations/28",
      "modality": "ROAD",
      "customer": "/sales/customers/1",
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
           "@id": "/sales/equipment_shipping_record_lines/3",
           "equipmentRecord": "/equipment_records/10",
           "estimatedPickUpDate": null,
           "vesselLoadingDate": "2024-08-25T00:00:00-0400",
           "estimatedArrivalDate": "2024-08-25T00:00:00-0400",
           "actualArrivalDate": null,
           "truckType": "50t big camion"
        },
        {
           "equipmentRecord": "/equipment_records/6",
           "estimatedPickUpDate": "2024-08-25T00:00:00-0400",
           "vesselLoadingDate": "2024-08-25T00:00:00-0400",
           "estimatedArrivalDate": "2024-08-25T00:00:00-0400",
           "actualArrivalDate": "2024-08-25T00:00:00-0400",
           "truckType": "Dis camion POUETTE POUETTE",
           "comment": "Arrivé à beaufcity 12h45"
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON node "sso.@id" should be equal to the string "/locations/28"
    And the JSON node "modality" should be equal to the string "ROAD"
    And the JSON node "customer.@id" should be equal to the string "/sales/customers/1"
    And the JSON node "incoterm.@id" should be equal to the string "/sales/incoterms/2"
    And the JSON node "loadingPlace" should be equal to the string "Not here t fou ou koa"
    And the JSON node "departurePlace" should be equal to the string "mais quoi !"
    And the JSON node "arrivalPlace" should be equal to the string "Koucoubé Sénégal"
    And the JSON node "forwarder.@id" should be equal to the string "/freight_forwarders/2"
    And the JSON node "carrier.@id" should be equal to the string "/freight_forwarders/1"
    And the JSON node "notes" should be equal to the string "Cette fois faut pas perdre le coli"
    And the JSON node "shipAuthorization" should be false
    And the JSON node "equipmentShippingRecordLines" should have 2 elements
    And the JSON node "equipmentShippingRecordLines[0].equipmentRecord.id" should be equal to 10
    And the JSON node "equipmentShippingRecordLines[0].estimatedPickUpDate" should be null
    And the JSON node "equipmentShippingRecordLines[0].actualArrivalDate" should be null
    And the JSON node "equipmentShippingRecordLines[0].truckType" should be equal to the string "50t big camion"
    And the JSON node "equipmentShippingRecordLines[1].equipmentRecord.id" should be equal to 6
    And the JSON node "equipmentShippingRecordLines[1].actualArrivalDate" should be equal to "2024-08-25T00:00:00-04:00"
    And the JSON node "equipmentShippingRecordLines[1].truckType" should be equal to the string "Dis camion POUETTE POUETTE"
    And the JSON node "equipmentShippingRecordLines[1].comment" should be equal to the string "Arrivé à beaufcity 12h45"
    And an email should have been sent asynchronously with subject "Pick-Up information on an ESR line has been updated on ESR# 5"
    Then the JSON node "equipmentShippingRecordLines[1].estimatedPickUpDateConfirmation" should be false

  Scenario: Updating an ESR is denied when pickup confirmation is changed without proper role
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/equipment_shipping_records/5" with body:
    """
    {
        "equipmentShippingRecordLines": [
            {
                "@id": "/sales/equipment_shipping_record_lines/4",
                "estimatedPickUpDateConfirmation": true
            }
        ]
    }
    """
    Then the response status code should be 403


  Scenario: A MOO_ESR user can always update pickup confirmation
    Given I authenticate as the intranet user "user-moo-esr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/equipment_shipping_records/5" with body:
    """
    {
        "equipmentShippingRecordLines": [
            {
                "@id": "\/sales\/equipment_shipping_record_lines\/3"
            },
            {
                "@id": "/sales/equipment_shipping_record_lines/4",
                "estimatedPickUpDateConfirmation": false
            }
        ]
    }
    """
    Then the response status code should be 200
    Then the JSON node "equipmentShippingRecordLines[1].estimatedPickUpDateConfirmation" should be false


  Scenario: Test the workflow of the equipment shipping record
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/sales/equipment_shipping_records/5/status" with body:
    """
    {
        "status": "BOOKED"
    }
    """
    Then the response status code should be 200
    Then the JSON node "status" should be equal to "BOOKED"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/sales/equipment_shipping_records/5/status" with body:
    """
    {
        "status": "SHIPPED"
    }
    """
    Then the response status code should be 200
    Then the JSON node "status" should be equal to "SHIPPED"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/sales/equipment_shipping_records/5/status" with body:
    """
    {
        "status": "CLOSED"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should contain "INTERNAL ERROR: Status not updated to CLOSED. Reason: ESRL# 3 Actual Arrival Date not set"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/equipment_shipping_records/5" with body:
    """
    {
      "sso": "/locations/28",
      "modality": "ROAD",
      "customer": "/sales/customers/1",
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
           "@id": "/sales/equipment_shipping_record_lines/3",
           "equipmentRecord": "/equipment_records/10",
           "estimatedPickUpDate": null,
           "vesselLoadingDate": "2024-08-25T00:00:00-0400",
           "estimatedArrivalDate": "2024-08-25T00:00:00-0400",
           "actualArrivalDate": "2024-08-25T00:00:00-0400",
           "estimatedPickUpDate": "2024-08-24T00:00:00-0400",
           "truckType": "10 tonnes",
           "comment": "Arrivage dans Paris à 8h"
        },
        {
           "@id": "/sales/equipment_shipping_record_lines/4",
           "equipmentRecord": "/equipment_records/6",
           "estimatedPickUpDate": "2024-08-24T00:00:00-0400",
           "vesselLoadingDate": "2024-08-25T00:00:00-0400",
           "estimatedArrivalDate": "2024-08-24T00:00:00-0400",
           "actualArrivalDate": "2024-08-25T00:00:00-0400",
           "truckType": "5 tonnes"
        }
      ]
    }
    """
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/sales/equipment_shipping_records/5/status" with body:
    """
    {
        "status": "CLOSED"
    }
    """
    Then the response status code should be 200

  Scenario: Test i can change status closed to shipped as a moo
    Given I authenticate as the intranet user "user-moo-esr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/sales/equipment_shipping_records/5/status" with body:
    """
    {
        "status": "SHIPPED"
    }
    """
    Then the response status code should be 200
    Then the JSON node "status" should be equal to "SHIPPED"

  Scenario: I can delete an ESR as allowed user
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/equipment_shipping_records/3"
    Then the response status code should be 204


  @resetFileTable
  Scenario: As a basic user, I can upload a file to an equipment shipping record
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/equipment_shipping_records/1/files" with file "file" "file.doc"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: As not granted authorized app, I can't upload a file to a equipment shipping record
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/equipment_shipping_records/1/files" with file "file" "file.doc"
    Then the response status code should be 403

  Scenario: Upload an invalid file to a equipment shipping record
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/equipment_shipping_records/1/files" with file "file" "image.gif"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "files: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "files"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: Download a equipment shipping record attached file as an not granted authorized app should not be possible
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/equipment_shipping_records/1/files/1"
    Then the response status code should be 403

  Scenario: Download a equipment shipping record attached file as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/equipment_shipping_records/1/files/1"
    Then the response status code should be 200

  Scenario: As a basic user, I can delete a file from a equipment shipping record
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/equipment_shipping_records/1/files/1"
    Then the response status code should be 204

  Scenario: Get a report for ESR By SSO By Status
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/equipment_shipping_records;x=sso.name;y=status"
    Then the response status code should be 200

  Scenario: Get a report for ESR By ManufacturerLocation By Status
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/equipment_shipping_records;x=equipmentShippingRecordLines.equipmentRecord.manufacturerLocation.name;y=status?options[status][]=PENDING&options[status][]=BOOKED"
    Then the response status code should be 200
