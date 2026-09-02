Feature: Test Trouble Ticket Entity

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\MIS\TroubleTicket\TroubleTicket" should only be available for intranet user

  Scenario: Request all trouble tickets as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/trouble_tickets"
    Then the response status code should be 403

  Scenario: Request a single trouble ticket as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/trouble_tickets/1"
    Then the response status code should be 403

  Scenario: Filters are declared on resource
    Given the class "App\Entity\MIS\TroubleTicket\TroubleTicket" is exposed on the API
    Then the filter "order[id]" should be available and its type should be "string"
    Then the filter "order[createdAt]" should be available and its type should be "string"
    Then the filter "order[lastCommentedAt]" should be available and its type should be "string"
    Then the filter "order[dueDate]" should be available and its type should be "string"
    Then the filter "order[type.type]" should be available and its type should be "string"
    Then the filter "order[status]" should be available and its type should be "string"
    Then the filter "order[jiraIssueNumber]" should be available and its type should be "string"
    Then the filter "order[indiceFactor]" should be available and its type should be "string"
    Then the filter "order[type.description]" should be available and its type should be "string"
    Then the filter "order[module.name]" should be available and its type should be "string"
    Then the filter "order[isAddToUserStories]" should be available and its type should be "string"
    Then the filter "order[createdBy.lastname]" should be available and its type should be "string"
    Then the filter "order[assignee.lastname]" should be available and its type should be "string"
    Then the filter "order[misAssignee.lastname]" should be available and its type should be "string"
    Then the filter "order[createdBy.businessUnit.region.name]" should be available and its type should be "string"
    Then the filter "id" should be available and its type should be "int"
    Then the filter "legacyId" should be available and its type should be "int"
    Then the filter "createdBy.businessUnit.location" should be available and its type should be "string"
    Then the filter "createdBy.businessUnit.region" should be available and its type should be "string"
    Then the filter "createdBy.businessUnit.region.subDivision" should be available and its type should be "string"
    Then the filter "createdBy.businessUnit.region.subDivision.division" should be available and its type should be "string"
    Then the filter "createdBy.premise.supportTeam" should be available and its type should be "string"
    Then the filter "module.application" should be available and its type should be "string"
    Then the filter "type" should be available and its type should be "string"
    Then the filter "type.type" should be available and its type should be "string"
    Then the filter "module" should be available and its type should be "string"
    Then the filter "assignee" should be available and its type should be "string"
    Then the filter "createdBy.premise" should be available and its type should be "string"
    Then the filter "module.operationalOwner" should be available and its type should be "string"
    Then the filter "indiceFactor" should be available and its type should be "string"
    Then the filter "misAssignee" should be available and its type should be "string"
    Then the filter "open" should be available and its type should be "bool"
    Then the filter "autoEscalated" should be available and its type should be "bool"
    Then the filter "exists[assignee]" should be available and its type should be "bool"
    Then the filter "exists[misAssignee]" should be available and its type should be "bool"
    Then the filter "exists[jiraIssueNumber]" should be available and its type should be "bool"
    Then the filter "createdAt[after]" should be available and its type should be "DateTimeInterface"
    Then the filter "createdAt[before]" should be available and its type should be "DateTimeInterface"
    Then the filter "dueDate[after]" should be available and its type should be "DateTimeInterface"
    Then the filter "dueDate[before]" should be available and its type should be "DateTimeInterface"
    Then the filter "lastCommentedAt[after]" should be available and its type should be "DateTimeInterface"
    Then the filter "lastCommentedAt[before]" should be available and its type should be "DateTimeInterface"
    Then the filter "normalizationGroups[]" should be available and its type should be "string"
    Then the filter "jiraIssueNumber" should be available and its type should be "string"
    Then the filter "shortDescription" should be available and its type should be "string"
    Then the filter "q" should be available and its type should be "string"
    And the filter "columns" should be available and its type should be "string"

  Scenario: Request all trouble tickets as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/trouble_tickets"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_tickets.json"

  Scenario: Request a single trouble ticket as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/trouble_tickets/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "module.localKeyUser" should not be null

  Scenario: Request a single trouble ticket as basic user should return its support level
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/trouble_tickets/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "supportLevel.@id" should be equal to the string "/mis/support_levels/3"
    And the JSON node "supportLevel.level" should be equal to 2
    And the JSON node "supportLevel.name" should be equal to the string "Technical support"

  Scenario: As basic user, I should not be able to update the support level of a trouble ticket
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/1" with body:
    """
    {
      "supportLevel": "/mis/support_levels/5"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "supportLevel.@id" should be equal to the string "/mis/support_levels/3"
    And the JSON node "supportLevel.level" should be equal to 2

  Scenario: As MIS user, I should be able to update the support level of a trouble ticket
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/1" with body:
    """
    {
      "supportLevel": "/mis/support_levels/5"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "supportLevel.@id" should be equal to the string "/mis/support_levels/5"
    And the JSON node "supportLevel.level" should be equal to 4
    And no email should have been sent asynchronously

  Scenario: As basic user, I should not be able to create a trouble ticket without module
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/trouble_tickets" with body:
    """
    {
      "shortDescription": "I have a problem",
      "description": "This is really a big problem, please fix quickly",
      "type": "/mis/types/2",
      "indiceFactor": "IF 100",
      "ccs": ["/people/14", "/people/15"],
      "additionalOwners": ["/people/10"],
      "assignee": "/people/21",
      "misAssignee": "/people/19",
      "satisfaction": "Satisfied"
    }
    """
    Then the response status code should be 422

  Scenario: As basic user, I should be able to create a trouble ticket (but not all properties)
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/trouble_tickets" with body:
    """
    {
      "shortDescription": "I have a problem",
      "description": "This is really a big problem, please fix quickly",
      "type": "/mis/types/2",
      "module": "/modules/3",
      "indiceFactor": "IF 100",
      "ccs": ["/people/14", "/people/15"],
      "additionalOwners": ["/people/10"],
      "assignee": "/people/21",
      "misAssignee": "/people/19",
      "satisfaction": "Satisfied"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "shortDescription" should be equal to the string "I have a problem"
    And the JSON node "description" should be equal to the string "This is really a big problem, please fix quickly"
    And the JSON node "type.@id" should be equal to the string "/mis/types/2"
    And the JSON node "module.@id" should be equal to the string "/modules/3"
    And the JSON node "indiceFactor" should be equal to the string "IF 100"
    And the JSON node "ccs[0].@id" should be equal to the string "/people/14"
    And the JSON node "ccs[1].@id" should be equal to the string "/people/15"
    And the JSON node "additionalOwners" should have 0 element
    And the JSON node "assignee" should be null
    And the JSON node "misAssignee" should be null
    And the JSON node "satisfaction" should be null
    And an email should have been sent asynchronously with subject matching pattern "/TTS #\S+ opened, Intranet - ODIL, location_parts/"
    And this asynchronous email should be sent only to "user-asm@tld.fr"
    And this asynchronous email should be sent as cc only to "user-expired@tld.fr, user-sageparts@sageparts.com"

  Scenario: Default assignee is set when it's defined on database
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/trouble_tickets" with body:
    """
    {
      "shortDescription": "I have a problem",
      "description": "This is really a big problem, please fix quickly",
      "type": "/mis/types/2",
      "module": "/modules/27",
      "indiceFactor": "IF 1000",
      "ccs": ["/people/14", "/people/15"],
      "assignee": "/people/21"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "shortDescription" should be equal to the string "I have a problem"
    And the JSON node "description" should be equal to the string "This is really a big problem, please fix quickly"
    And the JSON node "type.@id" should be equal to the string "/mis/types/2"
    And the JSON node "module.@id" should be equal to the string "/modules/27"
    And the JSON node "indiceFactor" should be equal to the string "IF 1000"
    And the JSON node "ccs[0].@id" should be equal to the string "/people/14"
    And the JSON node "ccs[1].@id" should be equal to the string "/people/15"
    And the JSON node "assignee.@id" should be equal to the string "/people/11"
    And an email should have been sent asynchronously with subject matching pattern "/TTS #\S+ opened, Intranet - ER, location_parts/"
    And this asynchronous email should be sent only to "user-coo@tld.fr, user-mism@tld.fr, user-gceo@tld.fr, user-cio@tld.fr"
    And this asynchronous email should be sent as cc only to "user-expired@tld.fr, user-sageparts@sageparts.com"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/comments?resource=/mis/trouble_tickets/55"
    Then the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].message" should contain "<p><strong>Answer to the following questions in this ticket:</strong></p>"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notifications.json"
    And the JSON node "hydra:totalItems" should be equal to 1

  Scenario: As creator of TTS, I should be able to update a trouble ticket (but not all fields)
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/54" with body:
    """
    {
      "shortDescription": "I have a problem, please update",
      "description": "This is really a big problem, please fix quickly, please update",
      "type": "/mis/types/3",
      "module": "/modules/4",
      "indiceFactor": "IF 1000",
      "ccs": ["/people/14"],
      "additionalOwners": ["/people/10"],
      "assignee": "/people/21",
      "misAssignee": "/people/19",
      "comment": "Just a test comment",
      "satisfaction": "Satisfied"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "shortDescription" should be equal to the string "I have a problem"
    And the JSON node "description" should be equal to the string "This is really a big problem, please fix quickly"
    And the JSON node "type.@id" should be equal to the string "/mis/types/2"
    And the JSON node "module.@id" should be equal to the string "/modules/3"
    And the JSON node "indiceFactor" should be equal to the string "IF 100"
    And the JSON node "ccs[0].@id" should be equal to the string "/people/14"
    And the JSON node "ccs[1].@id" should be equal to the string "/people/15"
    And the JSON node "additionalOwners" should have 1 element
    And the JSON node "additionalOwners[0].@id" should be equal to the string "/people/10"
    And the JSON node "assignee" should be null
    And the JSON node "misAssignee" should be null
    And the JSON node "satisfaction" should be equal to the string "Satisfied"
    And an email should have been sent asynchronously with subject matching pattern "/TTS #\S+ updated, Intranet - ODIL, location_parts/"
    And this asynchronous email should be sent only to "user-asm@tld.fr"
    And this asynchronous email should be sent as cc only to "user-expired@tld.fr, user-sageparts@sageparts.com, user-transferred@tld.fr"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/comments?resource=/mis/trouble_tickets/54"
    Then the JSON node "hydra:member" should have 2 element

  Scenario: As creator of TTS, I should be able to comment TTS (comment is mandatory)
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/8/comment" with parameters:
      | key      | value              |
      | ccs      | ["/people/14"]     |
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Comment is mandatory."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/54/comment" with parameters:
      | key               | value              |
      | comment           | test comment       |
      | ccs               | ["/people/14"]     |
      | assignee          | /people/14         |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "ccs" should have 1 element
    And the JSON node "ccs[0].@id" should be equal to the string "/people/14"
    And the JSON node "assignee" should be null
    And an email should have been sent asynchronously with subject matching pattern "/TTS #\S+ commented by BASIC user, Intranet - ODIL, location_parts/"
    And this asynchronous email should be sent only to "user-asm@tld.fr"
    And this asynchronous email should be sent as cc only to "user-expired@tld.fr, user-transferred@tld.fr"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/comments?resource=/mis/trouble_tickets/54"
    Then the JSON node "hydra:member[2].message" should be equal to the string "test comment"

  Scenario: As creator of TTS, I should be able to transfer TTS (comment is mandatory)
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/54/transfer" with parameters:
      | key       | value        |
      | assignee  | /people/14   |
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Comment is mandatory."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/54/transfer" with parameters:
      | key               | value                       |
      | comment           | test comment updated        |
      | ccs               | ["/people/14", "/people/15"]|
      | assignee          | /people/13                  |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "ccs" should have 2 elements
    And the JSON node "ccs[0].@id" should be equal to the string "/people/14"
    And the JSON node "ccs[1].@id" should be equal to the string "/people/15"
    And the JSON node "assignee.@id" should be equal to the string "/people/13"
    And an email should have been sent asynchronously with subject matching pattern "/TTS #\S+ transferred to HR user, Intranet - ODIL, location_parts/"
    And this asynchronous email should be sent only to "user-asm@tld.fr, user-hr@tld.fr"
    And this asynchronous email should be sent as cc only to "user-expired@tld.fr, user-sageparts@sageparts.com, user-transferred@tld.fr"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/comments?resource=/mis/trouble_tickets/54"
    And the JSON node "hydra:member" should have 4 elements
    Then the JSON node "hydra:member[3].message" should be equal to the string "test comment updated"

  Scenario: As user mis, I should be able to update a trouble ticket
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/54" with body:
    """
    {
      "shortDescription": "I have a problem, please update",
      "description": "This is really a big problem, please fix quickly, please update",
      "type": "/mis/types/6",
      "module": "/modules/5",
      "indiceFactor": "IF 1000",
      "ccs": ["/people/14"],
      "additionalOwners": [],
      "assignee": "/people/21",
      "misAssignee": "/people/19",
      "satisfaction": "Not satisfied at all"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "shortDescription" should be equal to the string "I have a problem, please update"
    And the JSON node "description" should be equal to the string "This is really a big problem, please fix quickly"
    And the JSON node "type.@id" should be equal to the string "/mis/types/6"
    And the JSON node "module.@id" should be equal to the string "/modules/5"
    And the JSON node "indiceFactor" should be equal to the string "IF 1000"
    And the JSON node "ccs" should have 2 element
    And the JSON node "ccs[0].@id" should be equal to the string "/people/14"
    And the JSON node "ccs[1].@id" should be equal to the string "/people/15"
    And the JSON node "additionalOwners" should have 0 element
    And the JSON node "assignee.@id" should be equal to the string "/people/66"
    And the JSON node "misAssignee" should be null
    And the JSON node "satisfaction" should be equal to the string "Not satisfied at all"
    And an email should have been sent asynchronously with subject matching pattern "/TTS #\S+ updated, Intranet - SFR, location_parts/"
    And this asynchronous email should be sent only to "user-basic@tld.fr, user-mism@tld.fr, user-moo-sfr@tld.fr, user-cio@tld.fr"
    And this asynchronous email should be sent as cc only to "user-expired@tld.fr, user-sageparts@sageparts.com, user-mis@tld.fr"

  Scenario: As basic user, I should not be able to create a trouble ticket on behalf of someone else
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/trouble_tickets" with body:
    """
    {
      "createdBy": "/people/13",
      "shortDescription": "I have a problem",
      "description": "This is really a big problem, please fix quickly",
      "type": "/mis/types/2",
      "module": "/modules/3",
      "indiceFactor": "IF 100",
      "ccs": ["/people/14", "/people/15"]
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "createdBy.@id" should be equal to the string "/people/11"

  Scenario: As user MIS, I should be able to create a trouble ticket on behalf of someone else
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/trouble_tickets" with body:
    """
    {
      "createdBy": "/people/13",
      "shortDescription": "I have a problem",
      "description": "This is really a big problem, please fix quickly",
      "type": "/mis/types/2",
      "module": "/modules/3",
      "indiceFactor": "IF 100",
      "ccs": ["/people/14", "/people/15"]
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "createdBy.@id" should be equal to the string "/people/13"

  Scenario: Download excel trouble ticket reports should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/mis/trouble_tickets?columns=id,createdBy,assignee,misAssignee,createdAt,status,module.application.name,module.name,module.operationalOwner,type,shortDescription,indiceFactor,dueDate,satisfaction,satisfactionComment"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Created By | Assignee| Mis Assignee | Created At | Status | Application | Module | Moo | Type | Subject | Indice Factor | Due Date | Satisfaction | Satisfaction Comment |


  @resetFileTable
  Scenario: As a basic user, I can't upload a file to a trouble ticket
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/1/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403

  Scenario: As a user mis, I can upload a file to a trouble ticket and it will not zip the file because ticket is security high attention
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/1/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
    And the JSON node "extension" should not be equal to "zip"
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/1/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
    And the JSON node "extension" should be equal to "zip"

  Scenario: As a user mis, I can upload a file to a trouble ticket
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/3/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
    And the JSON node "extension" should be equal to "doc"

  Scenario: Upload an invalid file to a trouble ticket
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/3/files" with file "file" "image.gif"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "files: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "files"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: Download a trouble ticket attached file as an extranet user should not be possible
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/trouble_tickets/1/files/1"
    Then the response status code should be 403

  Scenario: Download a trouble ticket attached file as a vendor user should not be possible
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/trouble_tickets/1/files/1"
    Then the response status code should be 403

  Scenario: Download a trouble ticket attached file as an authorized application should not be possible
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/trouble_tickets/1/files/1"
    Then the response status code should be 403

  Scenario: Download a trouble ticket attached file as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/trouble_tickets/1/files/1"
    Then the response status code should be 200

  Scenario: As a basic user, I can't delete a file from a trouble ticket
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/mis/trouble_tickets/1/files/1"
    Then the response status code should be 403

  Scenario: As a user mis, I can delete a file from a trouble ticket
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/mis/trouble_tickets/1/files/1"
    Then the response status code should be 204