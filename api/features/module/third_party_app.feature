Feature: Test third party apps

  Scenario: Filters are declared on resource business unit position
    Given the class "App\Entity\Module\ThirdPartyApp\BusinessUnitPosition" is exposed on the API
    Then the filter "thirdPartyApp" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"
    And the filter "order[position.description]" should be available and its type should be "string"
    And the filter "order[businessUnit.name]" should be available and its type should be "string"
    And the filter "columns" should be available and its type should be "string"

  Scenario: Filters are declared on resource update task
    Given the class "App\Entity\Module\ThirdPartyApp\UpdateTask" is exposed on the API
    Then the filter "thirdPartyApp" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"
    And the filter "order[user.lastname]" should be available and its type should be "string"
    And the filter "order[user.firstname]" should be available and its type should be "string"
    And the filter "order[user.businessUnit.name]" should be available and its type should be "string"
    And the filter "order[createdAt]" should be available and its type should be "string"
    And the filter "order[updatedAt]" should be available and its type should be "string"
    And the filter "order[updatedBy.lastname]" should be available and its type should be "string"
    And the filter "order[originType]" should be available and its type should be "string"
    And the filter "order[demandType]" should be available and its type should be "string"
    And the filter "user" should be available and its type should be "string"
    And the filter "done" should be available and its type should be "bool"
    And the filter "confirmed" should be available and its type should be "bool"
    And the filter "updatedBy" should be available and its type should be "string"
    And the filter "originType" should be available and its type should be "string"
    And the filter "demandType" should be available and its type should be "string"
    And the filter "user.businessUnit" should be available and its type should be "string"

  Scenario: Request a single third party app extended
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/modules/38"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module/module/schemas/module.json"
    And the JSON node "name" should be equal to the string "TPA Extended"

  Scenario: Edit a third party app
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/38" with body:
    """
    {
      "sso": false,
      "mfaUser": false,
      "mfaAdmin": false
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module/module/schemas/module.json"
    And the JSON node "sso" should be false
    And the JSON node "mfaUser" should be false
    And the JSON node "mfaAdmin" should be false

  Scenario: Add a business unit/position
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/third_party_app/business_unit_positions" with body:
    """
    {
      "thirdPartyApp": "/modules/38",
      "position": "/positions/14",
      "businessUnit": "/business_units/1"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/business_unit_position.json"

  Scenario: Export business unit position XLS file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "modules/third_party_app/38/business_unit_positions?columns=id,businessUnit,position"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Business Unit | Position |
    And the xlsx file should have 2 lines

  Scenario: Add admin member should not be possible as user with no permissions
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/third_party_app/members" with body:
    """
    {
      "user": "/people/35",
      "thirdPartyApp": "/modules/38"
    }
    """
    Then the response status code should be 403

  Scenario: Add admin member
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/third_party_app/members" with body:
    """
    {
      "user": "/people/35",
      "thirdPartyApp": "/modules/38"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/member.json"

  Scenario: Remove admin member as user with no permissions should not be possible
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/modules/third_party_app/members/3"
    Then the response status code should be 403

  Scenario: Remove admin member as superuser should be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/modules/third_party_app/members/3"
    Then the response status code should be 204

  #Putting back the one created before for tests
  Scenario: Add admin member
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/third_party_app/members" with body:
    """
    {
      "user": "/people/35",
      "thirdPartyApp": "/modules/38"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/member.json"

  Scenario: Edit admin member should not be possible as user with no permissions
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/third_party_app/members/4" with body:
    """
    {
      "user": "/people/36",
      "thirdPartyApp": "/modules/38"
    }
    """
    Then the response status code should be 403

  Scenario: Edit admin member as superuser should be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/third_party_app/members/4" with body:
    """
    {
      "user": "/people/36",
      "thirdPartyApp": "/modules/38"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/member.json"

  Scenario: Check update tasks previously created when add a business unit/position
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/modules/third_party_app/update_tasks?thirdPartyApp=/modules/38&order[user.lastname]=ASC&order[user.firstname]=ASC"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/update_tasks.json"
    And the JSON node "hydra:totalItems" should be equal to 12
    And the JSON node "hydra:member[2].createdBy.username" should be equal to "user-superuser@tld.fr"
    And the JSON node "hydra:member[2].user.username" should be equal to "juste-arriver@tld.fr"
    And the JSON node "hydra:member[2].thirdPartyApp.@id" should be equal to "/extendeds/38"
    And the JSON node "hydra:member[1].user.username" should be equal to "jean-arrive-dans-2-jours@tld.fr"
    And the JSON node "hydra:member[2].comment" should be equal to "test comment update task"

  Scenario: Confirm GRANT update task
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/third_party_app/update_tasks/1" with body:
    """
    {
      "done": true,
      "confirmed": true
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/update_task.json"
    And the JSON node "done" should be true
    And the JSON node "confirmed" should be true

  Scenario: Deny GRANT update task
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/third_party_app/update_tasks/8" with body:
    """
    {
      "done": true,
      "confirmed": false
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/update_task.json"
    And the JSON node "done" should be true
    And the JSON node "confirmed" should be false

  Scenario: Convert module to third party app light
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/34/convert/third_party_app_light" with body:
    """
    {}
    """
    Then the response status code should be 200
    And the JSON node "@id" should be equal to "/lights/34"
    And the JSON node "@type" should be equal to "Light"

  Scenario: Convert module to third party app extended
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/34/convert/third_party_app_extended" with body:
    """
    {}
    """
    Then the response status code should be 200
    And the JSON node "@id" should be equal to "/extendeds/34"
    And the JSON node "@type" should be equal to "Extended"

  Scenario: As basic user I should be able to request all extended modules
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/extendeds"
    And the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module/extended/schemas/extendeds.json"

  Scenario: As basic user I should be able to request one extended module
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/extendeds/38"
    And the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module/extended/schemas/extended.json"

  Scenario: As basic user, I should not have permissions to create extended module
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/third_party_app/extended" with body:
    """
    {
      "name": "Extended Test",
      "shortDescription": "Extended for test",
      "fullDescription": "Extended for test full",
      "operationalOwner": "/people/14"
    }
    """
    Then the response status code should be 403

  Scenario: As superuser, I should have permissions to create extended module
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/third_party_app/extended" with body:
    """
    {
      "name": "Extended Test",
      "shortDescription": "Extended for test",
      "fullDescription": "Extended for test full",
      "operationalOwner": "/people/14"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/module/extended/schemas/extended.json"
    And the JSON node "name" should be equal to the string "Extended Test"
    And the JSON node "shortDescription" should be equal to the string "Extended for test"
    And the JSON node "fullDescription" should be equal to the string "Extended for test full"
    And the JSON node "operationalOwner.@id" should be equal to the string "/people/14"

  Scenario: As basic user, I should not have permissions to create light module
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/lights" with body:
    """
    {
      "name": "Light Test",
      "shortDescription": "Light for test"
    }
    """
    Then the response status code should be 403

  Scenario: As superuser, I should have permissions to create light module
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/lights" with body:
    """
    {
      "name": "Light Test",
      "shortDescription": "Light for test",
      "fullDescription": "Light for test full",
      "operationalOwner": "/people/14"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/module/light/schemas/light.json"
    And the JSON node "name" should be equal to the string "Light Test"
    And the JSON node "shortDescription" should be equal to the string "Light for test"
    And the JSON node "fullDescription" should be equal to the string "Light for test full"
    And the JSON node "operationalOwner.@id" should be equal to the string "/people/14"

  Scenario: As basic user I should be able to request all lights modules
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/lights"
    And the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module/light/schemas/lights.json"

  Scenario: As basic user I should be able to request one extended module
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/lights/38"
    And the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module/light/schemas/light.json"

  Scenario: As superuser, I can add user to whitelist
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/extendeds/38"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module/extended/schemas/extended.json"
    And the JSON node "whitelistedUsers" should have 0 element
    And the JSON node "blacklistedUsers" should have 0 element
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/whitelist/add" with body:
    """
    {
      "user": "/people/15",
      "module": "/extendeds/38"
    }
    """
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/extendeds/38"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module/extended/schemas/extended.json"
    And the JSON node "whitelistedUsers" should have 1 element
    And the JSON node "whitelistedUsers[0].@id" should be equal to the string "/people/15"
    And the JSON node "blacklistedUsers" should have 0 element

  Scenario: As basic user, I can't add user to whitelist
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/whitelist/add" with body:
    """
    {
      "user": "/people/16",
      "module": "/extendeds/38"
    }
    """
    Then the response status code should be 403

  Scenario: As basic user, I can't remove user to whitelist
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/whitelist/remove" with body:
    """
    {
      "user": "/people/15",
      "module": "/extendeds/38"
    }
    """
    Then the response status code should be 403

  Scenario: As superuser, I can remove user to whitelist
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/whitelist/remove" with body:
    """
    {
      "user": "/people/15",
      "module": "/extendeds/38"
    }
    """
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/extendeds/38"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module/extended/schemas/extended.json"
    And the JSON node "whitelistedUsers" should have 0 element
    And the JSON node "blacklistedUsers" should have 0 element

  Scenario: As basic user, I can't add user to blacklist
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/blacklist/add" with body:
    """
    {
      "user": "/people/15",
      "module": "/extendeds/38"
    }
    """
    Then the response status code should be 403

  Scenario: As basic user, I can't remove user to blacklist
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/blacklist/remove" with body:
    """
    {
      "user": "/people/15",
      "module": "/extendeds/38"
    }
    """
    Then the response status code should be 403

  Scenario: As superuser, I can add user to blacklist
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/blacklist/add" with body:
    """
    {
      "user": "/people/15",
      "module": "/extendeds/38"
    }
    """
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/extendeds/38"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module/extended/schemas/extended.json"
    And the JSON node "whitelistedUsers" should have 0 element
    And the JSON node "blacklistedUsers" should have 1 element
    And the JSON node "blacklistedUsers[0].@id" should be equal to the string "/people/15"

  Scenario: As superuser, I can remove user to blacklist
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/blacklist/remove" with body:
    """
    {
      "user": "/people/15",
      "module": "/extendeds/38"
    }
    """
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/extendeds/38"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module/extended/schemas/extended.json"
    And the JSON node "whitelistedUsers" should have 0 element
    And the JSON node "blacklistedUsers" should have 0 element

  Scenario: As superuser, I can move user from blacklist to whitelist, not possible for basic user
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/blacklist/add" with body:
    """
    {
      "user": "/people/15",
      "module": "/extendeds/38"
    }
    """
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/extendeds/38"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module/extended/schemas/extended.json"
    And the JSON node "whitelistedUsers" should have 0 element
    And the JSON node "blacklistedUsers" should have 1 element
    And the JSON node "blacklistedUsers[0].@id" should be equal to the string "/people/15"
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/whitelist/move" with body:
    """
    {
      "user": "/people/15",
      "module": "/extendeds/38"
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/whitelist/move" with body:
    """
    {
      "user": "/people/15",
      "module": "/extendeds/38"
    }
    """
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/extendeds/38"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module/extended/schemas/extended.json"
    And the JSON node "whitelistedUsers" should have 1 element
    And the JSON node "whitelistedUsers[0].@id" should be equal to the string "/people/15"
    And the JSON node "blacklistedUsers" should have 0 element

  Scenario: As superuser, I can move user from whitelist to blacklist, not possible for basic user
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/blacklist/move" with body:
    """
    {
      "user": "/people/15",
      "module": "/extendeds/38"
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/blacklist/move" with body:
    """
    {
      "user": "/people/15",
      "module": "/extendeds/38"
    }
    """
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/extendeds/38"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module/extended/schemas/extended.json"
    And the JSON node "whitelistedUsers" should have 0 element
    And the JSON node "blacklistedUsers" should have 1 element
    And the JSON node "blacklistedUsers[0].@id" should be equal to the string "/people/15"

  Scenario: As superuser, I can't add user to whitelist if blacklisted
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/whitelist/add" with body:
    """
    {
      "user": "/people/15",
      "module": "/extendeds/38"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to "User is blacklisted"

  Scenario: As superuser, I can't add user to blacklist if whitelisted
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/whitelist/move" with body:
    """
    {
      "user": "/people/15",
      "module": "/extendeds/38"
    }
    """
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/blacklist/add" with body:
    """
    {
      "user": "/people/15",
      "module": "/extendeds/38"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to "User is whitelisted"

  Scenario: As a standard user, I can't update update tasks
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/third_party_app/update_tasks/1" with body:
    """
    {
      "done": true
    }
    """
    Then the response status code should be 403

  Scenario: As a superuser I can promote member to admin
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/third_party_app/members/4" with body:
    """
    {
      "admin": true
    }
    """
    Then the response status code should be 200
    And the JSON node "admin" should be true

  Scenario: As an admin, I can confirmed update task
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/third_party_app/update_tasks/1" with body:
    """
    {
      "done": true
    }
    """
    Then the response status code should be 200
    And the JSON node "done" should be true

  Scenario: Create remove access when disable user who is member of third party app
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/modules/third_party_app/update_tasks?user=36"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 0
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/36" with body:
    """
    {
      "disabled": true
    }
    """
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/modules/third_party_app/update_tasks?user=36"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0].demandType" should be equal to "REMOVE_ACCESS"
    And the JSON node "hydra:member[0].thirdPartyApp.id" should be equal to "38"

  Scenario: As superuser, I can add user coming soon to whitelist
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/extendeds/34"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module/extended/schemas/extended.json"
    And the JSON node "whitelistedUsers" should have 0 element
    And the JSON node "blacklistedUsers" should have 0 element
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/whitelist/add" with body:
    """
    {
      "user": "/people/108",
      "module": "/extendeds/34"
    }
    """
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/extendeds/34"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module/extended/schemas/extended.json"
    And the JSON node "whitelistedUsers" should have 1 elements
    And the JSON node "whitelistedUsers[0].@id" should be equal to the string "/people/108"
    And the JSON node "blacklistedUsers" should have 0 element

  Scenario: As superuser, I can remove user coming soon to whitelist
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/whitelist/remove" with body:
    """
    {
      "user": "/people/108",
      "module": "/extendeds/34"
    }
    """
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/extendeds/34"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module/extended/schemas/extended.json"
    And the JSON node "whitelistedUsers" should have 0 element
    And the JSON node "blacklistedUsers" should have 0 element

  Scenario: As superuser, I can add user coming soon to blacklist
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/blacklist/add" with body:
    """
    {
      "user": "/people/108",
      "module": "/extendeds/34"
    }
    """
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/extendeds/34"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module/extended/schemas/extended.json"
    And the JSON node "whitelistedUsers" should have 0 element
    And the JSON node "blacklistedUsers" should have 1 element
    And the JSON node "blacklistedUsers[0].@id" should be equal to the string "/people/108"

  Scenario: As superuser, I can remove user coming soon to blacklist
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/blacklist/remove" with body:
    """
    {
      "user": "/people/108",
      "module": "/extendeds/34"
    }
    """
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/extendeds/34"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module/extended/schemas/extended.json"
    And the JSON node "whitelistedUsers" should have 0 element
    And the JSON node "blacklistedUsers" should have 0 element

  Scenario: Validate update task with comment
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/modules/third_party_app/update_tasks/4" with body:
    """
    {
      "done": true,
      "confirmed": true,
      "comment": "new comment on update task"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/update_task.json"
    And the JSON node "comment" should be equal to "new comment on update task"

  Scenario: As super user I test if the user can be moved back from the blacklist with the move_from api
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/blacklist/move" with body:
    """
    {
      "user": "/people/31",
      "module": "/extendeds/38"
    }
    """
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "modules/38"
    Then the response status code should be 200
    And the JSON node "blacklistedUsers" should have 1 element
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/blacklist/move_from" with body:
    """
    {
      "user": "/people/31",
      "module": "/extendeds/38"
    }
    """
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "modules/38"
    Then the response status code should be 200
    And the JSON node "blacklistedUsers" should have 0 element



