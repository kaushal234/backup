Feature: Test Technician On Call

  Scenario: Download excel TOC reports should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/service/technician_on_calls?columns=id,createdAt,createdBy,salesOrganisationService.name,assignee,technician,status,serviceActivity.name,technicianOnCallType.name,indiceFactor,unitOperationalStatus.name,equipmentRecord.model,equipmentRecord.serialNumber,mainContact.fullName,title,updatedAt,factoryFlag,airport.code,customer.name,openDays,daysWithoutActivity,csrList,sparePartsRequest.count,equipmentRecord.manufacturerLocation.name,solvedAt,daysToSolved,factoryFlagHistory,hourmeter,partsList,sprList,warrantyLegacyId,thirdPartyName,thirdPartyRef"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | ID | Creation Date | Created By | SSO Service | Assignee | Technician | Status | Service Activity | Who Pays | IF | Unit Operational Status | Model | SN | Main Contact | Title | Last Update | FF | Airport Code | TOC's Customer | Open Days | Days Without Activity | CSR No. | Number Of SPR | Factory | Date Solved | Days To Solved | FF History | Hourmeter | TOC Parts | SPR Parts | WC No. | Third Party | Third Party Ref |

  Scenario: As a basic user, I should access to the report of TOC Survey
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/reports/resource=/service/technician_on_call_surveys;x=salesOrganisationService.name;y=quantity"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "@type" should be equal to the string "Report"
    And the JSON node "x" should be equal to the string "salesOrganisationService.name"
    And the JSON node "y" should be equal to the string "quantity"

  Scenario: As a service user, I should access to the report of TOC oldest
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
#    Then I load Technician On Call oldest data
    When I send a "GET" request to "/reports/resource=/service/technician_on_calls_oldest_reports;x=month;y=oldest"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "@type" should be equal to the string "Report"
    And the JSON node "x" should be equal to the string "month"
    And the JSON node "y" should be equal to the string "oldest"
#    And the JSON node "total" should be equal to the number 4560

  Scenario: As a service user, I should access to the KPI
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
#    Then I load Technician On Call KPI data
    When I send a "GET" request to "/reports/resource=/service/technician_on_calls;x=month;y=quantity"
    Then the response status code should be 200
    And the JSON node "x" should be equal to the string "month"
    And the JSON node "y" should be equal to the string "quantity"
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
#    And the JSON node "total" should be equal to the number 39
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/service/technician_on_calls;x=month;y=tir"
    Then the response status code should be 200
    And the JSON node "x" should be equal to the string "month"
    And the JSON node "y" should be equal to the string "tir"
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
#    And the JSON node "total" should be equal to the number 575
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/service/technician_on_calls;x=month;y=tat"
    Then the response status code should be 200
    And the JSON node "x" should be equal to the string "month"
    And the JSON node "y" should be equal to the string "tat"
#    And the JSON node "total" should be equal to the number 7.75
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: As a service user, I should access to the closure time report
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
#    Then I load Technician On Call closure time data
    When I send a "GET" request to "/service/technician-on-call/closure_time_reports"
    Then the response status code should be 200
    And the JSON node "hydra:member[0].delay" should be equal to the string "less_than_7"
#    And the JSON node "hydra:member[0].numberOfTechnicianOnCalls" should be equal to the number 2
#    And the JSON node "hydra:member[0].percentRemoteSolved" should be equal to the number 50
#    And the JSON node "hydra:member[0].deltaWithYear" should be equal to "-69.7"

  Scenario: As a service user, I should access to the Operate report
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
#    Then I load Technician On Call Operate data
    When I send a "GET" request to "/reports/resource=/service/technician_on_calls;x=date;y=operate"
    Then the response status code should be 200
    And the JSON node "x" should be equal to the string "date"
    And the JSON node "y" should be equal to the string "operate"
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
#    And the JSON node "total" should be equal to the number 21
#    And the JSON node "rows.TWO_INTERVENTIONS.Value.value" should be equal to the number 2

  Scenario: As a service user, I should access to the Troubleshooting report
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
#    Then I load Technician On Call Operate data
    When I send a "GET" request to "/reports/resource=/service/technician_on_calls;x=date;y=troubleshooting"
    Then the response status code should be 200
    And the JSON node "x" should be equal to the string "date"
    And the JSON node "y" should be equal to the string "troubleshooting"
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
#    And the JSON node "total" should be equal to the number 15
#    And the JSON node "rows.WITH_CSR.Value.value" should be equal to the number 13

  Scenario: As a service user, I should access to the Spare part request report
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
#    Then I load Technician On Call Operate data
    When I send a "GET" request to "/reports/resource=/service/technician_on_calls;x=date;y=sparePartRequest"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "x" should be equal to the string "date"
    And the JSON node "y" should be equal to the string "sparePartRequest"
#    And the JSON node "rows.WITH_SPR.Value.value" should be equal to the number 1

  Scenario: As a service user, I should access to the Interventions By Model report
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
#    Then I load Technician On Call Operate data
    When I send a "GET" request to "/reports/resource=/service/customer_service_records;x=equipmentRecord.model;y=salesOrganisationService.name"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "x" should be equal to the string "equipmentRecord.model"
    And the JSON node "y" should be equal to the string "salesOrganisationService.name"
#    And the JSON node "total" should be equal to the number 8
#    And the JSON node "rows.TXL-737.location_sso.value" should be equal to the number 8

  Scenario: As a service user, I should access to the report of TOC backlog
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
#    Then I generate Technician On Call backlog data
    When I send a "GET" request to "/reports/resource=/service/technician_on_call/backlog_reports;x=month;y=backlog"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "x" should be equal to the string "month"
    And the JSON node "y" should be equal to the string "backlog"
#    And the JSON node "total" should be equal to the number 16
#    And the JSON node "yTotals.location_sso" should be equal to the number 7

  Scenario: As a service user, I should access to the report of TOC created and solved since one year by month
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/service/technician_on_calls;x=month;y=quantity"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "@type" should be equal to the string "Report"
    And the JSON node "x" should be equal to the string "month"
    And the JSON node "y" should be equal to the string "quantity"

  Scenario: As a service user, I should access to the report of TOC factory flag by model
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/service/technician_on_calls;x=equipmentRecord.model;y=factoryFlag"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "@type" should be equal to the string "Report"
    And the JSON node "x" should be equal to the string "equipmentRecord.model"
    And the JSON node "y" should be equal to the string "factoryFlag"
#    And the JSON node "yTotals.location_sso" should be equal to the number 2
#    And the JSON node "yTotals.location_sso_2" should be equal to the number 1

  Scenario: As a service user, I should access to the report of TOC Time SPR Dispatch
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/reports/resource=/parts/spare_parts_requests;x=delay;y=salesOrganisationService.name"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "@type" should be equal to the string "Report"
    And the JSON node "x" should be equal to the string "delay"
    And the JSON node "y" should be equal to the string "salesOrganisationService.name"

  Scenario: Resource should be accessible to extranet user (if they are allowed)
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_calls.json"
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_call.json"
    Given I authenticate as the extranet user "contract-buyer-extra@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 0 element
    Given I authenticate as the extranet user "contract-buyer-extra@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls/1"
    Then the response status code should be 403

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Service\TechnicianOnCall" is exposed on the API
    Then the filter "order[id]" should be available and its type should be "string"
    Then the filter "order[airport.code]" should be available and its type should be "string"
    Then the filter "order[indiceFactor]" should be available and its type should be "string"
    Then the filter "order[createdAt]" should be available and its type should be "string"
    Then the filter "order[updatedAt]" should be available and its type should be "string"
    Then the filter "order[createdBy.lastname]" should be available and its type should be "string"
    Then the filter "order[customer.name]" should be available and its type should be "string"
    Then the filter "id" should be available and its type should be "int"
    And the filter "status" should be available and its type should be "string"
    And the filter "equipmentRecord" should be available and its type should be "string"
    And the filter "airport" should be available and its type should be "string"
    And the filter "airport.country" should be available and its type should be "string"
    And the filter "assignee" should be available and its type should be "string"
    And the filter "unitOperationalStatus" should be available and its type should be "string"
    And the filter "serviceActivity" should be available and its type should be "string"
    And the filter "indiceFactor" should be available and its type should be "string"
    And the filter "tags" should be available and its type should be "string"
    And the filter "createdBy" should be available and its type should be "string"
    And the filter "salesOrganisationService" should be available and its type should be "string"
    And the filter "equipmentRecord.product.family.productType" should be available and its type should be "string"
    And the filter "equipmentRecord.product" should be available and its type should be "string"
    Then the filter "createdAt[after]" should be available and its type should be "DateTimeInterface"
    Then the filter "createdAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "equipmentRecord.salesOrganisation" should be available and its type should be "string"
    And the filter "equipmentRecord.manufacturerLocation" should be available and its type should be "string"
    And the filter "equipmentRecord.buyer" should be available and its type should be "string"
    And the filter "equipmentRecord.endUser" should be available and its type should be "string"
    And the filter "equipmentRecord.maintainer" should be available and its type should be "string"
    And the filter "parts.partNumber" should be available and its type should be "string"
    And the filter "sparePartsRequests.parts.partNumber" should be available and its type should be "string"
    And the filter "title" should be available and its type should be "string"
    And the filter "errorCodes" should be available and its type should be "string"
    And the filter "confidential" should be available and its type should be "bool"
    And the filter "actor" should be available and its type should be "array"
    And the filter "airport.serviceAreas" should be available and its type should be "string"
    And the filter "thirdPartyRef" should be available and its type should be "string"

  Scenario: Request all TOCs as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls"
    Then the response status code should be 403

  Scenario: Request a single TOC as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls/1"
    Then the response status code should be 403

  Scenario: Request all TOCs as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_calls.json"
    And the JSON node "hydra:member" should not be null

  Scenario: Request a single TOC as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_call.json"
    And the JSON node "title" should be equal to the string "First TOC"
    And the JSON node "parts" should have 1 element
    And the JSON node "parts[0].description" should be equal to the string "Part"

  Scenario: Request a single TOC without Equipment Record as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls/21"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_call.json"
    And the JSON node "equipmentRecord" should be null

  Scenario: Request a single TOC as anonymous user should not be possible without the correct token
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls/45/from_token?token=token2"
    Then the response status code should be 404

  Scenario: Request a single TOC as anonymous user should be possible with the correct token
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls/45/from_token?token=token"
    Then the response status code should be 200

  Scenario: Request a TOC with SB link to the ER
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls/24"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_call.json"
    And the JSON node "serviceBulletins[0].title" should be equal to the string "NBL BRAKE PEDAL ADJUSTMENT"

  Scenario: As AST user, I should filter TOC to see only recently factory flag
    Given I authenticate as the intranet user "user-ast-bu2@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls?factoryFlagRecentlyClosed=true"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 1 elements

  Scenario: As a extranet user, I cannot get confidential TOC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls/22"
    Then the response status code should be 200
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls/22"
    Then the response status code should be 404

  Scenario: As basic user, I should be able to create a TOC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_calls" with body:
    """
    {
      "originalTitle": "TOC created by POST",
      "originalDescription": "This is really a big problem, please fix quickly",
      "equipmentRecord": "/equipment_records/30",
      "assignee": "/people/65",
      "technician": "/people/40",
      "errorCodes": "404",
      "unitOperationalStatus": "/unit_operational_statuses/MCF",
      "technicianOnCallType": "/service/technician_on_call_types/1",
      "serviceActivity": "/service/service_activities/1",
      "tags": [
        "/technician_on_call_tags/1",
        "/technician_on_call_tags/2",
        "/technician_on_call_tags/3"
      ],
      "mainContact": "sales/extranet_users/200",
      "contacts": [
        "sales/extranet_users/203"
      ],
      "indiceFactor": "IF 1",
      "airport": "/airports/61",
      "salesOrganisationService": "/locations/38",
      "customer": "/sales/customers/1"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_call.json"
    And the JSON node "title" should be equal to the string "TOC created by POST"
    And the JSON node "originalTitle" should be equal to the string "TOC created by POST"
    And the JSON node "originalDescription" should be equal to the string "This is really a big problem, please fix quickly"
    And the JSON node "description" should be equal to the string "This is really a big problem, please fix quickly"
    And the JSON node "status" should be equal to the string "IN_PROGRESS"
    And an email should have been sent asynchronously with subject matching pattern "/TOC#\d+, Day 1, location_sso, AIR DE RIEN, EZTow - OPEN and ASSIGNED/"
    And this asynchronous email should be sent only to "user-ast@tld.fr, user-basic@tld.fr, user-service@tld.fr, srme-ibs@tld.fr, srme-link@tld.fr, support@smart-airport-systems.com"

  Scenario: As basic user, a email can be sent from a TOC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_calls/1/email" with body:
    """
    {
      "to": "/people/12",
      "subject": "Sujet de test",
      "message": "Ceci est un message de test.",
      "ccs": [
          "/people/2",
          "/people/3"
      ],
      "bccs": [
          "/people/4"
      ]
    }
    """
    Then the response status code should be 204
    And an email should have been sent asynchronously with subject "TOC#1 : Sujet de test"
    And this asynchronous email should be sent only to "user-superuser@tld.fr"

  Scenario: As basic user, a email can be sent from a TOC, with empty message
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_calls/1/email" with body:
    """
    {
      "to": "/people/12",
      "subject": "Sujet de test Without Message",
      "ccs": [
          "/people/2",
          "/people/3"
      ],
      "bccs": [
          "/people/4"
      ]
    }
    """
    Then the response status code should be 204
    And an email should have been sent asynchronously with subject "TOC#1 : Sujet de test Without Message"
    And this asynchronous email should be sent only to "user-superuser@tld.fr"

  Scenario: As basic user, a specific email should be sent when I create a TOC on a BLACK-CAT unit
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_calls" with body:
    """
    {
      "originalTitle": "TOC Black Cat",
      "originalDescription": "This TOC is on a BlackCat Unit",
      "equipmentRecord": "/equipment_records/3",
      "assignee": "/people/65",
      "technician": "/people/40",
      "errorCodes": "BC",
      "unitOperationalStatus": "/unit_operational_statuses/MCP",
      "technicianOnCallType": "/service/technician_on_call_types/1",
      "serviceActivity": "/service/service_activities/1",
      "tags": [
        "/technician_on_call_tags/1",
        "/technician_on_call_tags/2",
        "/technician_on_call_tags/3"
      ],
      "mainContact": "sales/extranet_users/200",
      "contacts": [
        "sales/extranet_users/203"
      ],
      "indiceFactor": "IF 100",
      "airport": "/airports/61",
      "salesOrganisationService": "/locations/23",
      "customer": "/sales/customers/1"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_call.json"
    And the JSON node "title" should be equal to the string "TOC Black Cat"
    And the JSON node "originalTitle" should be equal to the string "TOC Black Cat"
    And an email should have been sent asynchronously with subject matching pattern "/TOC#\d+, Day 1, location_sso, AIR DE RIEN, Produit Test - OPEN on BLACK CAT/"
    And this asynchronous email should be sent only to "user-basic@tld.fr, user-evp@tld.fr, user-pse@tld.fr, user-em@tld.fr, user-psm@tld.fr, service-hub@location-sso.com, srme-ibs@tld.fr, srme-link@tld.fr, support@smart-airport-systems.com"

  Scenario: As basic user, When I create a TOC, I should ask to create CSR in the same time, but CSR creation is not allowed
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    Then I send a "POST" request to "/service/technician_on_calls" with body:
    """
    {
      "nestedCustomerServiceRecord": {
          "leader": "/people/64",
          "plannedAt": "2022-07-20T05:02:14-04:00"
      },
      "originalTitle": "TOC created by POST",
      "originalDescription": "This is really a big problem, please fix quickly",
      "equipmentRecord": "/equipment_records/1",
      "assignee": "/people/65",
      "technician": "/people/40",
      "unitOperationalStatus": "/unit_operational_statuses/MCF",
      "technicianOnCallType": "/service/technician_on_call_types/1",
      "serviceActivity": "/service/service_activities/1",
      "indiceFactor": "IF 1",
      "airport": "/airports/61",
      "salesOrganisationService": "/locations/23",
      "customer": "/sales/customers/1",
      "mainContact": "sales/extranet_users/200"
    }
    """
    Then the response status code should be 206
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_call_customer_service_record.json"
    And the JSON node "title" should be equal to the string "TOC created by POST"
    And the JSON node "originalTitle" should be equal to the string "TOC created by POST"
    And the JSON node "originalDescription" should be equal to the string "This is really a big problem, please fix quickly"
    And the JSON node "description" should be equal to the string "This is really a big problem, please fix quickly"
    And the JSON node "@sub_resources.customerServiceRecord.@context" should be equal to the string "/contexts/Error"
    And the JSON node "status" should be equal to the string "IN_PROGRESS"

  Scenario: As basic user, I try to add defective parts to an not closed TOC should not work
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    Then I send a "PUT" request to "/service/technician_on_calls/1/status" with body:
    """
    {
      "defectiveParts": [
        {
          "partNumber": "test",
          "description": "desc",
          "quantity": 1
        }
      ]
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "defectiveParts"
    And the JSON node "violations[0].message" should contain "Faulty parts can only be added when the TOC is solved or closed."

  Scenario: As basic user, I try to add CSR to an existing closed TOC should not work
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    Then I send a "PUT" request to "/service/technician_on_calls/10" with body:
    """
    {
      "nestedCustomerServiceRecord": {}
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "nestedCustomerServiceRecord"
    And the JSON node "violations[0].message" should contain "You cannot add CSR to a CLOSED TOC"

  Scenario: As CSM user, I try to add CSR to an existing closed TOC should not work
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    Then I send a "PUT" request to "/service/technician_on_calls/8" with body:
    """
    {
      "nestedCustomerServiceRecord": {}
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "customerServiceRecords"
    And the JSON node "violations[0].message" should contain "You cannot create new CSR, this TOC already have some. Since the TOC migration"
    And the JSON node "violations[1].propertyPath" should be equal to "nestedCustomerServiceRecord"
    And the JSON node "violations[1].message" should contain "You cannot create new CSR, this TOC already have some. Since the TOC migration"

  Scenario: As CSM user, When I create a TOC, I should ask to create CSR in the same time, and CSR should be created
    Then I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    Then I send a "POST" request to "/service/technician_on_calls" with body:
    """
    {
      "nestedCustomerServiceRecord": {
          "leader": "/people/64",
          "plannedAt": "2022-07-20T05:02:14-04:00"
      },
      "originalTitle": "TOC created by POST",
      "originalDescription": "This is really a big problem, please fix quickly",
      "equipmentRecord": "/equipment_records/1",
      "assignee": "/people/65",
      "technician": "/people/40",
      "unitOperationalStatus": "/unit_operational_statuses/MCF",
      "technicianOnCallType": "/service/technician_on_call_types/1",
      "serviceActivity": "/service/service_activities/1",
      "indiceFactor": "IF 1",
      "airport": "/airports/61",
      "salesOrganisationService": "/locations/23",
      "customer": "/sales/customers/1",
      "mainContact": "sales/extranet_users/200"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_call_customer_service_record.json"
    And the JSON node "title" should be equal to the string "TOC created by POST"
    And the JSON node "originalTitle" should be equal to the string "TOC created by POST"
    And the JSON node "originalDescription" should be equal to the string "This is really a big problem, please fix quickly"
    And the JSON node "description" should be equal to the string "This is really a big problem, please fix quickly"
    And the JSON node "status" should be equal to the string "IN_PROGRESS"
    And an email should have been sent asynchronously with subject matching pattern "/TOC#\d+ Day 1, location_sso, AIR DE RIEN, Produit Test - CSR CREATED - IF 1 -/"

  Scenario: As basic user, I should not be able to create a TOC if required field missing
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_calls" with body:
    """
    {
      "originalTitle": "title short",
      "originalDescription": "desc short"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be empty
    And the JSON node "violations[0].message" should contain "At least one of the fields EquipmentRecord or Serial must be filled in"
    And the JSON node "violations[1].propertyPath" should be equal to "originalTitle"
    And the JSON node "violations[1].message" should contain "This value is too short. It should have 12 characters or more."
    And the JSON node "violations[2].propertyPath" should be equal to "originalDescription"
    And the JSON node "violations[2].message" should contain "This value is too short. It should have 12 characters or more."
    And the JSON node "violations[3].propertyPath" should be equal to "technicianOnCallType"
    And the JSON node "violations[3].message" should contain "This value should not be blank."
    And the JSON node "violations[4].propertyPath" should be equal to "serviceActivity"
    And the JSON node "violations[4].message" should contain "This value should not be blank."
    And the JSON node "violations[5].propertyPath" should be equal to "airport"
    And the JSON node "violations[5].message" should contain "This value should not be blank."
    And the JSON node "violations[6].propertyPath" should be equal to "salesOrganisationService"
    And the JSON node "violations[6].message" should contain "This value should not be blank."
    And the JSON node "violations[7].propertyPath" should be equal to "customer"
    And the JSON node "violations[7].message" should contain "This value should not be null."

  Scenario: As basic user, I should not be allowed to edit a TOC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/1" with body:
    """
    {
      "originalTitle": "Not allowed"
    }
    """
    Then the response status code should be 403

  Scenario: As service user, I should have an error when trying to send wrong values
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/1" with body:
    """
    {
      "indiceFactor": "IF 1 000 000 000 000 000 000 000"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "indiceFactor"
    And the JSON node "violations[0].message" should contain "The value you selected is not a valid choice."

  Scenario: As service user, I should have an error when trying to send wrong status
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/1" with body:
    """
    {
      "status": "Not existing status"
    }
    """
    Then the response status code should be 400
    And the JSON node "detail" should be equal to "Status Not existing status is not allowed."

  Scenario: As a basic user, I should be able to change status to IN PROGRESS
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/2/status" with body:
    """
    {
      "status": "IN_PROGRESS"
    }
    """
    Then the response status code should be 200

  Scenario: As a basic user, I cannot solved a technician on call without mandatory closing details
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/5/status" with body:
    """
    {
      "status": "SOLVED"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "originalSymptoms"
    And the JSON node "violations[0].message" should be equal to "This value should not be blank."
    And the JSON node "violations[1].propertyPath" should be equal to "originalRootCause"
    And the JSON node "violations[1].message" should be equal to "This value should not be blank."
    And the JSON node "violations[2].propertyPath" should be equal to "originalSolution"
    And the JSON node "violations[2].message" should be equal to "This value should not be blank."

  Scenario: As a service user, I should be able to update a TOC
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/2" with body:
    """
    {
      "originalTitle": "This is the Second TOC",
      "originalDescription": "Finally it's not a big problem",
      "equipmentRecord": "/equipment_records/1",
      "assignee": "/people/64",
      "technician": "/people/40",
      "errorCodes": "403",
      "unitOperationalStatus": "/unit_operational_statuses/NMC",
      "technicianOnCallType": "/service/technician_on_call_types/3",
      "serviceActivity": "/service/service_activities/5",
      "tags": [],
      "indiceFactor": "IF 100",
      "airport": "/airports/62",
      "salesOrganisationService": "/locations/23"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_call.json"
    And the JSON node "title" should be equal to the string "This is the Second TOC"
    And the JSON node "originalTitle" should be equal to the string "This is the Second TOC"
    And the JSON node "originalDescription" should be equal to the string "Finally it's not a big problem"
    And the JSON node "description" should be equal to the string "Finally it's not a big problem"
    And the JSON node "equipmentRecord.@id" should be equal to the string "/equipment_records/1"
    And the JSON node "assignee.@id" should be equal to the string "/people/64"
    And an email should have been sent asynchronously with subject matching pattern "/TOC#\d, Day 1, location_sso, customer_for_fur, Produit Test has been updated/"
    And this asynchronous email should be sent only to "user-ceo@tld.fr, user-csm@tld.fr, user-pse@tld.fr, user-em@tld.fr, user-psm@tld.fr, user-evp@tld.fr, user-csm@tld.fr, user-service@tld.fr"

  Scenario: As a basic user, I should NOT be able to delete a TOC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/service/technician_on_calls/3"
    Then the response status code should be 403

  Scenario: As a CSM user, I should be able to delete a TOC
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/service/technician_on_calls/3"
    Then the response status code should be 204

  Scenario: As a CSM user, I should NOT be able to delete a TOC linked to a CSR
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/service/technician_on_calls/8"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The Technician On Call '8' is not deletable because it is used by 1 Customer Service Record (20)"

  Scenario: As a CSM user, I should NOT be able to delete a TOC linked to a SPR
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/service/technician_on_calls/15"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The Technician On Call '15' is not deletable because it is used by 1 Spare Parts Request (1)"

  Scenario: As a basic user, I should be able to see all comments
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/comments?normalization_groups[]=file:light&resource=/service/technician_on_calls/7&pagination=false"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to the number "6"
    And the JSON node "hydra:member" should have 6 elements

  Scenario: As a basic user, I should be able to see all comments of linked modules
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/comments?normalization_groups[]=file:light&resource=/service/technician_on_calls/25&pagination=false&extraComment=true"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to the number "5"
    And the JSON node "hydra:member" should have 5 elements
    And the JSON node "hydra:member[0].discriminator" should be equal to the string "WC"
    And the JSON node "hydra:member[1].discriminator" should be equal to the string "PDC"
    And the JSON node "hydra:member[2].discriminator" should be equal to the string "PDC"
    And the JSON node "hydra:member[3].discriminator" should be equal to the string "CSR"
    And the JSON node "hydra:member[4].discriminator" should be equal to the string "CSR"

  Scenario: As a basic user, I should be able to add a comment
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
      "resource": "/service/technician_on_calls/7",
      "message": "Hello world"
    }
    """
    Then the response status code should be 201
    And the JSON node "message" should be equal to the string "Hello world"
    And an email should have been sent asynchronously with subject "TOC#7 Day 1, location_sso, customer_for_fur, Produit Test - New Log Entry for - IF 100 -"
    And this asynchronous email should be sent only to "user-ceo@tld.fr, user-basic@tld.fr, user-service@tld.fr, user-ast-bu2@tld.fr, user-csm@tld.fr, user-pse@tld.fr, user-em@tld.fr, user-psm@tld.fr, user-evp@tld.fr"
    And an email should have been sent asynchronously with subject matching pattern "/TOC#7, day 1, location_sso, Produit Test - New notification/"
    And this asynchronous email should be sent only to "user-campaign@tld.com"

  Scenario: As a basic user, I should be able to add a comment on a TOC linked to a Jira Tracteasy issue
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
      "resource": "/service/technician_on_calls/7",
      "message": "<p>Hello <strong>Jira</strong></p>"
    }
    """
    Then the response status code should be 201

  Scenario: When user subscribe to TOC, a comment is created an must not be public
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
      "resource": "/service/technician_on_calls/7",
      "message": "User Basic subscribed",
      "metadata": {
        "subscription": 11
      },
      "discriminator": null
    }
    """
    Then the response status code should be 201
    And the JSON node "public" should be false

  Scenario: As a service user, When I put a factory flag, then nothing should happen
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/12" with body:
    """
    {
      "factoryFlag": true
    }
    """
    Then the response status code should be 200
    And the JSON node "factoryFlag" should be false

  Scenario: As a basic user, I cannot POST a comment with factory flag
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
      "resource": "/service/technician_on_calls/11",
      "message": "Enable factory flag",
      "metadata": {"factoryFlag": "OPEN_FACTORY_FLAG"}
    }
    """
    Then the response status code should be 403

  Scenario: As a service user, I can POST a comment with factory flag, then factory support should be open
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
      "resource": "/service/technician_on_calls/11",
      "message": "Enable factory flag",
      "metadata": {"factoryFlag": "OPEN_FACTORY_FLAG"}
    }
    """
    Then the response status code should be 201
    And an email should have been sent asynchronously with subject "Factory Support is required - TOC#11, Day 1, location_sso, customer_for_fur, Produit Test"
    And this asynchronous email should be sent only to "user-ast-bu2@tld.fr, user-csm@tld.fr, user-evp@tld.fr, user-pse@tld.fr, user-em@tld.fr, user-psm@tld.fr, user-service@tld.fr"
    Then I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls/11"
    Then the JSON node "factoryFlag" should be true

  Scenario: As a basic user, I cannot POST a comment to close factory flag
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
      "resource": "/service/technician_on_calls/11",
      "message": "Enable factory flag",
      "metadata": {"factoryFlag": "CLOSE_FACTORY_FLAG"}
    }
    """
    Then the response status code should be 403

  Scenario: As a service user, I can POST a comment and remove factory flag, then factory support should be false
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
      "resource": "/service/technician_on_calls/12",
      "message": "Disable factory flag",
      "metadata": {"factoryFlag": "CLOSE_FACTORY_FLAG"}
    }
    """
    Then the response status code should be 201
    And an email should have been sent asynchronously with subject "Factory Support has been provided - TOC#12, Day 1, location_sso, customer_for_fur, Produit Test"
    And this asynchronous email should be sent only to "user-ast-bu2@tld.fr, user-csm@tld.fr, user-evp@tld.fr, user-pse@tld.fr, user-em@tld.fr, user-psm@tld.fr, user-service@tld.fr"
    Then I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls/12"
    Then the JSON node "factoryFlag" should be false

  Scenario: As a service user, I cannot solve a TOC with a factory flag OPEN
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/13" with body:
    """
    {
      "status": "SOLVED"
    }
    """
    Then the response status code should be 400
    And the JSON node "detail" should contain "the Factory Flag is still open"

  Scenario: As basic user, I am authorized to Solve a TOC through status route
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/18/status" with body:
    """
    {
      "status": "SOLVED",
      "originalSymptoms": "This is what happens",
      "originalRootCause": "This is why this happens",
      "originalSolution": "This is how to stop the issue",
      "defectiveParts": [
            {
                "partNumber": "test defective part",
                "description": "desc",
                "quantity": 1
            }
        ]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_call.json"
    And the JSON node "status" should be equal to the string "SOLVED"
    And the JSON node "defectiveParts[0].partNumber" should be equal to the string "test defective part"
    And an email should have been sent asynchronously with subject "TOC#18, for Produit Test, SN# SN_003 has been SOLVED"
    And this asynchronous email should be sent only to "user-campaign@tld.com"
    And this asynchronous email should be sent as cc to "user-basic@tld.fr"

  Scenario: As an CSM, when I reopen a CLOSED TOC, closure-specific information should be clear
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/48" with body:
    """
    {
      "status": "IN_PROGRESS"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_call.json"
    And the JSON node "status" should be equal to the string "IN_PROGRESS"
    And the JSON node "defectiveParts" should have 0 element
    And the JSON node "originalSymptoms" should be empty
    And the JSON node "originalRootCause" should be empty
    And the JSON node "originalSolution" should be empty

  Scenario: As an AST, I am not authorized to close a TOC
    Given I authenticate as the intranet user "user-ast@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/18/status" with body:
    """
    {
      "status": "CLOSED"
    }
    """
    Then the response status code should be 400

  Scenario: As an CSM, I am authorized to close a TOC
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/18/status" with body:
    """
    {
      "status": "CLOSED"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_call.json"
    And the JSON node "status" should be equal to the string "CLOSED"

  Scenario: Abasic user, I am not authorized to suspend a TOC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/19" with body:
    """
    {
      "status": "SUSPENDED"
    }
    """
    Then the response status code should be 403

  Scenario: As a basic user, I cannot open a factory flag on CLOSED TOC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
      "resource": "/service/technician_on_calls/6",
      "message": "Enable factory flag",
      "metadata": {"factoryFlag": "OPEN_FACTORY_FLAG"}
    }
    """
    Then the response status code should be 403

  Scenario: As a basic user, I cannot reopen a CLOSED TOC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/49" with body:
    """
    {
      "status" : "IN_PROGRESS"
    }
    """
    Then the response status code should be 403

  Scenario: As a CSM user, I can reopen a CLOSED TOC
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/49" with body:
    """
    {
      "status" : "IN_PROGRESS"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "IN_PROGRESS"

  Scenario: As a basic user, I can create a new part to a TOC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_call_parts" with body:
    """
    {
        "partNumber": "PN",
        "vendorPartNumber": "VPN",
        "serialNumber": "SN",
        "description": "Desc",
        "quantity": 2.1,
        "defective": true,
        "replacement": "CUSTOMER",
        "comment": "Comment",
        "technicianOnCall": "/service/technician_on_calls/1"
    }
    """
    Then the response status code should be 201
    And the JSON node "technicianOnCall" should be equal to "/service/technician_on_calls/1"

  Scenario: As a service user, I can edit a part to a TOC
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_call_parts/3" with body:
    """
    {
        "description": "Desc edited",
        "defective": true,
        "comment": "Comment edited"
    }
    """
    Then the response status code should be 200
    And the JSON node "description" should be equal to "Desc edited"
    And the JSON node "comment" should be equal to "Comment edited"
    And the JSON node "defective" should be true

  Scenario: As a basic user, I can remove a part
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/service/technician_on_call_parts/2"
    Then the response status code should be 204
    Then I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls/14"
    And the JSON node "parts" should have 1 element

  Scenario: As a basic user, I can get TOC parts
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_call_parts"
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_call_parts/1"
    Then the response status code should be 200

  Scenario: As a service user, adding a disabled extranet user as contact should not be possible
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/1" with body:
    """
    {
        "mainContact": "sales/extranet_users/210",
        "contacts": ["sales/extranet_users/210"]
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "mainContact"
    And the JSON node "violations[0].message" should be equal to "contact-archived@tld.com is disabled. Cannot be added as Contact"
    And the JSON node "violations[1].propertyPath" should be equal to "mainContact"
    And the JSON node "violations[1].message" should be equal to "contact-archived@tld.com is not allowed as Contact for this TOC : not linked to the customer"
    And the JSON node "violations[2].propertyPath" should be equal to "contacts[0]"
    And the JSON node "violations[2].message" should be equal to "contact-archived@tld.com is disabled. Cannot be added as Contact"

  Scenario: As a service user, adding a extranet user not having right CRT as contact should not be possible
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/1" with body:
    """
    {
        "mainContact": "sales/extranet_users/204",
        "contacts": ["sales/extranet_users/205"]
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "mainContact"
    And the JSON node "violations[0].message" should be equal to the string "contract-buyer@extra.net is not allowed as Contact for this TOC : not linked to the customer"
    And the JSON node "violations[1].propertyPath" should be equal to "contacts[0]"
    And the JSON node "violations[1].message" should be equal to the string "contract-enduser-extra@extra.net is not allowed as Contact for this TOC : not linked to the selected Equipment Record"

  Scenario: As service user, when I edit a TOC with hourMeter, an HourMeterTransaction is created
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/1" with body:
    """
    {
      "hourMeter": 6842
    }
    """
    Then the response status code should be 200
    And the JSON node "@sub_resources.hourMeter.@context" should be equal to the string "/contexts/TechnicianOnCallHourMeterTransaction"
    And the JSON node "@sub_resources.hourMeter.hourMeter" should be equal to 6842

  Scenario: As service user, when I edit a TOC with too low hourMeter value, HourmeterTransaction is not created
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/1" with body:
    """
    {
      "hourMeter": 77
    }
    """
    Then the response status code should be 200
    And the JSON node "equipmentRecord.hourMeter" should be equal to 6842

  Scenario: As a basic user, I can create a task link to a TOC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/tasks" with body:
    """
    {
        "shortDescription": "TOC Task",
        "description": "TOC task description",
        "module": "/modules/44",
        "referenceId": 1,
        "escalationTrigger": 60,
        "escalationTriggerUnit": "DAYS",
        "confidential": false,
        "startedAt": "2026-02-02T15:54:50+01:00",
        "dueDate": "2099-02-08 04:05:06",
        "indiceFactor": "IF 1",
        "assignee": "/people/64",
        "technician": "/people/40",
        "recipients": [
            "/people/64"
        ]
    }
    """
    Then the response status code should be 201
    And the JSON node "shortDescription" should be equal to the string "TOC Task"
    And the JSON node "module.name" should be equal to the string "TOC"
    And the JSON node "referenceId" should be equal to the number 1

  Scenario: As a extranet user, when I create a TOC, should be IF 10
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_calls" with body:
    """
    {
        "equipmentRecord": "\/equipment_records\/1",
        "originalTitle": "sdfgsdfgsdfgsdfg",
        "originalDescription": "sdfgsdfgsdfgsdfg",
        "serviceActivity": "\/service\/service_activities\/1",
        "unitOperationalStatus": "\/unit_operational_statuses\/MCF",
        "mainContact": "\/sales\/extranet_users\/200",
        "technicianOnCallType": "\/service\/technician_on_call_types\/1",
        "hourMeter": 10,
        "airport": "\/airports\/62",
        "errorCodes": "123"
    }
    """
    Then the response status code should be 201
    And the JSON node "indiceFactor" should be equal to the string "IF 10"

  Scenario: As an extranet user without the role_TOC ACL, I cannot create a TOC
    Given I authenticate as the extranet user "user-campaign@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_calls" with body:
    """
    {
        "equipmentRecord": "\/equipment_records\/1",
        "originalTitle": "sdfg",
        "originalDescription": "dfg",
        "serviceActivity": "\/service\/service_activities\/1",
        "unitOperationalStatus": "\/unit_operational_statuses\/MCF",
        "mainContact": "\/sales\/extranet_users\/200",
        "technicianOnCallType": "\/service\/technician_on_call_types\/1",
        "hourMeter": 10,
        "airport": "\/airports\/62",
        "errorCodes": "123"
    }
    """
    Then the response status code should be 403

  Scenario: As a extranet user, when I create a TOC for info request, should be IF 1
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_calls" with body:
    """
    {
        "equipmentRecord": "\/equipment_records\/1",
        "originalTitle": "sdfgsdfgsdfg",
        "originalDescription": "sdfgsdfgsdfg",
        "serviceActivity": "\/service\/service_activities\/7",
        "unitOperationalStatus": "\/unit_operational_statuses\/MCF",
        "mainContact": "\/sales\/extranet_users\/200",
        "technicianOnCallType": "\/service\/technician_on_call_types\/1",
        "hourMeter": 10,
        "airport": "\/airports\/62",
        "errorCodes": "123"
    }
    """
    Then the response status code should be 201
    And the JSON node "indiceFactor" should be equal to the string "IF 1"

  Scenario: As a extranet user, when I create a TOC non mission capable, should be IF 100
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_calls" with body:
    """
    {
        "equipmentRecord": "\/equipment_records\/1",
        "originalTitle": "sdfgsdfgsdfg",
        "originalDescription": "sdfgsdfgsdfg",
        "serviceActivity": "\/service\/service_activities\/7",
        "unitOperationalStatus": "\/unit_operational_statuses\/NMC",
        "mainContact": "\/sales\/extranet_users\/200",
        "technicianOnCallType": "\/service\/technician_on_call_types\/1",
        "hourMeter": 10,
        "airport": "\/airports\/62",
        "errorCodes": "123"
    }
    """
    Then the response status code should be 201
    And the JSON node "indiceFactor" should be equal to the string "IF 100"

  @resetFileTable
  Scenario: As a basic user, I cannot upload a main file with not allowed type to a TOC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/service/technician_on_calls/1/main_file" with file "file" "file.doc"
    Then the response status code should be 422

  Scenario: As a basic user, I can upload a main file to a TOC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/service/technician_on_calls/1/main_file" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: Download a TOC attached main file as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls/1/main_file/1"
    Then the response status code should be 200

  Scenario: Change main file of TOC with permission OK
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/service/technician_on_calls/1/main_file" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: As a basic user, I can delete a main file from a TOC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/service/technician_on_calls/1/main_file/2"
    Then the response status code should be 204

  Scenario: As a basic user, I can upload a file to a TOC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/service/technician_on_calls/1/files" with file "file" "file.doc"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: Download a TOC attached file as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls/1/files/3"
    Then the response status code should be 200

  Scenario: Change file of TOC with permission OK
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/service/technician_on_calls/1/files" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: As a basic user, I can delete a file from a TOC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/service/technician_on_calls/1/files/4"
    Then the response status code should be 204

  Scenario: As basic User, I cannot duplicate a  TOC with missing data
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_calls/4/duplicate" with body:
    """
{
    "inputLines": [
        {
            "equipmentRecord": "/equipment_records/21",
            "salesOrganisationService": "/locations/30",
            "hourMeter": null
        },
        {
            "equipmentRecord": "/equipment_records/22",
            "airport": "/airports/101",
            "hourMeter": null
        }
    ]
}
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "[0].airport"
    And the JSON node "violations[0].message" should be equal to "This value should not be null."
    And the JSON node "violations[1].propertyPath" should be equal to "[1].salesOrganisationService"
    And the JSON node "violations[1].message" should be equal to "This value should not be null."


  Scenario: As basic User, I can duplicate a TOC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_calls/4/duplicate" with body:
    """
{
    "inputLines": [
        {
            "equipmentRecord": "/equipment_records/18",
            "airport": "/airports/100",
            "salesOrganisationService": "/locations/30",
            "hourMeter": 1024
        },
        {
            "equipmentRecord": "/equipment_records/23",
            "airport": "/airports/103",
            "salesOrganisationService": "/locations/30",
            "hourMeter": 800
        },
        {
            "equipmentRecord": "/equipment_records/26",
            "airport": "/airports/112",
            "salesOrganisationService": "/locations/30",
            "hourMeter": 600
        }
    ]
}
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_calls_batch_output.json"

  Scenario: As basic User, I can duplicate a TOC but not create CSR
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_calls/4/duplicate" with body:
    """
{
    "inputLines": [
        {
            "equipmentRecord": "/equipment_records/18",
            "airport": "/airports/100",
            "salesOrganisationService": "/locations/30",
            "hourMeter": 1024,
            "nestedCustomerServiceRecord": {
                "leader": null,
                "plannedAt": null
            }
        },
        {
            "equipmentRecord": "/equipment_records/26",
            "airport": "/airports/101",
            "salesOrganisationService": "/locations/30",
            "hourMeter": 768
        }
    ]
}
    """
    Then the response status code should be 206
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_calls_batch_output.json"
    And the JSON node "outputLines[0].customerServiceRecordErrorMessage" should be equal to the string "Access Denied"

  Scenario: As CSM User, I can duplicate a TOC but and create CSR on one of the duplicated TOCs
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_calls/4/duplicate" with body:
    """
{
    "inputLines": [
        {
            "equipmentRecord": "/equipment_records/18",
            "airport": "/airports/100",
            "salesOrganisationService": "/locations/30",
            "hourMeter": 1024,
            "nestedCustomerServiceRecord": {
                "leader": null,
                "plannedAt": null
            }
        },
        {
            "equipmentRecord": "/equipment_records/26",
            "airport": "/airports/101",
            "salesOrganisationService": "/locations/30",
            "hourMeter": 768
        }
    ]
}
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_calls_batch_output.json"
    And the JSON node "outputLines[0].customerServiceRecordId" should not be null
    And the JSON node "outputLines[0].customerServiceRecordErrorMessage" should be null

  Scenario: As CSM User, I can duplicate a TOC and create CSR on all of the duplicated TOCs
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_calls/4/duplicate" with body:
    """
{
    "inputLines": [
        {
            "equipmentRecord": "/equipment_records/23",
            "airport": "/airports/100",
            "salesOrganisationService": "/locations/30",
            "hourMeter": 1024,
            "nestedCustomerServiceRecord": {
                "leader": null,
                "plannedAt": null
            }
        },
        {
            "equipmentRecord": "/equipment_records/26",
            "airport": "/airports/101",
            "salesOrganisationService": "/locations/30",
            "hourMeter": 768,
            "nestedCustomerServiceRecord": {
                "leader": null,
                "plannedAt": null
            }
        }
    ]
}
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_calls_batch_output.json"
    And the JSON node "outputLines[0].customerServiceRecordId" should not be null
    And the JSON node "outputLines[0].customerServiceRecordErrorMessage" should be null
    And the JSON node "outputLines[1].customerServiceRecordId" should not be null
    And the JSON node "outputLines[1].customerServiceRecordErrorMessage" should be null

  Scenario: As CSM User, I can duplicate a TOC and create CSR on all of the duplicated TOCs but One csr fails
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_calls/4/duplicate" with body:
    """
{
    "inputLines": [
        {
            "equipmentRecord": "/equipment_records/18",
            "airport": "/airports/100",
            "salesOrganisationService": "/locations/30",
            "hourMeter": 1024,
            "nestedCustomerServiceRecord": {
                "leader": "/people/13",
                "plannedAt": "2026-12-07"
            }
        },
        {
            "equipmentRecord": "/equipment_records/23",
            "airport": "/airports/101",
            "salesOrganisationService": "/locations/30",
            "hourMeter": 768,
            "nestedCustomerServiceRecord": {
                "leader": "/people/109",
                "plannedAt": "2026-12-07"
            }
        }
    ]
}
    """
    Then the response status code should be 206
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_calls_batch_output.json"
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_calls_batch_output.json"
    And the JSON node "outputLines[0].serialNumber" should be equal to "Green tag"
    And the JSON node "outputLines[0].technicianOnCallId" should not be null
    And the JSON node "outputLines[0].technicianOnCallErrorMessage" should be null
    And the JSON node "outputLines[0].customerServiceRecordId" should be null
    And the JSON node "outputLines[0].customerServiceRecordErrorMessage" should be equal to "interventions[0].leader: An AST, a CSTL, a CSM or a CSS position is mandatory to be leader on the intervention"
    And the JSON node "outputLines[1].serialNumber" should be equal to "GT2023"
    And the JSON node "outputLines[1].technicianOnCallId" should not be null
    And the JSON node "outputLines[1].technicianOnCallErrorMessage" should be null
    And the JSON node "outputLines[1].customerServiceRecordId" should not be null
    And the JSON node "outputLines[1].customerServiceRecordErrorMessage" should be null

    Scenario: As CSM User, requesting a CSR on a PENDING TOC changes its status to IN_PROGRESS
      Given I authenticate as the intranet user "user-csm@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "PUT" request to "/service/technician_on_calls/28/request-technician" with body:
      """
      {
        "nestedCustomerServiceRecord": {
          "leader": "/people/109",
          "plannedAt": "2026-12-07"
        }
      }
      """
      Then the response status code should be 200
      And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_call_customer_service_record.json"
      And the JSON node "@sub_resources.customerServiceRecord.@context" should be equal to the string "/contexts/TechnicianOnCallCustomerServiceRecord"
      And the JSON node "@sub_resources.customerServiceRecord.technicianOnCall.@id" should be equal to the string "/service/technician_on_calls/28"
      And the JSON node "@sub_resources.customerServiceRecord.interventions[0].plannedAt" should contain "2026-12-07"
      And the JSON node "@sub_resources.customerServiceRecord.interventions[0].leader.@id" should be equal to the string "/people/109"
      And the JSON node "status" should be equal to the string "IN_PROGRESS"

  Scenario: As CSM User, editing a PENDING TOC changes its status to IN_PROGRESS
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/29" with body:
      """
      {
        "originalDescription": "The description is changed in a PUT request by a People User, so the status should now be IN_PROGRESS"
      }
      """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_call.json"
    And the JSON node "originalDescription" should be equal to the string "The description is changed in a PUT request by a People User, so the status should now be IN_PROGRESS"
    And the JSON node "status" should be equal to the string "IN_PROGRESS"

  Scenario: As CSM User, requesting a CSR on a SUSPENDED TOC changes its status to IN_PROGRESS
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/30/request-technician" with body:
      """
      {
        "nestedCustomerServiceRecord": {
          "leader": "/people/109",
          "plannedAt": "2026-12-07"
        }
      }
      """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_call_customer_service_record.json"
    And the JSON node "@sub_resources.customerServiceRecord.@context" should be equal to the string "/contexts/TechnicianOnCallCustomerServiceRecord"
    And the JSON node "@sub_resources.customerServiceRecord.technicianOnCall.@id" should be equal to the string "/service/technician_on_calls/30"
    And the JSON node "status" should be equal to the string "IN_PROGRESS"

  Scenario: As CSM User, editing a PENDING TOC changes its status to IN_PROGRESS
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/31" with body:
      """
      {
        "originalDescription": "The description is changed in a PUT request by a People User, so the status should now be IN_PROGRESS and not SUSPENDED Anymore"
      }
      """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_call.json"
    And the JSON node "originalDescription" should be equal to the string "The description is changed in a PUT request by a People User, so the status should now be IN_PROGRESS and not SUSPENDED Anymore"
    And the JSON node "description" should be equal to the string "The description is changed in a PUT request by a People User, so the status should now be IN_PROGRESS and not SUSPENDED Anymore"
    And the JSON node "status" should be equal to the string "IN_PROGRESS"

  Scenario: As an extranet user, I should be allow to create a TOC and it should update airport of equipment record
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    Then I send a "POST" request to "/service/technician_on_calls" with body:
    """
    {
      "originalTitle": "TOC created by extranet user",
      "originalDescription": "This is really a big problem, please fix quickly",
      "equipmentRecord": "/equipment_records/7",
      "customer": "/sales/customers/1",
      "assignee": "/people/65",
      "technician": "/people/40",
      "errorCodes": "404",
      "unitOperationalStatus": "/unit_operational_statuses/MCF",
      "technicianOnCallType": "/service/technician_on_call_types/1",
      "serviceActivity": "/service/service_activities/1",
      "mainContact": "sales/extranet_users/200",
      "indiceFactor": "IF 1",
      "airport": "/airports/65"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_call.json"
    And the JSON node "title" should be equal to the string "TOC created by extranet user"
    And the JSON node "originalTitle" should be equal to the string "TOC created by extranet user"
    And the JSON node "originalDescription" should be equal to the string "This is really a big problem, please fix quickly"
    And the JSON node "description" should be equal to the string "This is really a big problem, please fix quickly"
    And the JSON node "equipmentRecord.@id" should be equal to the string "/equipment_records/7"
    And the JSON node "assignee.@id" should be equal to the string "/people/65"
    And the JSON node "errorCodes" should be equal to "404"
    And the JSON node "unitOperationalStatus.@id" should be equal to the string "/unit_operational_statuses/MCF"
    And the JSON node "technicianOnCallType.@id" should be equal to the string "/service/technician_on_call_types/1"
    And the JSON node "serviceActivity.@id" should be equal to the string "/service/service_activities/1"
    And the JSON node "mainContact.@id" should be equal to the string "/sales/extranet_users/200"
    And the JSON node "indiceFactor" should be equal to the string "IF 1"
    And the JSON node "salesOrganisationService.@id" should be equal to the string "/locations/23"
    And the JSON node "airport.@id" should be equal to the string "/airports/65"
    And the JSON node "customer.@id" should be equal to the string "/sales/customers/1"
    And an email should have been sent asynchronously with subject matching pattern "/TOC#\d+, PENDING, open by AIR DE RIEN, for Produit Test, SN# SN_007/"
    And this asynchronous email should be sent only to "julien.lepers@tld.com, service-hub@location-sso.com"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records/7"
    Then the response status code should be 200
    And the JSON node "airport.@id" should be equal to "/airports/65"

  Scenario: As an extranet user, I should be allow to comment a TOC I can see
    Given I authenticate as the extranet user "contract-buyer-extra@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    Then I send a "POST" request to "/comments" with parameters:
      | key           | value                          |
      | resource      | /service/technician_on_calls/1 |
      | message       | "hello Kader, Kader Algerie"   |
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    Then I send a "POST" request to "/comments" with parameters:
      | key           | value                            |
      | resource      | /service/technician_on_calls/7   |
      | message       | "hello Kader, Kader Algerie"     |
    Then the response status code should be 201
    And an email should have been sent asynchronously with subject matching pattern "/TOC#7, day 1, location_sso, customer_for_fur, Produit Test - New Log Entry by customer_for_fur - IF 100 -/"
    And this asynchronous email should be sent only to "user-ceo@tld.fr, user-service@tld.fr, user-ast-bu2@tld.fr, user-csm@tld.fr, user-pse@tld.fr, user-em@tld.fr, user-psm@tld.fr, user-evp@tld.fr"

  Scenario: As an extranet user without the role_TOC ACL, I cannot comment a TOC even with equipment access
    Given I authenticate as the extranet user "contract-enduser@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    Then I send a "POST" request to "/comments" with parameters:
      | key           | value                            |
      | resource      | /service/technician_on_calls/7   |
      | message       | "no role_TOC, no comment"        |
    Then the response status code should be 403

  Scenario: As an evendor user, I should not be able to create a TOC
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    Then I send a "POST" request to "/service/technician_on_calls" with body:
    """
    {
      "originalTitle": "TOC created by extranet user",
      "originalDescription": "This is really a big problem, please fix quickly",
      "equipmentRecord": "/equipment_records/1",
      "assignee": "/people/65",
      "technician": "/people/40",
      "errorCodes": "404",
      "unitOperationalStatus": "/unit_operational_statuses/MCF",
      "technicianOnCallType": "/service/technician_on_call_types/1",
      "serviceActivity": "/service/service_activities/1",
      "mainContact": "sales/extranet_users/200",
      "indiceFactor": "IF 1",
      "airport": "/airports/61",
      "salesOrganisationService": "/locations/23",
      "customer": "/sales/customers/1"
    }
    """
    Then the response status code should be 403

  Scenario: As basic user, creating a TOC with IF 1000 should post a comment to open FactoryFlag
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    Then I send a "POST" request to "/service/technician_on_calls" with body:
    """
    {
      "originalTitle": "TOC created With IF 1000",
      "originalDescription": "This TOC should generate a sub-request to post a comment with the good metadata for FactoryFlag opening event if the user has not the FEATURE_TECHNICIAN_ON_CALL_OPEN_FACTORY_FLAG",
      "equipmentRecord": "/equipment_records/1",
      "assignee": "/people/65",
      "technician": "/people/40",
      "errorCodes": "418",
      "unitOperationalStatus": "/unit_operational_statuses/NMC",
      "technicianOnCallType": "/service/technician_on_call_types/2",
      "serviceActivity": "/service/service_activities/1",
      "mainContact": "sales/extranet_users/200",
      "indiceFactor": "IF 1000",
      "airport": "/airports/61",
      "salesOrganisationService": "/locations/23",
      "customer": "/sales/customers/1"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_call_customer_service_record.json"
    And the JSON node "@sub_resources.comment.message" should be equal to the string "<strong>Factory Flag automatically opened because the TOC has been set to IF 1000.</strong>"
    And the JSON node "factoryFlag" should be true

  Scenario: As service user, updating a TOC with IF 1000 should post a comment to open FactoryFlag
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    Then I send a "PUT" request to "/service/technician_on_calls/44" with body:
    """
    {
      "indiceFactor": "IF 1000"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_call_customer_service_record.json"
    And the JSON node "@sub_resources.comment.message" should be equal to the string "<strong>Factory Flag automatically opened because the TOC has been set to IF 1000.</strong>"
    And the JSON node "factoryFlag" should be true

  Scenario: Creating a TOC for ER not shipped should not be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_calls" with body:
    """
    {
      "originalTitle": "TOC created by POST",
      "originalDescription": "This is really a big problem, please fix quickly",
      "equipmentRecord": "/equipment_records/24",
      "assignee": "/people/65",
      "technician": "/people/40",
      "errorCodes": "404",
      "unitOperationalStatus": "/unit_operational_statuses/MCF",
      "technicianOnCallType": "/service/technician_on_call_types/1",
      "serviceActivity": "/service/service_activities/1",
      "tags": [
        "/technician_on_call_tags/1",
        "/technician_on_call_tags/2",
        "/technician_on_call_tags/3"
      ],
      "mainContact": "sales/extranet_users/200",
      "contacts": [
        "sales/extranet_users/203"
      ],
      "indiceFactor": "IF 1",
      "airport": "/airports/61",
      "salesOrganisationService": "/locations/38",
      "customer": "/sales/customers/1"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to the string "equipmentRecord"
    And the JSON node "violations[0].message" should be equal to the string "Cannot create a TOC on an Equipment Record that has not been shipped yet"

  Scenario: Creating a TOC for Retired ER should not be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_calls" with body:
    """
    {
      "originalTitle": "TOC created by POST",
      "originalDescription": "This is really a big problem, please fix quickly",
      "equipmentRecord": "/equipment_records/29",
      "assignee": "/people/65",
      "technician": "/people/40",
      "errorCodes": "404",
      "unitOperationalStatus": "/unit_operational_statuses/MCF",
      "technicianOnCallType": "/service/technician_on_call_types/1",
      "serviceActivity": "/service/service_activities/1",
      "tags": [
        "/technician_on_call_tags/1",
        "/technician_on_call_tags/2",
        "/technician_on_call_tags/3"
      ],
      "mainContact": "sales/extranet_users/200",
      "contacts": [
        "sales/extranet_users/203"
      ],
      "indiceFactor": "IF 1",
      "airport": "/airports/61",
      "salesOrganisationService": "/locations/38",
      "customer": "/sales/customers/1"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to the string "equipmentRecord"
    And the JSON node "violations[0].message" should be equal to the string "Cannot create a TOC on a retired Equipment Record"

  Scenario: As a basic user, I should not be able to create a TOC with Commissioning activity if confidential is false
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_calls" with body:
    """
    {
      "originalTitle": "TOC Commissioning not confidential",
      "originalDescription": "This TOC is a commissioning but not confidential",
      "equipmentRecord": "/equipment_records/1",
      "assignee": "/people/65",
      "technicianOnCallType": "/service/technician_on_call_types/1",
      "unitOperationalStatus": "/unit_operational_statuses/MCF",
      "serviceActivity": "/service/service_activities/2",
      "indiceFactor": "IF 10",
      "airport": "/airports/61",
      "salesOrganisationService": "/locations/23",
      "customer": "/sales/customers/1",
      "confidential": false
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "confidential"
    And the JSON node "violations[0].message" should contain "A TOC with Commissioning activity must be confidential."

  Scenario: As a basic user, I should be able to create a TOC with Commissioning activity if confidential is true
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_calls" with body:
    """
    {
      "originalTitle": "TOC Commissioning confidential",
      "originalDescription": "This TOC is a commissioning and confidential",
      "equipmentRecord": "/equipment_records/1",
      "assignee": "/people/65",
      "technicianOnCallType": "/service/technician_on_call_types/1",
      "unitOperationalStatus": "/unit_operational_statuses/MCF",
      "serviceActivity": "/service/service_activities/2",
      "indiceFactor": "IF 10",
      "airport": "/airports/61",
      "salesOrganisationService": "/locations/23",
      "customer": "/sales/customers/1",
      "confidential": true
    }
    """
    Then the response status code should be 201
    And the JSON node "confidential" should be true

  Scenario: As a CSM user, I should not be able to edit a TOC to Commissioning activity if confidential is false
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/1" with body:
    """
    {
      "serviceActivity": "/service/service_activities/2",
      "confidential": false
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "confidential"
    And the JSON node "violations[0].message" should contain "A TOC with Commissioning activity must be confidential."

  Scenario: Commissioning TOCs are hidden from extranet users
    # Visible to intranet users
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls/70"
    Then the response status code should be 200
    # Item is hidden from an extranet user who otherwise has access to the equipment record
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls/70"
    Then the response status code should be 404
    # Collection filtered on the commissioning activity returns nothing for the extranet user
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_calls?serviceActivity=2"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 0 element

  Scenario: As a service user, setting thirdPartyRef without thirdPartyName should fail validation
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/1" with body:
    """
    {
      "thirdPartyRef": "47B1ME"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "thirdPartyName"
    And the JSON node "violations[0].message" should be equal to the string "Third party name is required when a third party reference is provided"

  Scenario: As a service user, setting thirdPartyRef with thirdPartyName should succeed
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_calls/1" with body:
    """
    {
      "thirdPartyRef": "47B1ME",
      "thirdPartyName": "Wayne Enterprises"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_call.json"
    And the JSON node "thirdPartyRef" should be equal to the string "47B1ME"
    And the JSON node "thirdPartyName" should be equal to the string "Wayne Enterprises"
