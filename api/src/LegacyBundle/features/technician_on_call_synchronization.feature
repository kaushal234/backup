Feature: Test TOC double write API

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: When I create a TOC and I changed airport and SSO Service, the ER data should be updated and double write
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_calls" with body:
    """
    {
      "originalTitle": "TOC created by POST",
      "originalDescription": "This is really a big problem, please fix quickly",
      "equipmentRecord": "/equipment_records/1",
      "assignee": "/people/65",
      "errorCodes": "404",
      "unitOperationalStatus": "/unit_operational_statuses/MCF",
      "technicianOnCallType": "/service/technician_on_call_types/1",
      "serviceActivity": "/service/service_activities/1",
      "tags": [
        "/technician_on_call_tags/1",
        "/technician_on_call_tags/2",
        "/technician_on_call_tags/3"
      ],
      "indiceFactor": "IF 1",
      "airport": "/airports/111",
      "salesOrganisationService": "/locations/28",
      "customer": "/sales/customers/1",
      "mainContact": "sales/extranet_users/200"
    }
    """
    Then the response status code should be 201
    And the column "airport_code" from the "service" legacy table has been updated with string "PLO"
    And the column "sso_service" from the "service" legacy table has been updated with string "location_sso_2"

  Scenario: When I create a TOC, it is double-written
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_calls" with body:
    """
    {
      "originalTitle": "TOC Double-Write",
      "originalDescription": "This TOC is created for double-write testing",
      "equipmentRecord": "/equipment_records/6",
      "assignee": "/people/65",
      "technician": "/people/40",
      "errorCodes": "ERR-OR",
      "unitOperationalStatus": "/unit_operational_statuses/NMC",
      "technicianOnCallType": "/service/technician_on_call_types/3",
      "serviceActivity": "/service/service_activities/4",
      "indiceFactor": "IF 100",
      "airport": "/airports/110",
      "salesOrganisationService": "/locations/27",
      "thirdPartyName": "Sample Air",
      "factoryFlag": true,
      "mainContact": "/sales/extranet_users/200",
      "customer": "/sales/customers/1",
      "confidential": true
    }
    """
    Then the response status code should be 201
    And the column "short_desc" from the "toc" legacy table has been inserted with string "TOC Double-Write"
    And the column "prob_dsca" from the "toc" legacy table has been inserted with string "This TOC is created for double-write testing"
    And the column "status" from the "toc" legacy table has been inserted with string "IN_PROGRESS"
    And the column "dt" from the "toc" legacy table has been inserted
    And the column "postid" from the "toc" legacy table has been inserted with integer 2359
    And the column "erid" from the "toc" legacy table has been inserted with integer 37460
    And the column "assid" from the "toc" legacy table has been inserted with integer 2412
    And the column "tecid" from the "toc" legacy table has been inserted with integer 2387
    And the column "error_codes" from the "toc" legacy table has been inserted with string "ERR-OR"
    And the column "unit_operation_status" from the "toc" legacy table has been inserted with string "NMC"
    And the column "toc_type" from the "toc" legacy table has been inserted
    And the column "activity_type" from the "toc" legacy table has been inserted
    And the column "ifactor" from the "toc" legacy table has been inserted with string "IF 100"
    And the column "apc" from the "toc" legacy table has been inserted with string "JMA"
    And the column "cuid" from the "toc" legacy table has been inserted with integer 4074
    And the column "third_party" from the "toc" legacy table has been inserted with string "Y"
    And the column "conid" from the "toc" legacy table has been inserted with integer 7200
    And the column "notification" from the "toc" legacy table has been inserted with string "N"

  Scenario: When I set Who Pays as Factory on a TOC, a WC is created
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/32" with body:
    """
    {
      "technicianOnCallType": "/service/technician_on_call_types/4"
    }
    """
    Then the response status code should be 200
    And a new row has been inserted in the legacy table "warranty"

  Scenario: When I set Who Pays as Factory on a TOC with a REJECTED warranty, the warranty status must change to PENDING
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/2" with body:
    """
    {
      "technicianOnCallType": "/service/technician_on_call_types/4"
    }
    """
    Then the response status code should be 200
    And the column "warranty_status" from the "warranty" legacy table has been updated with string "PENDING"

  Scenario: When I delete a TOC with a PENDING WC, the WC and its mod_link are deleted from legacy DB
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/service/technician_on_calls/32"
    Then the response status code should be 204
    And a row with "parent_id" = 37460 should not exist in the legacy table "warranty"
    And a row with "parent_id" = 2359 should not exist in the legacy table "mod_links"

  Scenario: When I change again TOC type to factory with existing warranty, no warranty should be created
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/2" with body:
    """
    {
      "technicianOnCallType": "/service/technician_on_call_types/4"
    }
    """
    Then the response status code should be 200
    And 0 new rows have been inserted in the legacy table warranty
