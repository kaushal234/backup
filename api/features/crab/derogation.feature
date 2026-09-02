Feature: Test Derogation Entity

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Quality\Derogation" should only be available for "intranet" user

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Quality\Derogation" is exposed on the API
    Then the filter "status" should be available and its type should be "string"
    Then the filter "assignor" should be available and its type should be "string"
    Then the filter "assignee" should be available and its type should be "string"
    Then the filter "shortDescription" should be available and its type should be "string"
    Then the filter "dueDate[after]" should be available and its type should be "DateTimeInterface"
    Then the filter "dueDate[before]" should be available and its type should be "DateTimeInterface"
    Then the filter "crabs" should be available and its type should be "string"
    Then the filter "crabs.equipmentRecord" should be available and its type should be "string"
    Then the filter "crabs.equipmentRecord.product" should be available and its type should be "string"
    Then the filter "order[id]" should be available and its type should be "string"
    Then the filter "normalizationGroupsOverride[]" should be available and its type should be "string"
    And the filter "columns" should be available and its type should be "string"

  Scenario: Request all derogation
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/derogations"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/derogation/schemas/derogations.json"

  Scenario: Request all derogation for list
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/derogations?normalizationGroupsOverride[]=derogation:list&normalizationGroupsOverride[]=crab:equipment_list&normalizationGroupsOverride[]=equipment_list"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/derogation/schemas/derogation_list.json"

  Scenario: Request a single derogation
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/derogations/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/derogation/schemas/derogation.json"

  Scenario: Create a derogation should not be possible for basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/derogations" with body:
    """
    {}
    """
    Then the response status code should be 403

  Scenario: Create a derogation should be possible for basic qam but not with all properties and it should send email
    Given I authenticate as the intranet user "user-pm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/derogations" with body:
    """
    {
      "crabs": ["/quality/crabs/1"],
      "status": "ACCEPTED",
      "description": "please derogate me",
      "shortDescription": "please derogate me but short"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/derogation/schemas/derogation.json"
    And the JSON node "assignee.@id" should be equal to the string "/people/48"
    And the JSON node "status" should be equal to the string "OPEN"
    And the JSON node "description" should be equal to the string "please derogate me"
    And an email should have been sent asynchronously with subject matching pattern "/New derogation opened for CRAB#\S+ Assigned to PSM user/"
    And this asynchronous email should be sent to "user-psm@tld.fr"
    And this asynchronous email should be sent as cc to "user-qam@tld.fr"
    Given I authenticate as the intranet user "user-pm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/crabs/1"
    Then the response status code should be 200
    And the JSON node "derogation.description" should be equal to the string "please derogate me"
    And the JSON node "status" should be equal to the string "FOR-DEROGATION"

  Scenario: As assignee, I should receive a notification
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notifications.json"

  Scenario: I can't reopen a derogation on a closed CRAB
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "quality/derogations/1"
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "DENIED"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/derogations/1/status" with body:
    """
    {
      "status" : "OPEN",
      "comment": "this is a comment"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Status OPEN is not allowed. Reasons: Not possible to reopen a derogation on a closed CRAB."

  Scenario: I can't schedule or reschedule a due date after today
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/derogations/3" with body:
    """
    {
      "dueDate": "2000-09-31T00:00:00-04:00"
    }
    """
    Then the response status code should be 422

  Scenario: I can schedule or reschedule a due date after today
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/derogations/24" with body:
    """
    {
      "dueDate": "2150-09-31T00:00:00-04:00"
    }
    """
    Then the response status code should be 200
    And the JSON node "dueDate" should be equal to "2150-10-01T00:00:00-04:00"

  Scenario: Update a derogation should be possible but not for all fields
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/derogations/24" with body:
    """
    {
      "crabs": ["/quality/crabs/2"],
      "assignee": "/people/35",
      "status": "ACCEPTED",
      "description": "please derogate me, but updated",
      "shortDescription": "please derogate me short, but updated",
      "comment": "just for test"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/derogation/schemas/derogation.json"
    And the JSON node "assignee.@id" should be equal to "/people/35"
    And the JSON node "status" should be equal to the string "OPEN"
    And the JSON node "description" should be equal to "please derogate me"
    And the JSON node "shortDescription" should be equal to "please derogate me but short"
    And an email should have been sent asynchronously with subject matching pattern "/Derogation for CRAB#\S+/"
    And this asynchronous email should be sent to "user-spm@tld.fr"
    And this asynchronous email should be sent as cc to "user-qam@tld.fr"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/comments?resource=/quality/derogations/24"
    Then the JSON node "hydra:member[0].message" should be equal to the string "just for test"

  Scenario: Update a derogation description should be possible for QAM
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/derogations/24" with body:
    """
    {
      "description": "please derogate me, but updated",
      "shortDescription": "please derogate me short, but updated"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/derogation/schemas/derogation.json"
    And the JSON node "description" should be equal to "please derogate me, but updated"
    And the JSON node "shortDescription" should be equal to "please derogate me short, but updated"

  Scenario: Update a derogation status should not be possible for user basic
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/derogations/24/status" with body:
    """
    {
        "status": "DENIED",
        "comment": "this is a comment"
    }
    """
    Then the response status code should be 403

  Scenario: Close a derogation status should be possible for user qam but not for user PM
    Given I authenticate as the intranet user "user-pm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/derogations/24/status" with body:
    """
    {
        "status": "DENIED",
        "comment": "this is a comment"
    }
    """
    Then the response status code should be 400
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/derogations/24/status" with body:
    """
    {
        "status": "DENIED",
        "comment": "this is a comment"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/derogation/schemas/derogation.json"
    And the JSON node "status" should be equal to the string "DENIED"
    And the JSON node "closedBy.lastname" should be equal to the string "QAM"
    And the JSON node "closedAt" should be newer than 1 minute ago
    And an email should have been sent asynchronously with subject matching pattern "/Derogation for CRAB#\S+ denied/"
    And this asynchronous email should be sent to "user-pm@tld.fr"
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/crabs/1"
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "TO-FIX"
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/comments?resource=/quality/derogations/24"
    Then the JSON node "hydra:member[1].message" should be equal to "this is a comment"

  Scenario: Archiving a derogation should be possible for user qam but not for user PM
    Given I authenticate as the intranet user "user-pm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/derogations/24/status" with body:
    """
    {
        "status": "ARCHIVED",
        "comment": "this is a comment"
    }
    """
    Then the response status code should be 400
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/derogations/24/status" with body:
    """
    {
        "status": "ARCHIVED",
        "comment": "this is a comment"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/derogation/schemas/derogation.json"
    And the JSON node "status" should be equal to the string "ARCHIVED"

  Scenario: Update a closed or archived derogation should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/derogations/24" with body:
    """
    {
        "description": "toto"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Not possible to edit a closed or archived derogation."

  Scenario: Accepted a derogation should close crab and updated field in derogation
    Given I authenticate as the intranet user "user-pm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/derogations" with body:
    """
    {
      "crabs": ["/quality/crabs/3"],
      "description": "please derogate me",
      "shortDescription": "please derogate me but short"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/derogation/schemas/derogation.json"
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "quality/crabs/3" with body:
    """
    {
        "status": "TO-INSPECT",
        "fixingComments": "je teste que je ne peux changer le status d'un CRAB s'il est en derogation"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Not possible to edit a CRAB with the status FOR DEROGATION."
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/derogations/25/status" with body:
    """
    {
        "status": "ACCEPTED",
        "comment": "this is a comment"
    }
    """
    Then the response status code should be 200
    And an email should have been sent asynchronously with subject matching pattern "/Derogation for CRAB#\S+ accepted/"
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/crabs/3"
    And the JSON node "status" should be equal to the string "CLOSED"

  Scenario: Link an existing accepted derogation to a CRAB should be possible (with permission) and should close CRAB
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/crabs" with body:
    """
    {
      "equipmentRecord": "/equipment_records/12",
      "category": "Assy",
      "department": "/quality/crab_departments/1",
      "code": "/quality/crab_codes/1",
      "piQuestionId": 123456,
      "description": "Un pingouin sur la banquise ..change de description c est relou pour retrouver dans la DB ce qui a été créé qd les tests passent pas"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/crab/schemas/crab_post.json"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/crabs/8" with body:
    """
      {
        "derogation" : "/quality/derogations/25"
      }
    """
    Then the response status code should be 200
    And the JSON node "derogation" should be null
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/crabs/8" with body:
    """
      {
        "derogation" : "/quality/derogations/1"
      }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Only accepted derogation can be linked to a CRAB."
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/crabs/8" with body:
    """
      {
        "derogation" : "/quality/derogations/25"
      }
    """
    Then the response status code should be 200
    And the JSON node "derogation" should not be null
    And the JSON node "status" should be equal to the string "CLOSED"

  Scenario: Derogations filtered can be downloaded as an Excel file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/quality/derogations?columns=id,status,description,shortDescription,assignee,assignor,comment,dueDate,closedAt,closedBy"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Status | Description | Short Description | Assignee | Assignor | Comment | Due Date | Closed At | Closed By |

  Scenario: Update a derogation status should not be possible if no crabs
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/derogations/2/status" with body:
    """
    {
        "status": "ACCEPTED",
        "comment": "this is a comment"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "No crabs are affiliate to this derogation."

  Scenario: Deleting an accepted derogation should not be possible
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/derogations/25"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Not possible to delete an accepted derogation."

  @resetFileTable
  Scenario: As a qam user, I can upload a file to a derogation
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/derogations/1/files" with parameters:
      | key  | value      |
      | file | @file.doc  |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: Deleting a derogation should not be possible for user basic
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/derogations/1"
    Then the response status code should be 403

  Scenario: Deleting a non-accepted derogation should be possible for user qam
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/derogations/1"
    Then the response status code should be 204