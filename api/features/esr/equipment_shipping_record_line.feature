Feature: Test Equipment Shipping Record Line API

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine" is exposed on the API
    Then the filter "equipmentRecord.manufacturerLocation" should be available and its type should be "string"
    And the filter "estimatedPickUpDate[after]" should be available and its type should be "DateTimeInterface"
    And the filter "estimatedPickUpDate[before]" should be available and its type should be "DateTimeInterface"
    And the filter "order[estimatedPickUpDate]" should be available and its type should be "string"
    Then the filter "order[equipmentShippingRecord.id]" should be available and its type should be "string"

  Scenario: Request all Equipment Shipping Record Lines as XU should not be possible
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/equipment_shipping_record_lines"
    Then the response status code should be 403

  Scenario: Request all Equipment Shipping Record Lines as vendor user should not be possible
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/equipment_shipping_record_lines"
    Then the response status code should be 403

  Scenario: Request all Equipment Shipping Record Lines as XU should not be possible
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/equipment_shipping_record_lines/1"
    Then the response status code should be 403

  Scenario: Request all Equipment Shipping Record Lines as vendor user should not be possible
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/equipment_shipping_record_lines/1"
    Then the response status code should be 403

  Scenario: Request all Equipment Shipping Record Lines
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/equipment_shipping_record_lines"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_shipping_record_line/schemas/equipment_shipping_record_lines.json"

  Scenario: Request a given Equipment Shipping Record Line
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/equipment_shipping_record_lines/1"
    Then the response status code should be 403

  Scenario: Request a given Equipment Shipping Record Line
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/equipment_shipping_record_lines/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_shipping_record_line/schemas/equipment_shipping_record_line.json"

  Scenario: Update a given Equipment Shipping Record Line with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/equipment_shipping_record_lines/1" with body:
    """
    {
      "equipmentRecord": "/equipment_records/10",
      "estimatedPickUpDate": "2024-08-26T00:00:00-0400",
      "vesselLoadingDate": "2024-08-26T00:00:00-0400",
      "estimatedArrivalDate": "2024-08-26T00:00:00-0400",
      "actualArrivalDate": "2024-08-26T00:00:00-0400",
      "truckType": "26 tonnes FAILED",
      "comment": "I will fail"
    }
    """
    Then the response status code should be 403

  Scenario: Update with an ER already in an ESR is not possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/equipment_shipping_record_lines/1" with body:
    """
    {
      "equipmentRecord": "/equipment_records/10",
      "estimatedPickUpDate": "2024-08-26T00:00:00-0400",
      "vesselLoadingDate": "2024-08-26T00:00:00-0400",
      "estimatedArrivalDate": "2024-08-26T00:00:00-0400",
      "actualArrivalDate": "2024-08-26T00:00:00-0400",
      "truckType": "26 tonnes",
      "comment": "Not too much plz put"
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to the string "equipmentRecord: <br> Serial number SN_0010 already belongs to another open Equipment Shipping Record: 5. This Equipment Record must be removed from one of the open ESR before saving. <br>"

  Scenario: Update a given Equipment Shipping Record Line with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/equipment_shipping_record_lines/1" with body:
    """
    {
      "equipmentRecord": "/equipment_records/8",
      "estimatedPickUpDate": "2024-08-26T00:00:00-0400",
      "vesselLoadingDate": "2024-08-26T00:00:00-0400",
      "estimatedArrivalDate": "2024-08-26T00:00:00-0400",
      "actualArrivalDate": "2024-08-26T00:00:00-0400",
      "truckType": "26 tonnes",
      "comment": "Not too much plz put"
    }
    """
    Then the response status code should be 200
    And the JSON node "equipmentRecord" should be equal to "/equipment_records/8"
    And the JSON node "estimatedPickUpDate" should be equal to "2024-08-26T00:00:00-04:00"
    And the JSON node "vesselLoadingDate" should be equal to "2024-08-26T00:00:00-04:00"
    And the JSON node "estimatedArrivalDate" should be equal to "2024-08-26T00:00:00-04:00"
    And the JSON node "actualArrivalDate" should be equal to "2024-08-26T00:00:00-04:00"
    And the JSON node "truckType" should be equal to "26 tonnes"
    And the JSON node "comment" should be equal to "Not too much plz put"

  Scenario: Create a Equipment Shipping Record Line with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/equipment_shipping_record_lines" with body:
    """
    {
      "equipmentRecord": "/equipment_records/7",
      "estimatedPickUpDate": "2024-08-28T00:00:00-0400",
      "vesselLoadingDate": "2024-08-28T00:00:00-0400",
      "estimatedArrivalDate": "2024-08-28T00:00:00-0400",
      "actualArrivalDate": "2024-08-28T00:00:00-0400",
      "truckType": "26 tonnes",
      "comment": "Not too much plz post",
      "equipmentShippingRecord": "/sales/equipment_shipping_records/1"
    }
    """
    Then the response status code should be 403

  Scenario: Create a Equipment Shipping Record Line can only be done with permission
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/equipment_shipping_record_lines" with body:
    """
    {
      "equipmentRecord": "/equipment_records/9",
      "estimatedPickUpDate": "2024-08-28T00:00:00-0400",
      "vesselLoadingDate": "2024-08-28T00:00:00-0400",
      "estimatedArrivalDate": "2024-08-28T00:00:00-0400",
      "actualArrivalDate": "2024-08-28T00:00:00-0400",
      "truckType": "28 tonnes",
      "comment": "Not too much plz post",
      "equipmentShippingRecord": "/sales/equipment_shipping_records/1"
    }
    """
    Then the response status code should be 201
    And the JSON node "equipmentRecord" should be equal to "/equipment_records/9"
    And the JSON node "estimatedPickUpDate" should be equal to "2024-08-28T00:00:00-04:00"
    And the JSON node "vesselLoadingDate" should be equal to "2024-08-28T00:00:00-04:00"
    And the JSON node "estimatedArrivalDate" should be equal to "2024-08-28T00:00:00-04:00"
    And the JSON node "actualArrivalDate" should be equal to "2024-08-28T00:00:00-04:00"
    And the JSON node "truckType" should be equal to "28 tonnes"
    And the JSON node "comment" should be equal to "Not too much plz post"

  Scenario: Create a Equipment Shipping Record Line with the same ER should be not possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/equipment_shipping_record_lines" with body:
    """
    {
      "equipmentRecord": "/equipment_records/9",
      "estimatedPickUpDate": "2024-08-28T00:00:00-0400",
      "vesselLoadingDate": "2024-08-28T00:00:00-0400",
      "estimatedArrivalDate": "2024-08-28T00:00:00-0400",
      "actualArrivalDate": "2024-08-28T00:00:00-0400",
      "truckType": "28 tonnes",
      "comment": "Not too much plz post",
      "equipmentShippingRecord": "/sales/equipment_shipping_records/1"
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to the string "equipmentRecord: This ER was already added to this ESR."

  Scenario: Delete a Equipment Shipping Record Line with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/equipment_shipping_record_lines/2"
    Then the response status code should be 403

  Scenario: Delete a Equipment Shipping Record Line with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/equipment_shipping_record_lines/1"
    Then the response status code should be 204