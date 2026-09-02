Feature: Test Spare Parts Request API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Parts\SparePartsRequest" should only be available for intranet user

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Parts\SparePartsRequest" is exposed on the API
    Then the filter "order[id]" should be available and its type should be "string"
    Then the filter "order[createdAt]" should be available and its type should be "string"
    Then the filter "order[status]" should be available and its type should be "string"
    Then the filter "order[sph.name]" should be available and its type should be "string"
    Then the filter "order[factory.name]" should be available and its type should be "string"
    Then the filter "order[airport.code]" should be available and its type should be "string"
    Then the filter "order[salesOrder]" should be available and its type should be "string"
    Then the filter "order[customer.name]" should be available and its type should be "string"
    Then the filter "order[activity]" should be available and its type should be "string"
    Then the filter "order[type]" should be available and its type should be "string"
    Then the filter "legacyId" should be available and its type should be "int"
    Then the filter "poster" should be available and its type should be "string"
    Then the filter "sph" should be available and its type should be "string"
    Then the filter "factory" should be available and its type should be "string"
    Then the filter "airport" should be available and its type should be "string"
    Then the filter "status" should be available and its type should be "string"
    Then the filter "type" should be available and its type should be "string"
    Then the filter "salesOrder" should be available and its type should be "string"
    Then the filter "parts.partNumber" should be available and its type should be "string"
    Then the filter "exists[salesOrder]" should be available and its type should be "bool"
    And the filter "shippingDate[before]" should be available and its type should be "DateTimeInterface"
    And the filter "shippingDate[after]" should be available and its type should be "DateTimeInterface"
    And the filter "createdAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "createdAt[after]" should be available and its type should be "DateTimeInterface"
    Then the filter "sso.legacyId" should be available and its type should be "int"
    And the filter "columns" should be available and its type should be "string"


  Scenario: Download excel task reports should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/parts/spare_parts_requests?columns=id,createdAt,status,sph.name,factory.name,airport.code,salesOrder,customer.name,activity,type,linkedModuleId&pagination=0"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Created At | Status | SPH | Factory | Airport | Sales Order | Customer | Activity | Type | Parent |

  Scenario: Request all Spare Parts Requests
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/spare_parts_requests"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/spare_parts_request/schemas/spare_parts_requests.json"
    And the JSON node "hydra:member[0].parts" should not exist
    And the JSON node "hydra:member[0].deletedParts" should not exist
    And no requests have been sent to ION

  Scenario: Request a given TOC Spare Parts Request
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/spare_parts_requests/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/spare_parts_request/schemas/spare_parts_request.json"
    And the JSON node "@id" should be equal to the string "/parts/toc_spare_parts_requests/1"
    And the JSON node "tocId" should be equal to the number 37039
    And a "txShipments" SOAP client has been created
    And this client has been called on the operation "txList" with the following request:
    """
    {
      "ControlArea": {
        "maxNumberOfObjects": 500,
        "Filter": {
          "LogicalExpression": {
            "logicalOperator": "and"
          }
        }
      },
      "DataArea": {
        "txShipments": {
          "order": "9696969",
          "orderOrigins": "1|2|3"
        }
      }
    }
    """
    And a "SalesOrder" SOAP client has been created
    And this client has been called on the operation "Show" with the following request:
    """
    {
      "DataArea": {
        "SalesOrder": {
          "salesOrder": "9696969"
        }
      }
    }
    """
    And a total of 2 requests has been sent to ION

  Scenario: Request a given SB Spare Parts Request
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/spare_parts_requests/3"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/spare_parts_request/schemas/spare_parts_request.json"
    And the JSON node "@id" should be equal to the string "/parts/sb_spare_parts_requests/3"
    And the JSON node "sbId" should be equal to the number 58
    And a "txShipments" SOAP client has been created
    And this client has been called on the operation "txList" with the following request:
    """
    {
      "ControlArea": {
        "maxNumberOfObjects": 500,
        "Filter": {
          "LogicalExpression": {
            "logicalOperator": "and"
          }
        }
      },
      "DataArea": {
        "txShipments": {
          "order": "PV0000011",
          "orderOrigins": "1|2|3"
        }
      }
    }
    """
    And a "SalesOrder" SOAP client has been created
    And this client has been called on the operation "Show" with the following request:
    """
    {
      "DataArea": {
        "SalesOrder": {
          "salesOrder": "PV0000011"
        }
      }
    }
    """
    And a total of 2 requests has been sent to ION

  Scenario: Create a TOC Spare Parts Request with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/parts/toc_spare_parts_requests" with body:
    """
    {
    }
    """
    Then the response status code should be 403

  Scenario: Create a SB Spare Parts Request with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/parts/sb_spare_parts_requests" with body:
    """
    {
    }
    """
    Then the response status code should be 403

  Scenario: As a service user, I can filter Spare Parts Requests by TOC ID and display the related parts
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/toc_spare_parts_requests?technicianOnCall=/service/technician_on_calls/15&normalization_groups[]=part"
    And the JSON should be valid according to the schema "tests/fixtures/json/spare_parts_request/schemas/spare_parts_requests.json"
    And the JSON node "hydra:member[0].parts" should exist
    And a "txShipments" SOAP client has been created
    And this client has been called on the operation "txList" with the following request:
    """
    {
      "ControlArea": {
        "maxNumberOfObjects": 500,
        "Filter": {
          "LogicalExpression": {
            "logicalOperator": "and"
          }
        }
      },
      "DataArea": {
        "txShipments": {
          "order": "9696969",
          "orderOrigins": "1|2|3"
        }
      }
    }
    """
    And a "SalesOrder" SOAP client has been created
    And this client has been called on the operation "Show" with the following request:
    """
    {
      "DataArea": {
        "SalesOrder": {
          "salesOrder": "9696969"
        }
      }
    }
    """
    And a total of 2 requests has been sent to ION

  Scenario: As a service user, I can filter Spare Parts Requests by SB ID and display the related parts
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/sb_spare_parts_requests?sbId=58&normalization_groups[]=part"
    And the JSON should be valid according to the schema "tests/fixtures/json/spare_parts_request/schemas/spare_parts_requests.json"
    And the JSON node "hydra:member[0].parts" should exist
    And the JSON node "hydra:member" should have 3 elements
    And a "txShipments" SOAP client has been created
    And this client has been called on the operation "txList" with the following request:
    """
    {
      "ControlArea": {
        "maxNumberOfObjects": 500,
        "Filter": {
          "LogicalExpression": {
            "logicalOperator": "and"
          }
        }
      },
      "DataArea": {
        "txShipments": {
          "order": "PV0000011",
          "orderOrigins": "1|2|3"
        }
      }
    }
    """
    And a "txShipments" SOAP client has been created
    And this client has been called on the operation "txList" with the following request:
    """
    {
      "ControlArea": {
        "maxNumberOfObjects": 500,
        "Filter": {
          "LogicalExpression": {
            "logicalOperator": "and"
          }
        }
      },
      "DataArea": {
        "txShipments": {
          "order": "9696969",
          "orderOrigins": "1|2|3"
        }
      }
    }
    """
    And a "txShipments" SOAP client has been created
    And this client has been called on the operation "txList" with the following request:
    """
    {
      "ControlArea": {
        "maxNumberOfObjects": 500,
        "Filter": {
          "LogicalExpression": {
            "logicalOperator": "and"
          }
        }
      },
      "DataArea": {
        "txShipments": {
          "order": "9696969",
          "orderOrigins": "1|2|3"
        }
      }
    }
    """
    And a "SalesOrder" SOAP client has been created
    And this client has been called on the operation "Show" with the following request:
    """
    {
      "DataArea": {
        "SalesOrder": {
          "salesOrder": "PV0000011"
        }
      }
    }
    """
    And a "SalesOrder" SOAP client has been created
    And this client has been called on the operation "Show" with the following request:
    """
    {
      "DataArea": {
        "SalesOrder": {
          "salesOrder": "9696969"
        }
      }
    }
    """
    And a total of 4 requests has been sent to ION

  Scenario: As a service user, I can create a Spare Parts Request from a TOC
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/parts/toc_spare_parts_requests" with body:
    """
    {
      "technicianOnCall": "/service/technician_on_calls/16",
      "activity": "Troubleshooting",
      "type": "toc.type.customer",
      "sph": "/locations/27",
      "sso": "/locations/28",
      "factory": "/locations/29",
      "erpLocation": "/locations/29",
      "customer": "/sales/customers/1",
      "airport": "/airports/62",
      "equipmentRecords": ["/equipment_records/1"],
      "deliveryNotes": "just for test",
      "parts": [
        {
          "partNumber": "WW2006",
          "description": "Willy Waller",
          "unitOfMeasure": "BTC",
          "quantity": 42,
          "comment": "avec ton Willi Waller Two Thousand Six là, jamais manger des bonnes patates aura été aussi facile"
        }
      ],
      "deliveryAddress": {
        "contact": "/sales/extranet_users/200",
        "firstname": "Bob",
        "lastname": "by",
        "phone": "+33 6 10 20 30 40",
        "address": {
          "street1": "Rue Kétanou",
          "street2": "Impasse des babos",
          "postalCode": "M4X0A1",
          "town": "Cape",
          "city": "Paris",
          "country": "FR"
        },
        "airport": "/airports/61",
        "company": "TEST"
      }
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to the string "/parts/toc_spare_parts_requests/6"
    And the JSON node "activity" should be equal to the string "Troubleshooting"
    And the JSON node "type" should be equal to the string "Payable Services"
    And the JSON node "status" should be equal to the string "PENDING"
    And the JSON node "sph.@id" should be equal to the string "/locations/27"
    And the JSON node "sso.@id" should be equal to the string "/locations/28"
    And the JSON node "factory.@id" should be equal to the string "/locations/29"
    And the JSON node "customer.@id" should be equal to the string "/sales/customers/1"
    And the JSON node "airport.@id" should be equal to the string "/airports/62"
    And the JSON node "equipmentRecords[0].@id" should be equal to the string "/equipment_records/1"
    And the JSON node "parts[0].partNumber" should be equal to the string "WW2006"
    And the JSON node "parts[0].unitOfMeasure" should be equal to the string "BTC"
    And the JSON node "parts[0].quantity" should be equal to the number 42
    And the JSON node "parts[0].comment" should be equal to the string "avec ton Willi Waller Two Thousand Six là, jamais manger des bonnes patates aura été aussi facile"
    And the JSON node "deliveryAddress.@id" should be equal to the string "/parts/spare_parts_request_delivery_addresses/3"
    And the JSON node "deliveryAddress.contact.@id" should be equal to the string "/sales/extranet_users/200"
    And the JSON node "deliveryAddress.address.street1" should be equal to the string "Rue Kétanou"
    And the JSON node "deliveryAddress.address.street2" should be equal to the string "Impasse des babos"
    And the JSON node "deliveryAddress.address.postalCode" should be equal to the string "M4X0A1"
    And the JSON node "deliveryAddress.address.town" should be equal to the string "Cape"
    And the JSON node "deliveryAddress.address.city" should be equal to the string "Paris"
    And the JSON node "deliveryAddress.address.country" should be equal to the string "FR"
    And the JSON node "deliveryAddress.airport.@id" should be equal to the string "/airports/61"
    And the JSON node "deliveryNotes" should be equal to the string "just for test"
    And the JSON should be valid according to the schema "tests/fixtures/json/spare_parts_request/schemas/spare_parts_request.json"
    And an email should have been sent asynchronously with subject matching pattern "/New SPR#\S+ opened/"
    And this asynchronous email should be sent only to "partCustomerSupport@pcs.fr"
    And no requests have been sent to ION


  Scenario: As a service user, I can add parts on an existing Spare Parts Request
    Given I authenticate as the intranet user "user-service@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/toc_spare_parts_requests/6" with body:
    """
    {
      "parts": [
        "/parts/spare_parts_request_parts/9",
        {
          "partNumber": "WW2021",
          "description": "Willy Waller NEW 2021 edition",
          "unitOfMeasure": "WW2",
          "quantity": 43,
          "comment": "avec ton Willi Waller Two Thousand Twenty One là, jamais manger des bonnes patates aura été aussi facile"
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON node "parts" should have 2 elements
    And the JSON node "deletedParts" should have 0 element
    And the JSON node "parts[0].@id" should be equal to the string "/parts/spare_parts_request_parts/9"
    And the JSON node "parts[1].@id" should be equal to the string "/parts/spare_parts_request_parts/10"
    And the JSON node "parts[1].partNumber" should be equal to the string "WW2021"
    And the JSON node "parts[1].unitOfMeasure" should be equal to the string "WW2"
    And the JSON node "parts[1].quantity" should be equal to the number 43
    And the JSON node "parts[1].comment" should be equal to the string "avec ton Willi Waller Two Thousand Twenty One là, jamais manger des bonnes patates aura été aussi facile"
    And the JSON should be valid according to the schema "tests/fixtures/json/spare_parts_request/schemas/spare_parts_request.json"
    And an email should have been sent asynchronously with subject matching pattern "/SPR#\S+ - parts updated/"
    And this asynchronous email should be sent only to "partCustomerSupport@pcs.fr"
    And no requests have been sent to ION

  Scenario: As a service user, I can create a Spare Parts Request from a SB
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/parts/sb_spare_parts_requests" with body:
    """
    {
      "sbId": 410,
      "activity": "Troubleshooting",
      "type": "Payable Services",
      "sph": "/locations/27",
      "sso": "/locations/28",
      "factory": "/locations/29",
      "erpLocation": "/locations/29",
      "customer": "/sales/customers/1",
      "airport": "/airports/62",
      "equipmentRecords": ["/equipment_records/1"],
      "parts": [
        {
          "partNumber": "WW2006",
          "description": "Willy Waller",
          "unitOfMeasure": "BTC",
          "quantity": 42,
          "comment": "avec ton Willi Waller Two Thousand Six là, jamais manger des bonnes patates aura été aussi facile"
        }
      ],
      "deliveryAddress": {
        "contact": "/sales/extranet_users/200",
        "firstname": "Bob",
        "lastname": "by",
        "phone": "+33 6 10 20 30 40",
        "address": {
          "street1": "Rue Kétanou",
          "street2": "Impasse des babos",
          "postalCode": "M4X0A1",
          "town": "Cape",
          "city": "Paris",
          "country": "FR"
        },
        "airport": "/airports/61",
        "company": "TEST"
      }
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to the string "/parts/sb_spare_parts_requests/7"
    And the JSON node "activity" should be equal to the string "Troubleshooting"
    And the JSON node "type" should be equal to the string "Payable Services"
    And the JSON node "status" should be equal to the string "PENDING"
    And the JSON node "sph.@id" should be equal to the string "/locations/27"
    And the JSON node "sso.@id" should be equal to the string "/locations/28"
    And the JSON node "factory.@id" should be equal to the string "/locations/29"
    And the JSON node "customer.@id" should be equal to the string "/sales/customers/1"
    And the JSON node "airport.@id" should be equal to the string "/airports/62"
    And the JSON node "equipmentRecords[0].@id" should be equal to the string "/equipment_records/1"
    And the JSON node "parts[0].partNumber" should be equal to the string "WW2006"
    And the JSON node "parts[0].unitOfMeasure" should be equal to the string "BTC"
    And the JSON node "parts[0].quantity" should be equal to the number 42
    And the JSON node "parts[0].comment" should be equal to the string "avec ton Willi Waller Two Thousand Six là, jamais manger des bonnes patates aura été aussi facile"
    And the JSON node "deliveryAddress.@id" should be equal to the string "/parts/spare_parts_request_delivery_addresses/4"
    And the JSON node "deliveryAddress.contact.@id" should be equal to the string "/sales/extranet_users/200"
    And the JSON node "deliveryAddress.address.street1" should be equal to the string "Rue Kétanou"
    And the JSON node "deliveryAddress.address.street2" should be equal to the string "Impasse des babos"
    And the JSON node "deliveryAddress.address.postalCode" should be equal to the string "M4X0A1"
    And the JSON node "deliveryAddress.address.town" should be equal to the string "Cape"
    And the JSON node "deliveryAddress.address.city" should be equal to the string "Paris"
    And the JSON node "deliveryAddress.address.country" should be equal to the string "FR"
    And the JSON node "deliveryAddress.airport.@id" should be equal to the string "/airports/61"
    And the JSON should be valid according to the schema "tests/fixtures/json/spare_parts_request/schemas/spare_parts_request.json"
    And no requests have been sent to ION

  Scenario: As a service user, I can create another SPR on the same SB
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/parts/sb_spare_parts_requests" with body:
    """
    {
      "sbId": 410,
      "activity": "Troubleshooting",
      "type": "Payable Services",
      "sph": "/locations/27",
      "sso": "/locations/28",
      "factory": "/locations/29",
      "erpLocation": "/locations/29",
      "customer": "/sales/customers/1",
      "airport": "/airports/62",
      "equipmentRecords": ["/equipment_records/1"],
      "parts": [
        {
          "partNumber": "WW2006",
          "description": "Willy Waller",
          "unitOfMeasure": "BTC",
          "quantity": 8,
          "comment": "avec ton Willi Waller Two Thousand Six là, jamais manger des bonnes patates aura été aussi facile"
        },
        {
          "partNumber": "WW2022",
          "description": "Willy Waller 2022",
          "unitOfMeasure": "BTC",
          "quantity": 12,
          "comment": "avec ton Willi Waller Two Thousand Twenty Two là, jamais manger des bonnes patates aura été aussi facile"
        }
      ],
      "deliveryAddress": {
        "contact": "/sales/extranet_users/200",
        "firstname": "Bob",
        "lastname": "by",
        "company": "Creole",
        "phone": "+33 6 10 20 30 40",
        "address": {
          "street1": "Rue Kétanou",
          "street2": "Impasse des babos",
          "postalCode": "M4X0A1",
          "town": "Cape",
          "city": "Paris",
          "country": "FR"
        },
        "airport": "/airports/61"
      }
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to the string "/parts/sb_spare_parts_requests/8"

  Scenario: As a service user, I can only combine opened SPR from the same origin
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/spare_parts_requests/7/combine" with body:
    """
    {
      "sparePartsRequests": [
        "/parts/sb_spare_parts_requests/3",
        "/parts/sb_spare_parts_requests/4"
      ]
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "sparePartsRequests"
    And the JSON node "violations[0].message" should contain "All combined SPR must originate from the same SB"
    And the JSON node "violations[1].propertyPath" should be equal to "sparePartsRequests"
    And the JSON node "violations[1].message" should contain "All combined SPR must be either PENDING or OPEN"
    And the JSON node "violations[2].propertyPath" should be equal to "sparePartsRequests"
    And the JSON node "violations[2].message" should contain "All combined SPR must originate from the same SB"
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/spare_parts_requests/7/combine" with body:
    """
    {
      "sparePartsRequests": [
        "/parts/sb_spare_parts_requests/8"
      ]
    }
    """
    Then the response status code should be 200
    And the JSON node "parts" should have 2 elements
    And the JSON node "parts[0].partNumber" should be equal to "WW2006"
    And the JSON node "parts[0].quantity" should be equal to "50"
    And the JSON node "parts[1].partNumber" should be equal to "WW2022"
    And the JSON node "parts[1].quantity" should be equal to "12"
    And a comment should have been inserted on resource "/parts/sb_spare_parts_requests/7" with message "SPR#8 parts were imported and SPR#8 has been closed" by "user-parts@tld.fr"
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/sb_spare_parts_requests/8"
    And the JSON node "notes" should contain "SPR#8 parts were combined with SPR#7"
    And the JSON node "parts" should have 0 element

  Scenario: As an SPH user, I can't manually send a PENDING SPR to SHIPPED
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/spare_parts_requests/1/status" with body:
    """
    {
       "status": "SHIPPED"
    }
    """
    Then the response status code should be 422

  Scenario: As an SPH user, I must provide a valid Sales Order in LN
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/toc_spare_parts_requests/1" with body:
    """
    {
      "salesOrder": "DONOTEXIST"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "salesOrder"
    And the JSON node "violations[0].message" should contain "The sales order DONOTEXIST does not exist."

  Scenario: As an SPH user, I can manually send an OPEN SPR to SHIPPED
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/toc_spare_parts_requests/1" with body:
    """
    {
      "salesOrder": "C30244185",
      "deliveryAddress": "/parts/spare_parts_request_delivery_addresses/1"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/spare_parts_request/schemas/spare_parts_request.json"
    And the JSON node "status" should be equal to the string "OPEN"
    And the JSON node "deliveryAddress.@id" should be equal to the string "/parts/spare_parts_request_delivery_addresses/1"
    And a "txShipments" SOAP client has been created
    And this client has been called on the operation "txList" with the following request:
    """
    {
      "ControlArea": {
        "maxNumberOfObjects": 500,
        "Filter": {
          "LogicalExpression": {
            "logicalOperator": "and"
          }
        }
      },
      "DataArea": {
        "txShipments": {
          "order": "C30244185",
          "orderOrigins": "1|2|3"
        }
      }
    }
    """
    And a "SalesOrder" SOAP client has been created
    And this client has been called on the operation "Show" with the following request:
    """
    {
      "DataArea": {
        "SalesOrder": {
          "salesOrder": "C30244185"
        }
      }
    }
    """
    And a total of 2 requests has been sent to ION
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/spare_parts_requests/1/status" with body:
    """
    {
       "status": "SHIPPED"
    }
    """
    Then the response status code should be 200
    And an email should have been sent asynchronously with subject matching pattern "/SPR#\S+ - shipped/"
    And this asynchronous email should be sent only to "partCustomerSupport@pcs.fr"
    And an email should have been sent asynchronously with subject matching pattern "/SPR#\S+ has been shipped/"
    And this asynchronous email should be sent only to "julien.lepers@tld.com"

  Scenario: As a parts user, I can update the delivery address of an existing TOC Spare Parts Request
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/toc_spare_parts_requests/6" with body:
    """
    {
      "deliveryAddress": {
        "contact": "\/sales\/extranet_users\/204",
        "firstname": "Issac",
        "lastname": "DICK",
        "company": "Miller",
        "airport": "\/airports\/62",
        "phone": "+33 6 10 20 30 42",
        "address": {
            "street1": "81105 Orizzonte Street",
            "street2": "",
            "postalCode": "78747",
            "town": "",
            "city": "Austin",
            "state": "Texas",
            "country": "QA"
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "deliveryAddress.@id" should be equal to the string "/parts/spare_parts_request_delivery_addresses/6"
    And the JSON should be valid according to the schema "tests/fixtures/json/spare_parts_request/schemas/spare_parts_request.json"
    And an email should have been sent asynchronously with subject matching pattern "/SPR#\S+ - delivery address updated/"
    And this asynchronous email should be sent only to "partCustomerSupport@pcs.fr"

  Scenario: As a parts user, I can update the delivery address of an existing SB Spare Parts Request
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/sb_spare_parts_requests/7" with body:
    """
    {
      "deliveryAddress": "/parts/spare_parts_request_delivery_addresses/2"
    }
    """
    Then the response status code should be 200
    And the JSON node "deliveryAddress.@id" should be equal to the string "/parts/spare_parts_request_delivery_addresses/2"
    And the JSON should be valid according to the schema "tests/fixtures/json/spare_parts_request/schemas/spare_parts_request.json"
    And an email should have been sent asynchronously with subject matching pattern "/SPR#\S+ - delivery address updated/"
    And this asynchronous email should be sent only to "partCustomerSupport@pcs.fr"

  Scenario: As a SPH user, I can remove some parts on an existing Spare Parts Request and it would be listed in the deletedParts
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/toc_spare_parts_requests/6" with body:
    """
    {
      "parts": ["/parts/spare_parts_request_parts/9"]
    }
    """
    Then the response status code should be 200
    And the JSON node "parts" should have 1 element
    And the JSON node "deletedParts" should have 1 element
    And the JSON node "parts[0].@id" should be equal to the string "/parts/spare_parts_request_parts/9"
    And the JSON node "deletedParts[0].@id" should be equal to the string "/parts/spare_parts_request_parts/10"
    And the JSON should be valid according to the schema "tests/fixtures/json/spare_parts_request/schemas/spare_parts_request.json"

  Scenario: When the SO of an SPR is populated and a part is shipped, the tracking number and the shipping origin are populated
    Given I authenticate as the intranet user "user-service@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/sb_spare_parts_requests/3"
    Then the response status code should be 200
    And the JSON node "parts[0].trackings" should have 0 element
    And the JSON node "parts[0].shippingOrigin" should be null
    Given I authenticate as the intranet user "user-service@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/sb_spare_parts_requests/3" with body:
    """
    {
      "parts": [
        {
          "@id": "parts/spare_parts_request_parts/4",
          "partNumber": "2209307",
          "quantity": 5,
          "description": "a part"
        },
        {
          "@id": "parts/spare_parts_request_parts/4",
          "partNumber": "2209307",
          "quantity": 12,
          "description": "another part to prove that the report does not contain 2 rows"
        }
      ]
    }
    """
    Then the response status code should be 200
    And a "txShipments" SOAP client has been created
    And this client has been called on the operation "txList" with the following request:
    """
    {
      "ControlArea": {
        "maxNumberOfObjects": 500,
        "Filter": {
          "LogicalExpression": {
            "logicalOperator": "and"
          }
        }
      },
      "DataArea": {
        "txShipments": {
          "order": "PV0000011",
          "orderOrigins": "1|2|3"
        }
      }
    }
    """
    And a "SalesOrder" SOAP client has been created
    And this client has been called on the operation "Show" with the following request:
    """
    {
      "DataArea": {
        "SalesOrder": {
          "salesOrder": "PV0000011"
        }
      }
    }
    """
    And a total of 2 requests has been sent to ION
    And the JSON node "parts[0].trackings" should have 1 element
    And the JSON node "parts[0].trackings[0].trackingNumber" should be equal to "777361773542"
    And the JSON node "parts[0].shippingOrigin.@id" should be equal to the string "/locations/15"

  Scenario: SPR can be filtered by their parts' shipping origins
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/spare_parts_requests?parts.shippingOrigin=/locations/15"
    Then the JSON node "hydra:totalItems" should be equal to "1"
    And the JSON node "hydra:member[0].@id" should be equal to the string "/parts/sb_spare_parts_requests/3"

  Scenario: A report can be generated from the parts' shipping origins
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/parts/spare_parts_requests;x=parts.shippingOrigin.name;y=status"
    And the JSON node "total" should be equal to "1"

  Scenario: As a basic user, I can't delete a part
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/parts/spare_parts_request_parts/4"
    Then the response status code should be 403

  Scenario: As a SPH user, I can fully delete a part
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/parts/spare_parts_request_parts/4"
    Then the response status code should be 204

  Scenario: Update a given Spare Parts Request with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/toc_spare_parts_requests/6" with body:
    """
    {
    }
    """
    Then the response status code should be 403

  Scenario: When a shipping origin and a sales order are added on a SPR, the SPR status is set to OPEN
    Given I authenticate as the intranet user "user-spm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/toc_spare_parts_requests/6" with body:
    """
    {
      "salesOrder": "PV0000012"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/spare_parts_request/schemas/spare_parts_request.json"
    And the JSON node "status" should be equal to the string "OPEN"
    And a "txShipments" SOAP client has been created
    And this client has been called on the operation "txList" with the following request:
    """
    {
      "ControlArea": {
        "maxNumberOfObjects": 500,
        "Filter": {
          "LogicalExpression": {
            "logicalOperator": "and"
          }
        }
      },
      "DataArea": {
        "txShipments": {
          "order": "PV0000012",
          "orderOrigins": "1|2|3"
        }
      }
    }
    """
    And a "SalesOrder" SOAP client has been created
    And this client has been called on the operation "Show" with the following request:
    """
    {
      "DataArea": {
        "SalesOrder": {
          "salesOrder": "PV0000012"
        }
      }
    }
    """
    And a total of 2 requests has been sent to ION

  Scenario: As a service user, I can only edit the parts property of an SPR
    Given I authenticate as the intranet user "user-service@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "fields?iri=/parts/toc_spare_parts_requests/6&method=PUT"
    Then the response status code should be 200
    Then the JSON should be equal to:
    """
    [
      "activity",
      "type",
      "parts"
    ]
    """

  Scenario: As a parts user, I can edit most of the properties of an SPR
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "fields?iri=/parts/toc_spare_parts_requests/6&method=PUT"
    Then the response status code should be 200
    Then the JSON should be equal to:
    """
    [
      "activity",
      "type",
      "sph",
      "sso",
      "factory",
      "erpLocation",
      "estimatedShippingDate",
      "notes",
      "salesOrder",
      "customer",
      "airport",
      "deliveryNotes",
      "deliveryAddress",
      "parts"
    ]
    """

#  deliveryAddress.company is now mandatory but some legacy delivery address don't have
  Scenario: As a service user, I can't create a Spare Parts Request from a TOC if company is not specify
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/parts/toc_spare_parts_requests" with body:
    """
    {
      "technicianOnCall": "/service/technician_on_calls/16",
      "activity": "Troubleshooting",
      "type": "Payable Services",
      "sph": "/locations/27",
      "sso": "/locations/28",
      "factory": "/locations/29",
      "erpLocation": "/locations/29",
      "customer": "/sales/customers/1",
      "airport": "/airports/62",
      "equipmentRecords": ["/equipment_records/1"],
      "deliveryNotes": "just for test",
      "parts": [
        {
          "partNumber": "WW2006",
          "description": "Willy Waller",
          "unitOfMeasure": "BTC",
          "quantity": 42,
          "comment": "avec ton Willi Waller Two Thousand Six là, jamais manger des bonnes patates aura été aussi facile"
        }
      ],
      "deliveryAddress": {
        "contact": "/sales/extranet_users/200",
        "firstname": "Bob",
        "lastname": "by",
        "phone": "+33 6 10 20 30 40",
        "address": {
          "street1": "Rue Kétanou",
          "street2": "Impasse des babos",
          "postalCode": "M4X0A1",
          "town": "Cape",
          "city": "Paris",
          "country": "FR"
        },
        "airport": "/airports/61"
      }
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should contain "deliveryAddress.company: This value should not be null."

  Scenario: As a service user, I can't create a Spare Parts Request from a TOC if company is not specify
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/parts/toc_spare_parts_requests" with body:
    """
     {
      "technicianOnCall": "/service/technician_on_calls/3",
      "activity": "Troubleshooting",
      "sph": "/locations/27",
      "sso": "/locations/28",
      "factory": "/locations/29",
      "erpLocation": "/locations/29",
      "customer": "/sales/customers/1",
      "equipmentRecords": ["/equipment_records/1"],
      "parts": [
        {
          "partNumber": "WW2006",
          "description": "Willy Waller",
          "unitOfMeasure": "BTC",
          "quantity": 42,
          "comment": "avec ton Willi Waller Two Thousand Six là, jamais manger des bonnes patates aura été aussi facile"
        }
      ],
      "deliveryAddress": "\/parts\/spare_parts_request_delivery_addresses\/2"
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should contain "technicianOnCall: An SPR cannot be opened on a TOC of type Not Define Yet"

  Scenario: As a service user, I can create a Spare Parts Request from a TOC with existing delivery address with a null company
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/parts/toc_spare_parts_requests" with body:
    """
    {
      "technicianOnCall": "/service/technician_on_calls/16",
      "activity": "Troubleshooting",
      "type": "Payable Services",
      "sph": "/locations/27",
      "sso": "/locations/28",
      "factory": "/locations/29",
      "erpLocation": "/locations/29",
      "customer": "/sales/customers/1",
      "airport": "/airports/62",
      "equipmentRecords": ["/equipment_records/1"],
      "deliveryNotes": "test on legacy deliveryAddress.company is null",
      "parts": [
        {
          "partNumber": "WW2006",
          "description": "Willy Waller",
          "unitOfMeasure": "BTC",
          "quantity": 42,
          "comment": "avec ton Willi Waller Two Thousand Six là, jamais manger des bonnes patates aura été aussi facile"
        }
      ],
      "deliveryAddress": "\/parts\/spare_parts_request_delivery_addresses\/2"
    }
    """
    Then the response status code should be 201
    And the JSON node "deliveryAddress.company" should be null

  @resetFileTable
  Scenario: Basic users can't upload a proof of delivery
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/parts/spare_parts_requests/1/proof_of_delivery" with file "file" "file.pdf"
    Then the response status code should be 403

  Scenario: As a parts user, I can upload a proof of delivery on an SPR and it will be closed
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/parts/spare_parts_requests/1/proof_of_delivery" with file "file" "file.pdf"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/spare_parts_requests/1"
    And the response status code should be 200
    And the JSON node "status" should be equal to the string "CLOSED"

  Scenario: As a basic user, I can download an SPR proof of delivery
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/spare_parts_requests/1/proof_of_delivery/1"
    Then the response status code should be 200

  Scenario: Basic users can't delete a proof of delivery
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/parts/spare_parts_requests/1/proof_of_delivery/1"
    Then the response status code should be 403

  Scenario: As a parts user, I can delete a proof of delivery
    Given I authenticate as the intranet user "user-parts@tld.fr"
    When I send a "DELETE" request to "/parts/spare_parts_requests/1/proof_of_delivery/1"
    Then the response status code should be 204
    And I add "Accept" header equal to "application/ld+json"
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/spare_parts_requests/1"
    And the response status code should be 200
    And the JSON node "proofOfDelivery" should be null

  Scenario: generated files must be cleaned after tests
    Then I delete all the files created during test

  @resetFileTable
  Scenario: As a CSM user, I can upload a proof of delivery on an SPR and it will be closed
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/parts/spare_parts_requests/1/proof_of_delivery" with file "file" "file.pdf"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/spare_parts_requests/1"
    And the response status code should be 200
    And the JSON node "status" should be equal to the string "CLOSED"

  Scenario: Upload a contract attached file should be possible only for superuser, PARTS, SERVICE, PARTS_AGENTS, SERVICE_AGENTS
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/parts/spare_parts_requests/8/files" with file "file" "file.doc"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/parts/spare_parts_requests/8/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201

  Scenario: Upload an invalid file to a contract
    Given I authenticate as the intranet user "user-service@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/parts/spare_parts_requests/1/files" with file "file" "image.gif"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "files: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "sparePartsRequestFiles"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: Download a contract attached file should be possible for basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/spare_parts_requests/8/files/2"
    Then the response status code should be 200

  Scenario: Delete a contract attached file should be possible only for superuser, PARTS, SERVICE, PARTS_AGENTS, SERVICE_AGENTS
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/parts/spare_parts_requests/8/files/2"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-service@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/parts/spare_parts_requests/8/files/2"
    Then the response status code should be 204

  Scenario: As basic user I should not be able to construct a SPR when adding parts on a TOC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/parts/spare_parts_request_from_toc/1"
    Then the response status code should be 403

  Scenario: As authorized user, an error should be thrown when I try to construct a SPR when adding parts on a TOC with invalid ER
    Given I authenticate as the intranet user "user-service@tld.fr"
    And I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/parts/spare_parts_request_from_toc/37030"
    Then the response status code should be 400

  Scenario: As authorized user I should be able to construct a SPR when adding parts on a TOC
    Given I authenticate as the intranet user "user-service@tld.fr"
    And I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/parts/spare_parts_request_from_toc/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/spare_parts_request/schemas/spare_parts_request_from_toc.json"
