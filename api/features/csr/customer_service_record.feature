Feature: Test CSR Entity
#  Care, a CSR Commissioning is creating through ODP and played before CSR (id 3)

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord" is exposed on the API
    Then the filter "order[id]" should be available and its type should be "string"
    Then the filter "order[createdAt]" should be available and its type should be "string"
    Then the filter "order[updatedAt]" should be available and its type should be "string"
    Then the filter "order[airport.code]" should be available and its type should be "string"
    Then the filter "order[interventions.leader.lastname]" should be available and its type should be "string"
    Then the filter "id" should be available and its type should be "int"
    Then the filter "createdBy" should be available and its type should be "string"
    Then the filter "legacyId" should be available and its type should be "int"
    Then the filter "equipmentRecord.manufacturerLocation" should be available and its type should be "string"
    Then the filter "status" should be available and its type should be "string"
    Then the filter "equipmentRecord" should be available and its type should be "string"
    Then the filter "airport" should be available and its type should be "string"
    Then the filter "airport.country" should be available and its type should be "string"
    Then the filter "interventions.leader" should be available and its type should be "string"
    Then the filter "interventions.operators" should be available and its type should be "string"
    Then the filter "interventions.status" should be available and its type should be "string"
    Then the filter "equipmentRecord.salesOrganisation" should be available and its type should be "string"
    Then the filter "equipmentRecord.salesOrganisationService" should be available and its type should be "string"
    Then the filter "equipmentRecord.salesOrganisationService.legacyId" should be available and its type should be "int"
    Then the filter "equipmentRecord.endUser" should be available and its type should be "string"
    Then the filter "equipmentRecord.product" should be available and its type should be "string"
    Then the filter "equipmentRecord.deliveredCountry" should be available and its type should be "string"
    Then the filter "equipmentRecord.product.family.productType" should be available and its type should be "string"
    Then the filter "createdAt[after]" should be available and its type should be "DateTimeInterface"
    Then the filter "createdAt[before]" should be available and its type should be "DateTimeInterface"
    Then the filter "completedAt[after]" should be available and its type should be "DateTimeInterface"
    Then the filter "completedAt[before]" should be available and its type should be "DateTimeInterface"
    Then the filter "closedAt[after]" should be available and its type should be "DateTimeInterface"
    Then the filter "closedAt[before]" should be available and its type should be "DateTimeInterface"
    Then the filter "technicianPlanned" should be available and its type should be "array"
    Then the filter "discriminator[]" should be available and its type should be "string"
    And the filter "normalization_groups_override[]" should be available and its type should be "string"
    And the filter "columns" should be available and its type should be "string"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Service\CustomerServiceRecord\ServiceBulletinCustomerServiceRecord" is exposed on the API
    Then the filter "serviceBulletinLegacyId" should be available and its type should be "int"

  Scenario: As a service user, I should access to the report of CSR year backlog
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/service/customer_service_records;x=year;y=backlog"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "@type" should be equal to the string "Report"
    And the JSON node "x" should be equal to the string "year"
    And the JSON node "y" should be equal to the string "backlog"
    And the JSON node "total" should be equal to the number 15
    And the JSON node "yTotals.toc" should be equal to the number 6

  Scenario: CSRs can be filtered
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "service/customer_service_records?normalization_groups_override[]=customer_service_record_light"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/csr/schemas/customer_service_records_light.json"

  Scenario: Request all CSRs
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/customer_service_records"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/csr/schemas/customer_service_records.json"
    And the JSON node "hydra:totalItems" should be equal to 21

  Scenario: Request all CSRs for a user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/customer_service_records?technicianPlanned[0]=12"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/csr/schemas/customer_service_records.json"
    And the JSON node "hydra:totalItems" should be equal to 1

  Scenario: Request a single CSR
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/customer_service_records/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/csr/schemas/customer_service_record.json"

  Scenario: Request a non existing CSR
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/customer_service_records/not_found"
    Then the response status code should be 404

  Scenario: Request a single CSR by an XU is not permitted
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/customer_service_records/1"
    Then the response status code should be 403

  Scenario: As a user, I can't create a CSR without required data
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/default_customer_service_records" with body:
    """
    {
    }
    """
    Then the response status code should be 405

  Scenario: As a basic user, I can't create a CSR
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/commissioning_customer_service_records" with body:
    """
    {
      "equipmentRecord": "/equipment_records/15",
      "airport": "/airports/62",
      "description": "Voici Gali l'alligator, il arrive et sème la mort, éventre les oisillons et torture les papillons"
    }
    """
    Then the response status code should be 403

  Scenario: As a CSM user, I can create a CSR
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/commissioning_customer_service_records" with body:
    """
    {
      "equipmentRecord": "/equipment_records/15",
      "airport": "/airports/62",
      "description": "Voici Gali l'alligator, il arrive et sème la mort, éventre les oisillons et torture les papillons"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/csr/schemas/commissioning_customer_service_record.json"
    And the JSON node "createdBy.@id" should be equal to the string "/people/64"
    And the JSON node "createdAt" should be newer than 1 minute ago
    And the JSON node "equipmentRecord.@id" should be equal to the string "/equipment_records/15"
    And the JSON node "deletedAt" should be null
    And the JSON node "airport.@id" should be equal to the string "/airports/62"
    And the JSON node "status" should be equal to the string "PENDING"
    And the JSON node "description" should be equal to the string "Voici Gali l'alligator, il arrive et sème la mort, éventre les oisillons et torture les papillons"

  Scenario: As a CSM user, I can't planned an intervention with a bad leader
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/commissioning_customer_service_records/21" with body:
    """
    {
      "plannedAt": "2025-03-28T09:14:53.170Z",
      "leader": "/people/11",
      "customerServiceRecord": "/service/default_customer_service_records/21",
      "customerServiceRecordId": "9"
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "interventions[0].leader: An AST, a CSTL, a CSM or a CSS position is mandatory to be leader on the intervention"

  Scenario: Get CSR's available status
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/service/customer_service_records/22?normalizationGroups[]=workflow"
    Then the response status code should be 200
    And the JSON node "availableStatus" should exist
    And the JSON node "availableStatus" should have 2 elements
    And the JSON node "availableStatus[0]" should be equal to the string "PLANNED"
    And the JSON node "availableStatus[1]" should be equal to the string "ASSIGNED"

  Scenario: As a basic user, i can't update a CSR
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/customer_service_records/22" with body:
    """
    {
      "description": "j'ai pas la ref"
    }
    """
    Then the response status code should be 403

  Scenario: As a CSM, I can update a CSR
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/customer_service_records/22" with body:
    """
    {
      "description": "j'ai pas la ref",
      "airport": "/airports/112"
    }
    """
    Then the response status code should be 200
    And the JSON node "description" should be equal to "j'ai pas la ref"
    And the JSON node "equipmentRecord.airport.@id" should be equal to the string "/airports/112"

  Scenario: Update a CSR with bad status
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/customer_service_records/22" with body:
    """
    {
      "status": "COMPLETED"
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should contain "Status COMPLETED is not allowed"

  Scenario: Update a CSR to PLANNED without planned at is not allowed status
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/customer_service_records/22" with body:
    """
    {
      "status": "PLANNED"
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should contain "plannedAt: This value should not be blank"

  Scenario: Update a CSR to PLANNED without planned at is not allowed status
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/customer_service_records/22" with body:
    """
    {
      "plannedAt": "2024-07-01 20:00:00",
      "status": "PLANNED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to "PLANNED"

  Scenario: Update a CSR status in Assigned without leader
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/customer_service_records/22" with body:
    """
    {
      "status": "ASSIGNED"
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "status: A leader is mandatory to switch status to Assigned"

  Scenario: As a basic user, I can't close any CSR through the batch
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/customer_service_records/multiple_closure" with body:
    """
    {
      "customerServiceRecords" : ["/service/commissioning_customer_service_records/5", "/service/commissioning_customer_service_records/6"]
    }
    """
    Then the response status code should be 403

  Scenario: As a CSM, I can't close any CSR through the batch if their status isn't COMPLETED
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/customer_service_records/multiple_closure" with body:
    """
    {
      "customerServiceRecords" : ["/service/commissioning_customer_service_records/21", "/service/commissioning_customer_service_records/6"]
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to "Status CLOSED is not allowed."

  Scenario: As a csm, I can close any CSR through the batch if their status is COMPLETED
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/customer_service_records/multiple_closure" with body:
    """
    {
      "customerServiceRecords" : ["/service/commissioning_customer_service_records/6"]
    }
    """
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/service/customer_service_records/6"
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "CLOSED"

  Scenario: As a basic user, I can't delete a CSR
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/service/customer_service_records/22"
    Then the response status code should be 403

  Scenario: As a CSM I can delete a CSR
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/service/customer_service_records/22"
    Then the response status code should be 204

  Scenario: As a CSM user, I can create a SB CSR
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/service_bulletin_customer_service_records" with body:
    """
    {
      "equipmentRecord": "/equipment_records/15",
      "airport": "/airports/62",
      "description": "Je créé un CSR SB",
      "serviceBulletinLegacyId": 58
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/csr/schemas/service_bulletin_customer_service_record.json"
    And the JSON node "createdBy.@id" should be equal to the string "/people/64"
    And the JSON node "createdAt" should be newer than 1 minute ago
    And the JSON node "equipmentRecord.@id" should be equal to the string "/equipment_records/15"
    And the JSON node "deletedAt" should be null
    And the JSON node "airport.@id" should be equal to the string "/airports/62"
    And the JSON node "status" should be equal to the string "PENDING"
    And the JSON node "description" should be equal to the string "Je créé un CSR SB"
    And the JSON node "serviceBulletinLegacyId" should be equal to the string "58"

  Scenario: As a CSM user, I cannot create a SB CSR with ID not existing on legacy
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/service_bulletin_customer_service_records" with body:
    """
    {
      "equipmentRecord": "/equipment_records/15",
      "airport": "/airports/62",
      "description": "Je créé un CSR SB",
      "serviceBulletinLegacyId": 0
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "serviceBulletinLinesLegacyId"
    And the JSON node "violations[0].message" should contain "SB Line could not be found for this ER on this SB"
    And the JSON node "violations[1].propertyPath" should be equal to "serviceBulletinLegacyId"
    And the JSON node "violations[1].message" should contain "SB does not exist"

  Scenario: As a CSM user, I can create a TOC CSR
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_call_customer_service_records" with body:
    """
    {
      "equipmentRecord": "/equipment_records/15",
      "airport": "/airports/62",
      "description": "Je créé un CSR TOC",
      "technicianOnCall": "/service/technician_on_calls/1"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/csr/schemas/technician_on_call_customer_service_record.json"
    And the JSON node "createdBy.@id" should be equal to the string "/people/64"
    And the JSON node "createdAt" should be newer than 1 minute ago
    And the JSON node "equipmentRecord.@id" should be equal to the string "/equipment_records/15"
    And the JSON node "deletedAt" should be null
    And the JSON node "airport.@id" should be equal to the string "/airports/62"
    And the JSON node "status" should be equal to the string "PENDING"
    And the JSON node "description" should be equal to the string "Je créé un CSR TOC"

  Scenario: As a CSM user, I cannot create a TOC CSR if the TOC is already linked to a CSR
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_call_customer_service_records" with body:
    """
    {
      "equipmentRecord": "/equipment_records/15",
      "airport": "/airports/62",
      "description": "Je créé un CSR TOC",
      "technicianOnCall": "/service/technician_on_calls/1"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "technicianOnCall"
    And the JSON node "violations[0].message" should contain "TOC selected is already linked to a CSR"

  Scenario: CSR filtered can be downloaded as an Excel file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/service/customer_service_records?columns=id,status,createdAt,completedAt,createdBy,plannedAt,title, description,legacyModuleName,legacyModuleId,interventionLeader,airport.code,equipmentRecord.serialNumber,equipmentRecord.model,equipmentRecord.manufacturerLocation.name,equipmentRecord.salesOrganisation.name,equipmentRecord.buyer.name,equipmentRecord.endUser.name,equipmentRecord.customerSerialNumber,interventionStatus,interventionPlannedAt,type"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Status | Created At | Completed At | Created By | Planned At | Title | Description |  Csr Type | Module Legacy Id | Intervention Leader | Airport | Serial Number | Model | Factory | Sso | Buyer | End User | Customer Asset | Intervention Status | Intervention Planned At | Type |

  Scenario: Imperfect commissioning send an intranet notification to user
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/commissioning_customer_service_records/5" with body:
    """
    {
        "@id": "/service/commissioning_customer_service_records/5",
        "answerSurveyCustomerServiceRecords": [
            {
                "questionSurveyCustomerServiceRecord": "/service/question_survey_customer_service_records/1",
                "answer": "5",
                "comment": "It's perfect"
            },
            {
                "questionSurveyCustomerServiceRecord": "/service/question_survey_customer_service_records/2",
                "answer": "0",
                "comment": "William work on it"
            },
            {
                "questionSurveyCustomerServiceRecord": "/service/question_survey_customer_service_records/3",
                "answer": "5",
                "comment": "Perfect"
            }
        ],
        "type": "commissioning"
    }
    """
    And an email should have been sent asynchronously with subject matching pattern "~CSR#5 SURVEY Completed : 5/0/5 - ER#polo TXL-737 \*\*DEMO\*\* commissioned~"
    And this asynchronous email should be sent to "user-qam@tld.fr"
    And this asynchronous email should be sent to "user-ast@tld.fr"
    And this asynchronous email should be sent as cc to "user-superuser@tld.fr"

  Scenario: Perfect commissioning not send an intranet notification to user
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/commissioning_customer_service_records/5" with body:
    """
    {
        "@id": "/service/commissioning_customer_service_records/5",
        "answerSurveyCustomerServiceRecords": [
            {
                "questionSurveyCustomerServiceRecord": "/service/question_survey_customer_service_records/1",
                "answer": "5",
                "comment": "It's perfect"
            },
            {
                "questionSurveyCustomerServiceRecord": "/service/question_survey_customer_service_records/2",
                "answer": "5",
                "comment": "I fixed it"
            },
            {
                "questionSurveyCustomerServiceRecord": "/service/question_survey_customer_service_records/3",
                "answer": "5",
                "comment": "Perfect"
            }
        ],
        "type": "commissioning"
    }
    """
    Then no email should have been sent asynchronously with subject matching pattern "~CSR#5 Completed : 5/0/5 - ER#polo TXL-737 commissioned~"

  Scenario: A comment on a CSR with a filled imperfect survey notifies the survey recipients
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
        "resource": "/service/commissioning_customer_service_records/6",
        "message": "The customer is still unhappy"
    }
    """
    Then the response status code should be 201
    And an email should have been sent asynchronously with subject matching pattern "~CSR#6 - New comment on imperfect survey \(4/4/4\) - ER#polo TXL-737 \*\*DEMO\*\* commissioned~"
    And this asynchronous email should be sent from "user-csm@tld.fr"
    And this asynchronous email should be sent to "user-qam@tld.fr"
    And this asynchronous email should be sent to "user-psm@tld.fr"
    And this asynchronous email should be sent as cc to "user-superuser@tld.fr"
    And this asynchronous email should contain "The customer is still unhappy"

  Scenario: A comment on a CSR with a filled perfect survey does not notify anyone
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
        "resource": "/service/commissioning_customer_service_records/7",
        "message": "Just a follow-up"
    }
    """
    Then the response status code should be 201
    And no email should have been sent asynchronously with subject matching pattern "~New comment on imperfect survey~"

  Scenario: A comment on a CSR without a filled survey does not notify anyone
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
        "resource": "/service/commissioning_customer_service_records/9",
        "message": "No survey yet"
    }
    """
    Then the response status code should be 201
    And no email should have been sent asynchronously with subject matching pattern "~New comment on imperfect survey~"

  Scenario: As a basic user, I can get CSR survey result list
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/service/commissioning_customer_service_records"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 5
    And the JSON node "hydra:member[0].@id" should be equal to "/service/commissioning_customer_service_records/5"
    And the JSON node "hydra:member[0].id" should be equal to "5"
    And the JSON node "hydra:member[0].equipmentRecord.serialNumber" should be equal to the string "polo"
    And the JSON node "hydra:member[0].status" should be equal to the string "IN-PROGRESS"
    And the JSON node "hydra:member[0].completedAt" should be null
    And the JSON node "hydra:member[0].ratingResult" should be equal to the string "5/0/5"

  Scenario: Get CSR survey result as excel file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/service/commissioning_customer_service_records?columns=id,createdAt,completedAt,equipmentRecord.salesOrganisation.name,equipmentRecord.salesOrganisationService.name,equipmentRecord.manufacturerLocation.name,technicians,equipmentRecord.endUser,equipmentRecord.serialNumber,equipmentRecord.model,airport,equipmentRecord.greenTagDate,equipmentRecord.dateCommissioned,aspect,aspect_comment,conformity,conformity_comment,operational,operational_comment,shipping,shipping_comment,is_link_working,is_link_working_comment"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Created At | Completed At | Sso | Sso Service | Factory | Technicians | User Customer | Serial Number | Model | Airport | Green Tag Date | Commissioning Date | Aspect Rating (max 5) | Aspect Rating (max 5) Comments | Conformity Rating (max 5) | Conformity Rating (max 5) Comments | Operational Rating (max 5) | Operational Rating (max 5) Comments | Shipping Damages Responsibility | Shipping Damages Responsibility Comments | Sending Data To LINK FMS ? | Sending Data To LINK FMS ? Comments |

  Scenario: As a AST user, I can create a CSR commissioning in assigned status
    Given I authenticate as the intranet user "user-ast@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/commissioning_customer_service_records" with body:
    """
    {
      "title": "Commissioning",
      "equipmentRecord": "/equipment_records/15",
      "airport": "/airports/62",
      "description": "",
      "leader": "/people/65",
      "plannedAt": "2024-07-01 20:00:00"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/csr/schemas/commissioning_customer_service_record.json"
    And the JSON node "createdBy.@id" should be equal to the string "/people/65"
    And the JSON node "createdAt" should be newer than 1 minute ago
    And the JSON node "title" should be equal to the string "Commissioning"
    And the JSON node "equipmentRecord.@id" should be equal to the string "/equipment_records/15"
    And the JSON node "airport.@id" should be equal to the string "/airports/62"
    And the JSON node "description" should be empty
    And the JSON node "openIntervention.leader.@id" should be equal to the string "/people/65"
    And the JSON node "openIntervention.plannedAt" should be equal to the string "2024-07-01T20:00:00-04:00"
    And the JSON node "status" should be equal to the string "ASSIGNED"
    And the JSON node "plannedAt" should be equal to the string "2024-07-01T20:00:00-04:00"
    And the JSON node "@type" should be equal to the string "CommissioningCustomerServiceRecord"

  @resetFileTable
  Scenario: Upload a csr attached file as an extranet user should not be possible
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/service/customer_service_records/1/files" with file "file" "file.doc"
    Then the response status code should be 403

  Scenario: Upload a csr attached file as a vendor user should not be possible
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/service/customer_service_records/1/files" with file "file" "file.doc"
    Then the response status code should be 403

  Scenario: As a basic user, I can upload a file to a csr
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/service/customer_service_records/1/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: As a CSM user, I can update a CSR file Visibility
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/files/1" with body:
    """
    {
      "public": true
    }
    """
    Then the response status code should be 204

  Scenario: Upload an invalid file to a csr
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/service/customer_service_records/1/files" with file "file" "image.gif"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "files: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "files"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: Download a csr attached file as an extranet user should not be possible
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/customer_service_records/1/files/1"
    Then the response status code should be 403

  Scenario: Download a csr attached file as a vendor user should not be possible
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/customer_service_records/1/files/1"
    Then the response status code should be 403

  Scenario: Download a csr attached file as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/customer_service_records/1/files/1"
    Then the response status code should be 200

  Scenario: As a user basic, I can't delete a file from a csr
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/service/customer_service_records/1/files/1"
    Then the response status code should be 403

  Scenario: As a csm, I can delete a file from a csr
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/service/customer_service_records/1/files/1"
    Then the response status code should be 204