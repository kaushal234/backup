Feature: On Time Delivery Planning

  Scenario: As user-basic@tld.fr I should not be able to PUT on ODP route
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_records_odp/15" with body:
    """
    {
      "@id": "\/equipment_records\/15",
      "id": 15,
      "odpComment": "Oh my God ! I can't comment !"
    }
    """
    Then the response status code should be 403
    And the JSON node "hydra:description" should be equal to the string "Access Denied"

    #See unit tests for more rules
  Scenario: Update estimated GT date should send a notification
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_records_odp/21" with body:
    """
    {
      "estimatedGreenTagDate": "2099-01-31 20:10:00"
    }
    """
    Then the response status code should be 200
    And the JSON node "estimatedGreenTagDate" should be equal to the string "2099-01-31T20:10:00-05:00"
    And an email should have been sent asynchronously with subject "Escalation estimated GT date with important deviation"
    And this asynchronous email should be sent to "user-gcoo@tld.fr"
    And this asynchronous email should be sent to "user-cmo@tld.fr"
    And this asynchronous email should be sent as cc to "user-pm@tld.fr"
    And this asynchronous email should be sent as cc to "user-asm@tld.fr"

  Scenario: As user-qam@tld.fr I can't GT an Equipment Record with no estimatedGreenTagDate
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_records_odp/14" with body:
    """
    {
      "@id": "\/equipment_records\/14",
      "id": 14,
      "greenTagDate": "2024-03-02T00:00:00-05:00"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Equipment Record 37468 cannot be GreenTagged without Estimated GreenTag Date"

  Scenario: As user-qam@tld.fr I can't edit ODP comment
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_records_odp/19" with body:
    """
    {
      "odpComment": "Comme en terre"
    }
    """
    Then the response status code should be 403

  Scenario: As user-superuser@tld.fr I have an error if i edit an Equipment Record with null component on serial
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_records_odp/8" with body:
    """
    {
      "odpComment": "Comme en terre"
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should contain "Components on Equipment Record Serial list have to be edited: https://www.tld-gse.com/en/private/support/serials/8/show"

  Scenario: As user-qam@tld.fr I can GT a Light Equipment Record without Order
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_records_odp/19" with body:
    """
    {
      "greenTagDate": "2024-03-02T00:00:00-05:00"
    }
    """
    Then the response status code should be 200
    And the JSON node "greenTagDate" should be equal to the string "2024-03-02T00:00:00-05:00"

  Scenario: As user-qam@tld.fr I can't edit DateShipped
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_records_odp/19" with body:
    """
    {
      "dateShipped": "2024-03-07T00:00:00-05:00"
    }
    """
    Then the response status code should be 403

  Scenario: As a superUser I can create a CSR from an ER no light shipped and commissioned
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_records_odp/25" with body:
    """
    {
      "dateShipped": "2024-03-07T00:00:00-05:00"
    }
    """
    Then the response status code should be 200
    And an email should have been sent asynchronously with subject "ODP Update - SOR#not Found SOL#18829 SN#PDI2025 End User: customer_for_fur - Shipped date Change"
    And this asynchronous email should be sent to "user-sam@tld.fr"
    And this asynchronous email should be sent to "user-sa@tld.fr"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/service/customer_service_records/22"
    Then the response status code should be 404

  Scenario: As user-qam@tld.fr I can't GreenTag an Equipment Record with a closed Order Line
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_records_odp/20" with body:
    """
    {
      "greenTagDate": "2024-03-02T00:00:00-05:00"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "EquipmentRecord 37474 cannot be GreenTagged because its order line status is CLOSED"

  Scenario: As user-psm@tld.fr I can't GreenTag an Equipment Record
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_records_odp/17" with body:
    """
    {
      "greenTagDate": "2024-03-02T00:00:00-05:00"
    }
    """
    Then the response status code should be 403

  Scenario: As user-psm@tld.fr I can't Ship an Equipment Record without GreenTagDate
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_records_odp/1" with body:
    """
    {
      "dateShipped": "2024-03-02T00:00:00-05:00"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Equipment Record 37455 cannot be shipped without a GreenTag"

  Scenario: Get Pre-Delivery Inspections past 12 months Report
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "reports/resource=/equipment_records;x=month;y=inspection_rate_per_sso?options[totalsAsAverages]=1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "total" should be equal to the number 2.78

  Scenario: Get Upcoming Pre-Delivery Inspections Report
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "reports/resource=/equipment_records;x=manufacturerLocation.name;y=salesOrganisation.name?options[preDeliveryInspections]=+30"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "total" should be equal to the number 1

  Scenario: Test I can see Sso dashboard
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/equipment_records;x=salesOrganisation;y=dashboard"
    Then the response status code should be 200
    And the JSON node "resource" should be equal to the string "/equipment_records"
    And the JSON node "@type" should be equal to the string "Report"
    And the JSON node "x" should be equal to the string "salesOrganisation"
    And the JSON node "y" should be equal to the string "dashboard"

  Scenario: Test I can use filter gt not shipped with no estimated pick up date and customer responsible for pick up
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records?itemsPerPage=2000&normalization_groups[0]=odp%3Aview&normalization_groups[1]=expose_legacy&normalization_groups[2]=order_line&normalization_groups[3]=order_transaction&normalization_groups[4]=order_factory&odpFilter=gt_not_shipped_no_estimated_pick_up_date_customer_responsible&order[id]=DESC&salesOrganisation=%2Flocations%2F30"
    Then the response status code should be 200
    And the JSON node "@id" should be equal to the string "/equipment_records"
    And the JSON node "@type" should be equal to the string "hydra:Collection"

  Scenario: Test I can use filter gt not shipped with no estimated pick up date and tld responsible for pick up
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records?itemsPerPage=2000&normalization_groups[0]=odp%3Aview&normalization_groups[1]=expose_legacy&normalization_groups[2]=order_line&normalization_groups[3]=order_transaction&normalization_groups[4]=order_factory&odpFilter=gt_not_shipped_no_estimated_pick_up_date_tld_responsible&order[id]=DESC&salesOrganisation=%2Flocations%2F30"
    Then the response status code should be 200
    And the JSON node "@id" should be equal to the string "/equipment_records"
    And the JSON node "@type" should be equal to the string "hydra:Collection"

  Scenario: Test I can use filter green tag not shipped no estimated pick up date and not authorized
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records?itemsPerPage=2000&normalization_groups[0]=odp%3Aview&normalization_groups[1]=expose_legacy&normalization_groups[2]=order_line&normalization_groups[3]=order_transaction&normalization_groups[4]=order_factory&odpFilter=gt_not_shipped_no_estimated_pick_up_date_not_granted&order[id]=DESC&salesOrganisation=%2Flocations%2F30"
    Then the response status code should be 200
    And the JSON node "@id" should be equal to the string "/equipment_records"
    And the JSON node "@type" should be equal to the string "hydra:Collection"
