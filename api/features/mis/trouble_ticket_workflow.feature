Feature: Test Trouble Ticket Workflow

  Scenario: Default status for incident trouble ticket should be PENDING
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    #This is Trouble Ticket #16
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
    And the JSON node "status" should be equal to the string "PENDING"

  Scenario: Default status for request trouble ticket should be PENDING MOO
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    #This is Trouble Ticket #17
    When I send a "POST" request to "/mis/trouble_tickets" with body:
    """
    {
      "shortDescription": "I have a problem",
      "description": "This is really a big problem, please fix quickly",
      "type": "/mis/types/6",
      "module": "/modules/5",
      "indiceFactor": "IF 1000",
      "ccs": ["/people/14", "/people/15"],
      "assignee": "/people/21"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "status" should be equal to the string "PENDING MOO/GKU"

  Scenario: Assignor should not be able to put trouble ticket in status IN PROGRESS if MIS assignee is not set
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/58" with body:
    """
    {
      "status": "IN PROGRESS"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Status IN PROGRESS is not allowed. Reasons: You are not allowed to change the status."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/58/transfer" with parameters:
      | key               | value                       |
      | comment           | test comment updated        |
      | ccs               | ["/people/14", "/people/15"]|
      | nullifyAssignee   | 1                           |
    Then the response status code should be 201
    And the JSON node "status" should be equal to the string "PENDING"

  Scenario: User MIS should be able to put trouble ticket in status IN PROGRESS
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/58" with body:
    """
    {
      "status": "IN PROGRESS"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "status" should be equal to the string "IN PROGRESS"

  Scenario: User that is neither assignee nor assignor should be able to put trouble ticket in status AWAITING USER
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/58/transfer" with parameters:
      | key               | value                       |
      | comment           | test comment updated        |
      | ccs               | ["/people/14", "/people/15"]|
      | assignee          | /people/13                  |
    Then the response status code should be 201

  Scenario: User MIS should not be able to break workflow
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/3" with body:
    """
    {
      "status": "SOLUTION PROPOSED"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Status SOLUTION PROPOSED is not allowed."

  Scenario: User MIS should be able to put back trouble ticket in status IN PROGRESS and to propose a solution
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/58" with body:
    """
    {
      "status": "PENDING"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "PENDING"
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/58" with body:
    """
    {
      "status": "IN PROGRESS"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "IN PROGRESS"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/58" with body:
    """
    {
      "status": "SOLUTION PROPOSED"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Status SOLUTION PROPOSED is not allowed. Reasons: You are not allowed to change the status."
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/58" with body:
    """
    {
      "status": "SOLUTION PROPOSED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "SOLUTION PROPOSED"
    And the JSON node "solutionProposedAt" should not be null

  Scenario: Assignee can put back Trouble Ticket to IN PROGRESS
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/58" with body:
    """
    {
      "status": "IN PROGRESS"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "IN PROGRESS"
    And the JSON node "solutionProposedAt" should be null
    #Putting back to SOLUTION PROPOSED to continue worklflow
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/58" with body:
    """
    {
      "status": "SOLUTION PROPOSED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "SOLUTION PROPOSED"
    And the JSON node "solutionProposedAt" should not be null

  Scenario: Only assignee/assignor can close as solved
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/58" with body:
    """
    {
      "status": "SOLVED"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Status SOLVED is not allowed. Reasons: You are not allowed to change the status."
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/58" with body:
    """
    {
      "status": "SOLVED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "SOLVED"
    And the JSON node "closedAt" should be newer than 1 minute ago
    And the JSON node "solutionProposedAt" should not be null

  Scenario: Trouble ticket closed since more than 1 month cannot be reopened
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/4/reopen" with parameters:
      | key               | value                       |
      | comment           | test comment updated        |
    Then the response status code should be 403

  Scenario: Only assignor/assignee/MOO/GKU can reopen a trouble ticket
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/58/reopen" with parameters:
      | key               | value                       |
      | comment           | test comment updated        |
    Then the response status code should be 201
    And the JSON node "status" should be equal to the string "IN PROGRESS"
    And the JSON node "closedAt" should be null

  Scenario: MIS Workflow from IN PROGRESS to SOLUTION PROPOSED and back to PENDING
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/57/status" with body:
    """
      {
        "status": "IN PROGRESS"
      }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "IN PROGRESS"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/57/status" with body:
    """
      {
        "status": "PENDING"
      }
    """
    Then the response status code should be 400
    And the JSON node "detail" should be equal to the string "Status PENDING is not allowed. Reasons: You are not allowed to change the status."
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/57/status" with body:
    """
      {
        "status": "SOLUTION PROPOSED"
      }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "SOLUTION PROPOSED"
    And the JSON node "misAssignee.username" should be equal to the string "user-mis@tld.fr"
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/57/status" with body:
    """
      {
        "status": "IN PROGRESS"
      }
    """
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/57/status" with body:
    """
      {
        "status": "PENDING"
      }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "PENDING"
    And the JSON node "misAssignee" should be null


  Scenario: Assignor should not be able to put REQUEST trouble ticket in status IN PROGRESS
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/59" with body:
    """
    {
      "status": "IN PROGRESS"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Status IN PROGRESS is not allowed."
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/59/transfer" with parameters:
      | key               | value                       |
      | comment           | test comment updated        |
      | ccs               | ["/people/14", "/people/15"]|
      | nullifyAssignee   | 1                           |
    Then the response status code should be 201
    And the JSON node "status" should be equal to the string "PENDING MOO/GKU"

  Scenario: Assignor should be able to put trouble ticket in status AWAITING USER
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/59/transfer" with parameters:
      | key               | value                       |
      | comment           | test comment updated        |
      | ccs               | ["/people/14", "/people/15"]|
      | assignee          | /people/13                  |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "status" should be equal to the string "MOO/GKU AWAITING USER"

  Scenario: Assignor should be able to put trouble ticket in status PENDING MOO/GKU by transferring it to the MOO
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/59/transfer" with parameters:
      | key               | value                       |
      | comment           | test comment updated        |
      | ccs               | ["/people/14", "/people/15"]|
      | assignee          | /people/66                  |
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Status PENDING MOO/GKU is not allowed. Reasons: You are not allowed to change the status."
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/59/transfer" with parameters:
      | key               | value                       |
      | comment           | test comment updated        |
      | ccs               | ["/people/14", "/people/15"]|
      | assignee          | /people/66                  |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "status" should be equal to the string "PENDING MOO/GKU"

  Scenario: Only moo/GKU can put trouble ticket in status MOO/GKU SOLUTION PROPOSED
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/59" with body:
    """
    {
      "status": "MOO/GKU SOLUTION PROPOSED"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Status MOO/GKU SOLUTION PROPOSED is not allowed. Reasons: You are not allowed to change the status."
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/59" with body:
    """
    {
      "status": "MOO/GKU SOLUTION PROPOSED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "MOO/GKU SOLUTION PROPOSED"

  Scenario: Assignor can put back trouble ticket in status PENDING MOO
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/59/transfer" with parameters:
      | key               | value                       |
      | comment           | test comment updated        |
      | ccs               | ["/people/14", "/people/15"]|
      | assignee          | /people/33                  |
      | status          | PENDING MOO/GKU             |
    Then the response status code should be 201
    And the JSON node "status" should be equal to the string "PENDING MOO/GKU"
    #Putting back to MOO/GKU SOLUTION PROPOSED to continue workflow
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/59" with body:
    """
    {
      "status": "MOO/GKU SOLUTION PROPOSED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "MOO/GKU SOLUTION PROPOSED"

  Scenario: Assignor only can closed trouble ticket as solved
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/59" with body:
    """
    {
      "status": "SOLVED"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Status SOLVED is not allowed. Reasons: You are not allowed to change the status."
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/59" with body:
    """
    {
      "status": "SOLVED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "SOLVED"
    And the JSON node "closedAt" should be newer than 1 minute ago
    And no email should have been sent asynchronously
    #User MISM already ahs a notification so we check he has 2
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notifications.json"
    And the JSON node "hydra:totalItems" should be equal to 2
    #User MOO SFR already has 3 notification so we check he has 4
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notifications.json"
    And the JSON node "hydra:totalItems" should be equal to 4
    #User expired already ahs a notification so we check he has 2
    Given I authenticate as the intranet user "user-expired@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notifications.json"
    And the JSON node "hydra:totalItems" should be equal to 2
    #User sageparts already ahs a notification so we check he has 2
    Given I authenticate as the intranet user "user-sageparts@sageparts.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notifications.json"
    And the JSON node "hydra:totalItems" should be equal to 2

  Scenario: Only assignor/assignee/MOO/GKU can reopen a trouble ticket
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/59/reopen" with parameters:
      | key               | value                       |
      | comment           | test comment updated        |
    Then the response status code should be 201
    And the JSON node "status" should be equal to the string "PENDING MOO/GKU"
    And the JSON node "closedAt" should be null

  Scenario: MOO/GKU can put a trouble ticket from PENDING MOO/GKU to PENDING
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/59" with body:
    """
    {
      "status": "PENDING"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "PENDING"

  Scenario: User MIS can put a trouble ticket from PENDING MOO/GKU to PENDING
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/8" with body:
    """
    {
      "status": "PENDING"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "PENDING"


  Scenario: Updating type of trouble ticket should change status
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/58" with body:
    """
    {
      "type": "/mis/types/6"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "PENDING MOO/GKU"

  Scenario: User MIS can Propose a solution on a PENDING_MOO TTS
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/54" with body:
    """
    {
      "status": "SOLUTION PROPOSED"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "status" should be equal to the string "SOLUTION PROPOSED"
    And the JSON node "misAssignee.username" should be equal to the string "user-mis@tld.fr"

  Scenario: User MIS can Propose a solution on a PENDING_MOO TTS through 'Transfer' Route
    # Reopen a TTS to test Workflow
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/4" with body:
    """
    {
        "assignee": "/people/66",
        "comment": "Back to MOO for workflow test",
        "status": "PENDING MOO/GKU"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "status" should be equal to the string "PENDING MOO/GKU"
    #Then testing the transfer route for solution proposal
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/4/transfer" with parameters:
      | key               | value                           |
      | comment           | Propose solution to User        |
      | assignee          | /people/29                      |
      | status            | SOLUTION PROPOSED               |
      | misAssignee       | /people/99                      |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "status" should be equal to the string "SOLUTION PROPOSED"
    And the JSON node "misAssignee.username" should be equal to the string "user-mis@tld.fr"

  Scenario: User MIS can Propose a solution on a AWAITING_USER_MOO TTS
    #Change status to MOO/GKU AWAITING USER to test Workflow
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/58/transfer" with parameters:
      | key               | value                       |
      | comment           | test comment updated        |
      | ccs               | ["/people/14", "/people/15"]|
      | assignee          | /people/13                  |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "status" should be equal to the string "MOO/GKU AWAITING USER"
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/58" with body:
    """
    {
      "status": "SOLUTION PROPOSED"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "status" should be equal to the string "SOLUTION PROPOSED"
    And the JSON node "misAssignee.username" should be equal to the string "user-mis@tld.fr"

  Scenario: User MIS can Propose a solution on a AWAITING_USER_MOO TTS through 'Transfer' Route
    # Reopen a TTS to test Workflow
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/2" with body:
    """
    {
        "assignee": "/people/33",
        "comment": "Back to MOO for workflow test",
        "status": "PENDING MOO/GKU"
    }
    """
    # Transfer to user Status to be tested
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/2" with body:
    """
    {
        "comment": "Back to user for workflow test",
        "status": "MOO/GKU AWAITING USER"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "status" should be equal to the string "MOO/GKU AWAITING USER"
    #Then testing the transfer route for solution proposal
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/2/transfer" with parameters:
      | key               | value                           |
      | comment           | Propose solution to User        |
      | assignee          | /people/48                      |
      | status            | SOLUTION PROPOSED               |
      | misAssignee       | /people/99                      |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "status" should be equal to the string "SOLUTION PROPOSED"
    And the JSON node "misAssignee.username" should be equal to the string "user-mis@tld.fr"

  Scenario: User MIS can Propose a solution on a AWAITING_USER TTS
    #Change status to AWAITING USER to test Workflow
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/1/transfer" with parameters:
      | key               | value                           |
      | comment           | Back to User for workflow test  |
      | assignee          | /people/48                      |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "status" should be equal to the string "AWAITING USER"
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/1" with body:
    """
    {
      "status": "SOLUTION PROPOSED"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "status" should be equal to the string "SOLUTION PROPOSED"
    And the JSON node "misAssignee.username" should be equal to the string "user-mis@tld.fr"


  Scenario: User MIS can Propose a solution on a AWAITING_USER TTS through 'Transfer' Route
    #Change status to AWAITING USER to test Workflow
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/59/transfer" with parameters:
      | key               | value                           |
      | comment           | Back to User for workflow test  |
      | assignee          | /people/48                      |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "status" should be equal to the string "AWAITING USER"
    #Then testing the transfer route for solution proposal
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/59/transfer" with parameters:
      | key               | value                           |
      | comment           | Propose solution to User        |
      | assignee          | /people/11                      |
      | status            | SOLUTION PROPOSED               |
      | misAssignee       | /people/99                      |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "status" should be equal to the string "SOLUTION PROPOSED"
    And the JSON node "misAssignee.username" should be equal to the string "user-mis@tld.fr"

  Scenario: TTS incident is created for following tests on a module not MIS relative
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    #This is Trouble Ticket #18
    When I send a "POST" request to "/mis/trouble_tickets" with body:
    """
    {
      "shortDescription": "I have a problem",
      "description": "This is really a big problem, please fix quickly",
      "type": "/mis/types/1",
      "module": "/modules/29"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "status" should be equal to the string "PENDING"

  Scenario: As MOO I can request information on a TTS incident for module not MIS relative
    Given I authenticate as the intranet user "user-coo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/60/transfer" with parameters:
      | key               | value                           |
      | comment           | I want information              |
      | assignee          | /people/16                      |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "status" should be equal to the string "AWAITING USER"


  Scenario: Create a Trouble ticket in Request without default assignee should set status to PENDING
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    #This is Trouble Ticket #19
    When I send a "POST" request to "/mis/trouble_tickets" with body:
    """
    {
      "shortDescription": "I have a problem",
      "description": "This is really a big problem, please fix quickly",
      "type": "/mis/types/1",
      "module": "/modules/28"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/trouble_ticket/schemas/trouble_ticket.json"
    And the JSON node "status" should be equal to the string "PENDING"

  Scenario: autoEscalated should be reset to false when trouble ticket goes from SOLUTION PROPOSED to IN PROGRESS
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/trouble_tickets" with body:
    """
    {
      "shortDescription": "Auto escalated ticket test",
      "description": "This ticket will be used to test autoEscalated reset",
      "type": "/mis/types/2",
      "module": "/modules/27",
      "indiceFactor": "IF 1000"
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to the string "/mis/trouble_tickets/62"
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/62/status" with body:
    """
    {
      "status": "IN PROGRESS"
    }
    """
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/62/status" with body:
    """
    {
      "status": "SOLUTION PROPOSED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "SOLUTION PROPOSED"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/62/status" with body:
    """
    {
      "status": "IN PROGRESS"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "IN PROGRESS"
    And the JSON node "autoEscalated" should be false
    And the JSON node "solutionProposedAt" should be null

  Scenario: autoEscalated should be reset to false when MOO trouble ticket goes from MOO/GKU SOLUTION PROPOSED to PENDING MOO/GKU
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/trouble_tickets" with body:
    """
    {
      "shortDescription": "Auto escalated MOO ticket test",
      "description": "This ticket will be used to test autoEscalated reset on MOO workflow",
      "type": "/mis/types/6",
      "module": "/modules/5",
      "indiceFactor": "IF 1000"
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to the string "/mis/trouble_tickets/63"
    And the JSON node "status" should be equal to the string "PENDING MOO/GKU"
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/trouble_tickets/63" with body:
    """
    {
      "status": "MOO/GKU SOLUTION PROPOSED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "MOO/GKU SOLUTION PROPOSED"
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/trouble_tickets/63/transfer" with parameters:
      | key     | value           |
      | comment | back to pending |
      | status  | PENDING MOO/GKU |
    Then the response status code should be 201
    And the JSON node "status" should be equal to the string "PENDING MOO/GKU"
    And the JSON node "autoEscalated" should be false
    And the JSON node "solutionProposedAt" should be null
