Feature: Test Project Entity

  Scenario: Resource should only be accessible for intranet users
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/projects"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/project/schemas/projects.json"
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/projects/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/project/schemas/project.json"
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/projects"
    Then the response status code should be 403
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/projects/1"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/projects"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/projects/1"
    Then the response status code should be 403

  Scenario: Filters are declared on resource
    Given the class "App\Entity\MIS\Project\Project" is exposed on the API
    Then the filter "q" should be available and its type should be "string"
    Then the filter "order[id]" should be available and its type should be "string"
    And the filter "order[name]" should be available and its type should be "string"
    And the filter "order[indicesFactor]" should be available and its type should be "string"
    And the filter "order[createdAt]" should be available and its type should be "string"
    And the filter "order[startedAt]" should be available and its type should be "string"
    And the filter "order[module.name]" should be available and its type should be "string"
    And the filter "order[region.name]" should be available and its type should be "string"
    And the filter "order[projectManager.lastname]" should be available and its type should be "string"
    And the filter "order[misOwner.lastname]" should be available and its type should be "string"
    And the filter "order[status]" should be available and its type should be "string"
    And the filter "status" should be available and its type should be "string"
    And the filter "projectManager" should be available and its type should be "string"
    And the filter "misOwner" should be available and its type should be "string"
    And the filter "indicesFactor" should be available and its type should be "string"
    And the filter "module" should be available and its type should be "string"
    And the filter "misMembers" should be available and its type should be "string"
    And the filter "moduleKeyUsers" should be available and its type should be "string"
    And the filter "region" should be available and its type should be "string"
    And the filter "tags" should be available and its type should be "string"
    And the filter "normalizationGroups[]" should be available and its type should be "string"
    And the filter "startedAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "startedAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "createdAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "createdAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "order[activePhaseEstimatedClosureAt]" should be available and its type should be "string"
    And the filter "order[activePhaseRevisedClosureAt]" should be available and its type should be "string"
    And the filter "order[dueDate]" should be available and its type should be "string"
    And the filter "order[revisedDueDate]" should be available and its type should be "string"
    And the filter "columns" should be available and its type should be "string"

  Scenario: As mis user I can create an MIS Project and no task should have been created
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/projects" with body:
    """
    {
      "name": "new project",
      "teamsLink": "https://The-Party.com",
      "description": "this is the description of the new project",
      "indicesFactor": "IF 10",
      "region": "/regions/2",
      "projectManager": "/people/12",
      "misOwner": "/people/99",
      "startedAt": "2025-05-26",
      "phases": [
        {
          "number": 0,
          "estimatedClosureAt": "2099-06-01",
          "estimatedHours": 20,
          "revisedEstimatedHours": 30
        },
        {
          "number": 1,
          "estimatedClosureAt": "2099-06-10",
          "estimatedHours": 20,
          "revisedEstimatedHours": 30
        },
        {
          "number": 2,
          "estimatedClosureAt": "2099-06-20",
          "estimatedHours": 20,
          "revisedEstimatedHours": 30
        },
        {
          "number": 3,
          "estimatedClosureAt": "2099-06-30",
          "estimatedHours": 20,
          "revisedEstimatedHours": 30
        },
        {
          "number": 4,
          "estimatedClosureAt": "2099-07-01",
          "estimatedHours": 20,
          "revisedEstimatedHours": 30
        }
      ],
      "estimatedHours": 100,
      "moduleKeyUsers": ["/people/4", "/people/5", "/people/6"],
      "misMembers": ["/people/7", "/people/8", "/people/9"],
      "module": "/modules/1",
      "tags": ["/mis/project_tags/6"]
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/project/schemas/project.json"
    And the JSON node "name" should be equal to the string "new project"
    And the JSON node "teamsLink" should be equal to the string "https://The-Party.com"
    And the JSON node "description" should be equal to the string "this is the description of the new project"
    And the JSON node "indicesFactor" should be equal to the string "IF 10"
    And the JSON node "region.@id" should be equal to the string "/regions/2"
    And the JSON node "projectManager.@id" should be equal to the string "/people/12"
    And the JSON node "misOwner.@id" should be equal to the string "/people/99"
    And the JSON node "createdAt" should contain today's date
    And the JSON node "startedAt" should be equal to the string "26.05.2025"
    And the JSON node "phases[0].estimatedClosureAt" should be equal to the string "2099-06-01T00:00:00-04:00"
    And the JSON node "phases[1].estimatedClosureAt" should be equal to the string "2099-06-10T00:00:00-04:00"
    And the JSON node "phases[2].estimatedClosureAt" should be equal to the string "2099-06-20T00:00:00-04:00"
    And the JSON node "phases[3].estimatedClosureAt" should be equal to the string "2099-06-30T00:00:00-04:00"
    And the JSON node "phases[4].estimatedClosureAt" should be equal to the string "2099-07-01T00:00:00-04:00"
    And the JSON node "phases[0].estimatedHours" should be equal to 20
    And the JSON node "phases[1].estimatedHours" should be equal to 20
    And the JSON node "phases[2].estimatedHours" should be equal to 20
    And the JSON node "phases[3].estimatedHours" should be equal to 20
    And the JSON node "phases[4].estimatedHours" should be equal to 20
    And the JSON node "estimatedHours" should be equal to 100
    And the JSON node "estimatedHours" should be equal to 100
    And the JSON node "phases[0].revisedEstimatedHours" should be null
    And the JSON node "phases[1].revisedEstimatedHours" should be null
    And the JSON node "phases[2].revisedEstimatedHours" should be null
    And the JSON node "phases[3].revisedEstimatedHours" should be null
    And the JSON node "phases[4].revisedEstimatedHours" should be null
    And the JSON node "revisedEstimatedHours" should be null
    And the JSON node "phases[0].number" should be equal to 0
    And the JSON node "phases[1].number" should be equal to 1
    And the JSON node "phases[2].number" should be equal to 2
    And the JSON node "phases[3].number" should be equal to 3
    And the JSON node "phases[4].number" should be equal to 4
    And the JSON node "moduleKeyUsers[0].@id" should be equal to the string "/people/4"
    And the JSON node "moduleKeyUsers[1].@id" should be equal to the string "/people/5"
    And the JSON node "moduleKeyUsers[2].@id" should be equal to the string "/people/6"
    And the JSON node "misMembers[0].@id" should be equal to the string "/people/7"
    And the JSON node "misMembers[1].@id" should be equal to the string "/people/8"
    And the JSON node "misMembers[2].@id" should be equal to the string "/people/9"
    And the JSON node "module.@id" should be equal to the string "/modules/1"
    And the JSON node "tags[0].name" should be equal to the string "WEBSITE"
    And the JSON node "confidential" should be false
    And no email should have been sent asynchronously

  Scenario: As basic user I should be able to comment any non confidential project
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/1/comment" with parameters:
      | key               | value              |
      | comment           | test comment       |
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/comments?resource=/mis/projects/2"
    Then the JSON node "hydra:member" should have 0 element
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/2/comment" with parameters:
      | key               | value              |
      | comment           | test comment       |
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/comments?resource=/mis/projects/2"
    Then the JSON node "hydra:member" should have 1 element

  Scenario: As CIO I should be able to comment any project
    Given I authenticate as the intranet user "user-cio@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/comments?resource=/mis/projects/1"
    Then the JSON node "hydra:member" should have 0 element
    Given I authenticate as the intranet user "user-cio@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/1/comment" with parameters:
      | key               | value              |
      | comment           | test comment       |
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-cio@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/comments?resource=/mis/projects/1"
    Then the JSON node "hydra:member" should have 1 element
    Given I authenticate as the intranet user "user-cio@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/2/comment" with parameters:
      | key               | value              |
      | comment           | test comment       |
    Then the response status code should be 201
    And the JSON node "lastComment" should contain "test comment"
    And the JSON node "lastCommentedAt" should be newer than 1 minute ago
    Given I authenticate as the intranet user "user-cio@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/comments?resource=/mis/projects/2"
    Then the JSON node "hydra:member" should have 2 elements

  Scenario: As basic user I should not be able to changer status
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/1/status" with parameters:
      | key               | value              |
      | comment           | test comment       |
      | status            | PHASE 0            |
    Then the response status code should be 403

  Scenario: As project manager, changing status to Phase 0 should create 2 tasks and send 2 emails
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/6/status" with parameters:
      | key               | value              |
      | comment           | test comment       |
      | status            | PHASE 0            |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/project/schemas/project.json"
    And an email should have been sent asynchronously with subject "Task #54 opened, for SUPERUSER user by SUPERUSER user"
    And this asynchronous email should be sent only to "user-superuser@tld.fr"
    And an email should have been sent asynchronously with subject "Task #55 opened, for SUPERUSER user by SUPERUSER user"
    And this asynchronous email should be sent only to "user-superuser@tld.fr"
    And an email should have been sent asynchronously with subject "MIS Project #6 (new project) to PHASE 0"
    And this asynchronous email should be sent only to "user-cio@tld.fr, user-superuser@tld.fr, user-mis@tld.fr, representative_ellie@tlou.com, representative_abi@tld.com, representative_6_@loop.io, representative_7_@loop.io, representative_8_@loop.io, representative_9_@loop.io"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/comments?resource=/mis/projects/6"
    Then the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].message" should contain "Project status changed from PENDING to PHASE 0"

  Scenario: As project manager, changing status to Phase 1 without closing open tasks should not be permitted
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/6/status" with parameters:
      | key               | value              |
      | comment           | test comment       |
      | status            | PHASE 1            |
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to the string "status: At least one task of previous Phase is still open, please close it before changing status."

  Scenario: Closing open tasks should allow going to PHASE 1, and it should create another task
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/54/close" with parameters:
      | key          | value                       |
      | comment      | Here we go!                 |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "status" should be equal to the string "CLOSED"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/55/close" with parameters:
      | key          | value                       |
      | comment      | Here we go!                 |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "status" should be equal to the string "CLOSED"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/6/status" with parameters:
      | key               | value              |
      | comment           | test comment       |
      | status            | PHASE 1            |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/project/schemas/project.json"
    And the JSON node "phases[0].revisedClosureAt" should be newer than 1 minute ago
    And an email should have been sent asynchronously with subject "Task #56 opened, for SUPERUSER user by SUPERUSER user"
    And this asynchronous email should be sent only to "user-superuser@tld.fr"
    And an email should have been sent asynchronously with subject "MIS Project #6 (new project) to PHASE 1"
    And this asynchronous email should be sent only to "user-cio@tld.fr, user-superuser@tld.fr, user-mis@tld.fr, representative_ellie@tlou.com, representative_abi@tld.com, representative_6_@loop.io, representative_7_@loop.io, representative_8_@loop.io, representative_9_@loop.io"

  Scenario: As project manager, changing status to Phase 2 without closing open tasks should not be permitted
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/6/status" with parameters:
      | key               | value              |
      | comment           | test comment       |
      | status            | PHASE 2            |
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to the string "status: At least one task of previous Phase is still open, please close it before changing status."

  Scenario: Closing open tasks should allow going to PHASE 2, and it should create another task
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/56/close" with parameters:
      | key          | value                       |
      | comment      | Here we go!                 |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "status" should be equal to the string "CLOSED"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/6/status" with parameters:
      | key               | value              |
      | comment           | test comment       |
      | status            | PHASE 2            |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/project/schemas/project.json"
    And the JSON node "phases[1].revisedClosureAt" should be newer than 1 minute ago
    And an email should have been sent asynchronously with subject "Task #57 opened, for SUPERUSER user by SUPERUSER user"
    And this asynchronous email should be sent only to "user-superuser@tld.fr"
    And an email should have been sent asynchronously with subject "MIS Project #6 (new project) to PHASE 2"
    And this asynchronous email should be sent only to "user-cio@tld.fr, user-superuser@tld.fr, user-mis@tld.fr, representative_ellie@tlou.com, representative_abi@tld.com, representative_6_@loop.io, representative_7_@loop.io, representative_8_@loop.io, representative_9_@loop.io"

  Scenario: As project manager, changing status to Phase 3 without closing open tasks should not be permitted
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/6/status" with parameters:
      | key               | value              |
      | comment           | test comment       |
      | status            | PHASE 3            |
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to the string "status: At least one task of previous Phase is still open, please close it before changing status."

  Scenario: Closing open tasks should allow going to PHASE 3, and it should create another task
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/57/close" with parameters:
      | key          | value                       |
      | comment      | Here we go!                 |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "status" should be equal to the string "CLOSED"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/6/status" with parameters:
      | key               | value              |
      | comment           | test comment       |
      | status            | PHASE 3            |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/project/schemas/project.json"
    And the JSON node "phases[2].revisedClosureAt" should be newer than 1 minute ago
    And an email should have been sent asynchronously with subject "Task #58 opened, for SUPERUSER user by SUPERUSER user"
    And this asynchronous email should be sent only to "user-superuser@tld.fr"
    And an email should have been sent asynchronously with subject "MIS Project #6 (new project) to PHASE 3"
    And this asynchronous email should be sent only to "user-cio@tld.fr, user-superuser@tld.fr, user-mis@tld.fr, representative_ellie@tlou.com, representative_abi@tld.com, representative_6_@loop.io, representative_7_@loop.io, representative_8_@loop.io, representative_9_@loop.io"

  Scenario: As project manager, changing status to Phase 4 without closing open tasks should not be permitted
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/6/status" with parameters:
      | key               | value              |
      | comment           | test comment       |
      | status            | PHASE 4            |
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to the string "status: At least one task of previous Phase is still open, please close it before changing status."

  Scenario: Closing open tasks should allow going to PHASE 4
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/58/close" with parameters:
      | key          | value                       |
      | comment      | Here we go!                 |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "status" should be equal to the string "CLOSED"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/6/status" with parameters:
      | key               | value              |
      | comment           | test comment       |
      | status            | PHASE 4            |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/project/schemas/project.json"
    And the JSON node "phases[3].revisedClosureAt" should be newer than 1 minute ago
    And an email should have been sent asynchronously with subject "Task #59 opened, for SUPERUSER user by SUPERUSER user"
    And this asynchronous email should be sent only to "user-superuser@tld.fr"
    And an email should have been sent asynchronously with subject "MIS Project #6 (new project) to PHASE 4"
    And this asynchronous email should be sent only to "user-cio@tld.fr, user-superuser@tld.fr, user-mis@tld.fr, representative_ellie@tlou.com, representative_abi@tld.com, representative_6_@loop.io, representative_7_@loop.io, representative_8_@loop.io, representative_9_@loop.io"

  Scenario: As basic user, I should not be able to edit project
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/projects/6" with body:
    """
    {}
    """
    Then the response status code should be 403

  Scenario: As MIS Owner or Project Manager, I should be able to edit project except for estimated closure of phases
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/projects/6" with body:
        """
        {
          "name": "new project updated",
          "teamsLink": "https://The-Mugiwara.com",
          "description": "this is the description of the new project updated",
          "indicesFactor": "IF 100",
          "region": "/regions/3",
          "projectManager": "/people/15",
          "misOwner": "/people/13",
          "startedAt": "2025-05-27",
          "phases": [
            {
              "@id": "/mis/phases/26",
              "estimatedClosureAt": "2099-07-01",
              "revisedClosureAt": "2099-07-01",
              "estimatedHours": 10,
              "revisedEstimatedHours": 15
            },
            {
              "@id": "/mis/phases/27",
              "estimatedClosureAt": "2099-07-10",
              "revisedClosureAt": "2099-07-10",
              "estimatedHours": 10,
              "revisedEstimatedHours": 15
            },
            {
              "@id": "/mis/phases/28",
              "estimatedClosureAt": "2099-07-20",
              "revisedClosureAt": "2099-07-20",
              "estimatedHours": 10,
              "revisedEstimatedHours": 15
            },
            {
              "@id": "/mis/phases/29",
              "estimatedClosureAt": "2099-07-30",
              "revisedClosureAt": "2099-07-30",
              "estimatedHours": 10,
              "revisedEstimatedHours": 15
            },
            {
              "@id": "/mis/phases/30",
              "estimatedClosureAt": "2099-08-01",
              "revisedClosureAt": "2099-08-01",
              "estimatedHours": 10,
              "revisedEstimatedHours": 15
            }
          ],
          "moduleKeyUsers": ["/people/29", "/people/30", "/people/31"],
          "misMembers": ["/people/32", "/people/33", "/people/34"],
          "module": "/modules/2",
          "tags": ["/mis/project_tags/7"],
          "confidential": true
        }
        """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/project/schemas/project.json"
    And the JSON node "name" should be equal to the string "new project updated"
    And the JSON node "teamsLink" should be equal to the string "https://The-Mugiwara.com"
    And the JSON node "description" should be equal to the string "this is the description of the new project updated"
    And the JSON node "indicesFactor" should be equal to the string "IF 100"
    And the JSON node "region.@id" should be equal to the string "/regions/3"
    And the JSON node "projectManager.@id" should be equal to the string "/people/15"
    And the JSON node "misOwner.@id" should be equal to the string "/people/13"
    And the JSON node "startedAt" should be equal to the string "27.05.2025"
    And the JSON node "phases[0].estimatedClosureAt" should be equal to the string "2099-06-01T00:00:00-04:00"
    And the JSON node "phases[1].estimatedClosureAt" should be equal to the string "2099-06-10T00:00:00-04:00"
    And the JSON node "phases[2].estimatedClosureAt" should be equal to the string "2099-06-20T00:00:00-04:00"
    And the JSON node "phases[3].estimatedClosureAt" should be equal to the string "2099-06-30T00:00:00-04:00"
    And the JSON node "phases[4].estimatedClosureAt" should be equal to the string "2099-07-01T00:00:00-04:00"
    And the JSON node "phases[0].estimatedHours" should be equal to 10
    And the JSON node "phases[1].estimatedHours" should be equal to 10
    And the JSON node "phases[2].estimatedHours" should be equal to 10
    And the JSON node "phases[2].estimatedHours" should be equal to 10
    And the JSON node "phases[4].estimatedHours" should be equal to 10
    And the JSON node "phases[0].revisedEstimatedHours" should be equal to 15
    And the JSON node "phases[1].revisedEstimatedHours" should be equal to 15
    And the JSON node "phases[2].revisedEstimatedHours" should be equal to 15
    And the JSON node "phases[3].revisedEstimatedHours" should be equal to 15
    And the JSON node "phases[4].revisedEstimatedHours" should be equal to 15
    And the JSON node "phases[0].revisedClosureAt" should be equal to the string "2099-07-01T00:00:00-04:00"
    And the JSON node "phases[1].revisedClosureAt" should be equal to the string "2099-07-10T00:00:00-04:00"
    And the JSON node "phases[2].revisedClosureAt" should be equal to the string "2099-07-20T00:00:00-04:00"
    And the JSON node "phases[3].revisedClosureAt" should be equal to the string "2099-07-30T00:00:00-04:00"
    And the JSON node "phases[4].revisedClosureAt" should be equal to the string "2099-08-01T00:00:00-04:00"
    And the JSON node "moduleKeyUsers[0].@id" should be equal to the string "/people/29"
    And the JSON node "moduleKeyUsers[1].@id" should be equal to the string "/people/30"
    And the JSON node "moduleKeyUsers[2].@id" should be equal to the string "/people/31"
    And the JSON node "misMembers[0].@id" should be equal to the string "/people/32"
    And the JSON node "misMembers[1].@id" should be equal to the string "/people/33"
    And the JSON node "misMembers[2].@id" should be equal to the string "/people/34"
    And the JSON node "module.@id" should be equal to the string "/modules/2"
    And the JSON node "estimatedHours" should be equal to the string "50"
    And the JSON node "revisedEstimatedHours" should be equal to the string "75"
    And the JSON node "tags[0].name" should be equal to the string "SECURITY"
    And the JSON node "confidential" should be true
    And an email should have been sent asynchronously with subject "MIS Project #6 (new project updated) updated"
    And this asynchronous email should be sent only to "user-cio@tld.fr, user-hr@tld.fr, user-sageparts@sageparts.com, user-sa@tld.fr, user-coo@tld.fr, user-cmo@tld.fr, user-qam@tld.fr, user-mpe@tld.fr, user-asm@tld.fr"

  Scenario: As CIO, I should be able to edit all properties of project
    Given I authenticate as the intranet user "user-cio@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/projects/6" with body:
        """
        {
          "name": "new project updated again",
          "teamsLink": "https://Mystery.com",
          "description": "this is the description of the new project updated again",
          "indicesFactor": "IF 10",
          "region": "/regions/2",
          "projectManager": "/people/13",
          "misOwner": "/people/100",
          "startedAt": "2025-05-29",
          "phases": [
            {
              "@id": "/mis/phases/26",
              "estimatedClosureAt": "2098-07-01",
              "revisedClosureAt": "2099-07-01",
              "estimatedHours": 10,
              "revisedEstimatedHours": 15
            },
            {
              "@id": "/mis/phases/27",
              "estimatedClosureAt": "2098-07-10",
              "revisedClosureAt": "2099-07-10",
              "estimatedHours": 10,
              "revisedEstimatedHours": 15
            },
            {
              "@id": "/mis/phases/28",
              "estimatedClosureAt": "2098-07-20",
              "revisedClosureAt": "2099-07-20",
              "estimatedHours": 10,
              "revisedEstimatedHours": 15
            },
            {
              "@id": "/mis/phases/29",
              "estimatedClosureAt": "2098-07-30",
              "revisedClosureAt": "2099-07-30",
              "estimatedHours": 10,
              "revisedEstimatedHours": 15
            },
            {
              "@id": "/mis/phases/30",
              "estimatedClosureAt": "2098-08-01",
              "revisedClosureAt": "2099-08-01",
              "estimatedHours": 10,
              "revisedEstimatedHours": 15
            }
          ],
          "estimatedHours": 150,
          "moduleKeyUsers": ["/people/7", "/people/8", "/people/9"],
          "misMembers": ["/people/4", "/people/5", "/people/6"],
          "module": "/modules/2",
          "confidential": true,
          "tags": ["/mis/project_tags/7"]
        }
        """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/project/schemas/project.json"
    And the JSON node "name" should be equal to the string "new project updated again"
    And the JSON node "teamsLink" should be equal to the string "https://Mystery.com"
    And the JSON node "description" should be equal to the string "this is the description of the new project updated again"
    And the JSON node "indicesFactor" should be equal to the string "IF 10"
    And the JSON node "region.@id" should be equal to the string "/regions/2"
    And the JSON node "projectManager.@id" should be equal to the string "/people/13"
    And the JSON node "misOwner.@id" should be equal to the string "/people/100"
    And the JSON node "startedAt" should be equal to the string "29.05.2025"
    And the JSON node "phases[0].estimatedClosureAt" should be equal to the string "2098-07-01T00:00:00-04:00"
    And the JSON node "phases[1].estimatedClosureAt" should be equal to the string "2098-07-10T00:00:00-04:00"
    And the JSON node "phases[2].estimatedClosureAt" should be equal to the string "2098-07-20T00:00:00-04:00"
    And the JSON node "phases[3].estimatedClosureAt" should be equal to the string "2098-07-30T00:00:00-04:00"
    And the JSON node "phases[4].estimatedClosureAt" should be equal to the string "2098-08-01T00:00:00-04:00"
    And the JSON node "phases[0].estimatedHours" should be equal to 10
    And the JSON node "phases[1].estimatedHours" should be equal to 10
    And the JSON node "phases[2].estimatedHours" should be equal to 10
    And the JSON node "phases[2].estimatedHours" should be equal to 10
    And the JSON node "phases[4].estimatedHours" should be equal to 10
    And the JSON node "phases[0].revisedEstimatedHours" should be equal to 15
    And the JSON node "phases[1].revisedEstimatedHours" should be equal to 15
    And the JSON node "phases[2].revisedEstimatedHours" should be equal to 15
    And the JSON node "phases[3].revisedEstimatedHours" should be equal to 15
    And the JSON node "phases[4].revisedEstimatedHours" should be equal to 15
    And the JSON node "phases[0].revisedClosureAt" should be equal to the string "2099-07-01T00:00:00-04:00"
    And the JSON node "phases[1].revisedClosureAt" should be equal to the string "2099-07-10T00:00:00-04:00"
    And the JSON node "phases[2].revisedClosureAt" should be equal to the string "2099-07-20T00:00:00-04:00"
    And the JSON node "phases[3].revisedClosureAt" should be equal to the string "2099-07-30T00:00:00-04:00"
    And the JSON node "phases[4].revisedClosureAt" should be equal to the string "2099-08-01T00:00:00-04:00"
    And the JSON node "moduleKeyUsers[0].@id" should be equal to the string "/people/7"
    And the JSON node "moduleKeyUsers[1].@id" should be equal to the string "/people/8"
    And the JSON node "moduleKeyUsers[2].@id" should be equal to the string "/people/9"
    And the JSON node "misMembers[0].@id" should be equal to the string "/people/4"
    And the JSON node "misMembers[1].@id" should be equal to the string "/people/5"
    And the JSON node "misMembers[2].@id" should be equal to the string "/people/6"
    And the JSON node "module.@id" should be equal to the string "/modules/2"
    And the JSON node "estimatedHours" should be equal to the string "50"
    And the JSON node "revisedEstimatedHours" should be equal to the string "75"
    And the JSON node "tags[0].name" should be equal to the string "SECURITY"
    And the JSON node "confidential" should be true
    And an email should have been sent asynchronously with subject "MIS Project #6 (new project updated again) updated"
    And this asynchronous email should be sent only to "user-cio@tld.fr, user-hr@tld.fr, user-mism@tld.fr, representative_ellie@tlou.com, representative_abi@tld.com, representative_6_@loop.io, representative_7_@loop.io, representative_8_@loop.io, representative_9_@loop.io"

  Scenario: As MISM, I should be able to edit all properties of project too
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/projects/6" with body:
    """
    {
      "name": "new project updated again by MISM",
      "teamsLink": "https://Team-7.com",
      "indicesFactor": "IF 100"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/project/schemas/project.json"
    And the JSON node "name" should be equal to the string "new project updated again by MISM"
    And the JSON node "teamsLink" should be equal to the string "https://Team-7.com"
    And the JSON node "indicesFactor" should be equal to the string "IF 100"


  Scenario: As basic user (not part of project #6), I should be able to see all non confidential tasks even if it's linked to a confidential project
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/tasks"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/tasks.json"
    And the JSON node "hydra:member" should have 51 elements
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/tasks/59"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"

  Scenario: As basic user (not part of project #6), I should not be able to see confidential tasks
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/tasks/59" with body:
    """
    {
      "confidential": true
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "confidential" should be true
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/tasks/59"
    Then the response status code should be 404
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/tasks"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/tasks.json"
    And the JSON node "hydra:member" should have 50 elements

  Scenario: As user CIO, I should be able to see all tasks of MIS projects
    Given I authenticate as the intranet user "user-cio@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/tasks"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/tasks.json"
    And the JSON node "hydra:member" should have 51 elements
    Given I authenticate as the intranet user "user-cio@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/tasks/59"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"

  Scenario: As Project manager, MIS Owner or member of project I should be able to see all tasks of MIS Project I am part of
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/tasks"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/tasks.json"
    And the JSON node "hydra:member" should have 51 elements
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/tasks/59"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/tasks"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/tasks.json"
    And the JSON node "hydra:member" should have 51 elements
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/tasks/59"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"

  Scenario: As project manager, I should be able to close project lower or equal IF 100 and conclusion is mandatory (if all tasks of previous phase are closed)
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/6/close" with parameters:
      | key               | value              |
      | status            | CLOSED             |
      | conclusion        | closing            |
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to the string "status: At least one task of previous Phase is still open, please close it before changing status."
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "tasks/59/close" with parameters:
      | key          | value                       |
      | comment      | Here we go!                 |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "status" should be equal to the string "CLOSED"
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/6/close" with parameters:
      | key               | value              |
      | status            | CLOSED             |
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to the string "conclusion: This value should not be null."
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/6/close" with parameters:
      | key               | value              |
      | conclusion        | closing            |
      | status            | CLOSED             |
    Then the response status code should be 201
    And the JSON node "conclusion" should be equal to "closing"
    And the JSON node "status" should be equal to "CLOSED"
    And the JSON node "phases[4].revisedClosureAt" should be newer than 1 minute ago
    And an email should have been sent asynchronously with subject "MIS Project #6 (new project updated again by MISM) to CLOSED"
    And this asynchronous email should be sent only to "user-cio@tld.fr, user-mism@tld.fr, user-hr@tld.fr, representative_ellie@tlou.com, representative_abi@tld.com, representative_6_@loop.io, representative_7_@loop.io, representative_8_@loop.io, representative_9_@loop.io"

  Scenario: As project manager, I should not be able to close project greater than IF 100 and conclusion is mandatory, only CIO can do it
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/4/close" with parameters:
      | key               | value              |
      | status            | CLOSED             |
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-cio@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/4/close" with parameters:
      | key               | value              |
      | status            | CLOSED             |
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to the string "conclusion: This value should not be null."
    Given I authenticate as the intranet user "user-cio@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/4/close" with parameters:
      | key               | value              |
      | conclusion        | closing            |
      | status            | CLOSED             |
    Then the response status code should be 201
    And the JSON node "conclusion" should be equal to "closing"
    And the JSON node "status" should be equal to "CLOSED"
    And the JSON node "phases[4].revisedClosureAt" should be newer than 1 minute ago
    And an email should have been sent asynchronously with subject "MIS Project #4 (fourth project) to CLOSED"
    And this asynchronous email should be sent only to "user-superuser@tld.fr, user-basic@tld.fr, user-cio@tld.fr, user-sa@tld.fr, user-mis@tld.fr"

  @resetFileTable
  Scenario: As a basic user, I can't upload a file to a confidential mis project
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/1/files" with parameters:
      | key    | value     |
      | public | 1         |
      | file   | @file.doc |
    Then the response status code should be 403

  Scenario: As a basic user, I can upload a file to a non confidential mis project
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/2/files" with parameters:
      | key    | value     |
      | public | 1         |
      | file   | @file.doc |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: As a user cio, I can upload a file to a confidential mis project
    Given I authenticate as the intranet user "user-cio@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/1/files" with parameters:
      | key    | value     |
      | public | 1         |
      | file   | @file.doc |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: Upload an invalid file to a mis project
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/1/files" with file "file" "image.gif"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "files: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "files"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: Download a confidential mis project attached file as basic user should not be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/projects/1/files/2"
    Then the response status code should be 404

  Scenario: Download a non confidential mis project attached file as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/projects/2/files/1"
    Then the response status code should be 200

  Scenario: Download a confidential mis project attached file as CIO should be possible
    Given I authenticate as the intranet user "user-cio@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/projects/1/files/2"
    Then the response status code should be 200

  Scenario: Download a mis project attached file as an extranet user should not be possible
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/projects/1/files/2"
    Then the response status code should be 404

  Scenario: Download a mis project attached file as a vendor user should not be possible
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/projects/1/files/2"
    Then the response status code should be 404

  Scenario: Download a mis project attached file as an authorized application should not be possible
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/projects/1/files/2"
    Then the response status code should be 404

  Scenario: As mis user I should be able to set a project in phase 0 to pending this should automatically close the task in phase 0
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/3/status" with parameters:
      | key               | value              |
      | comment           | test comment       |
      | status            | PHASE 0            |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/project/schemas/project.json"
    And an email should have been sent asynchronously with subject "Task #60 opened, for MOO-CAT meow by MIS user"
    And this asynchronous email should be sent only to "user-moo-cat@tld.fr"
    And an email should have been sent asynchronously with subject "Task #61 opened, for MOO-CAT meow by MIS user"
    And this asynchronous email should be sent only to "user-moo-cat@tld.fr"
    And an email should have been sent asynchronously with subject "MIS Project #3 (third project) to PHASE 0"

    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/mis/projects/3/status" with parameters:
      | key               | value              |
      | comment           | test comment       |
      | status            | PENDING            |
    Then the response status code should be 201

    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/tasks/60"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "status" should be equal to the string "CLOSED"

    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/tasks/60"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/task/schemas/task.json"
    And the JSON node "status" should be equal to the string "CLOSED"

  Scenario: Download excel project report should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/mis/projects?columns=id,name,region,indicesFactor,projectManager,misOwner,createdAt,startedAt,confidential,module,status,tags,dueDate,revisedDueDate,estimatedHours,revisedEstimatedHours"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Name | Region | Indices Factor| Project Manager | Mis Owner | Created At | Started At | Confidential | Module | Status | Tags | Due Date | Revised Due Date | Estimated Hours | Revised Estimated Hours |

