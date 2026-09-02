Feature: Test members of third party app extended

  Scenario: Filters are declared on resource member
    Given the class "App\Entity\Module\ThirdPartyApp\Member" is exposed on the API
    Then the filter "thirdPartyApp" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"
    And the filter "order[user.lastname]" should be available and its type should be "string"
    And the filter "order[user.firstname]" should be available and its type should be "string"
    And the filter "order[admin]" should be available and its type should be "string"
    And the filter "order[user.businessUnit.name]" should be available and its type should be "string"
    And the filter "order[user.position.description]" should be available and its type should be "string"
    And the filter "thirdPartyApp" should be available and its type should be "string"
    And the filter "admin" should be available and its type should be "bool"
    And the filter "user" should be available and its type should be "string"
    And the filter "user.firstname" should be available and its type should be "string"
    And the filter "user.lastname" should be available and its type should be "string"
    And the filter "user.businessUnit" should be available and its type should be "string"
    And the filter "user.position" should be available and its type should be "string"
    And the filter "columns" should be available and its type should be "string"

  Scenario: Export members XLS file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "modules/third_party_app/38/members?columns=id,user,admin&user.lastname=martin"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | User | Admin |
    And the xlsx file should have 2 lines

  Scenario: Add a member to a third party app
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/third_party_app/members" with body:
    """
    {
      "user": "/people/31",
      "thirdPartyApp": "/modules/38",
      "admin": false
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/member.json"
    And the JSON node "thirdPartyApp.name" should be equal to the string "TPA Extended"
    And the JSON node "user.username" should be equal to the string "user-asm@tld.fr"
    And the JSON node "admin" should be false

  Scenario: Get members of third party app for basic users
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/modules/third_party_app/members"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/members.json"
    And the JSON node "hydra:totalItems" should be equal to "4"
    And the JSON node "hydra:member[0].user.username" should be equal to "arnie-i-will-be-back@tld.fr"

  Scenario: Remove member
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/modules/third_party_app/members/1"
    Then the response status code should be 204

  Scenario: Add admin
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/third_party_app/members" with body:
    """
    {
      "user": "/people/31",
      "thirdPartyApp": "/modules/38",
      "admin": true
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/member.json"
    And the JSON node "thirdPartyApp.name" should be equal to the string "TPA Extended"
    And the JSON node "user.username" should be equal to the string "user-asm@tld.fr"
    And the JSON node "admin" should be true

  Scenario: This user should be able to add new member
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/third_party_app/members" with body:
    """
    {
      "user": "/people/32",
      "thirdPartyApp": "/modules/38",
      "admin": false
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/member.json"
    And the JSON node "thirdPartyApp.name" should be equal to the string "TPA Extended"
    And the JSON node "user.username" should be equal to the string "user-sa@tld.fr"
    And the JSON node "admin" should be false

  Scenario: Basic user should not be able to add a member
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/third_party_app/members" with body:
    """
    {
      "user": "/people/32",
      "thirdPartyApp": "/modules/38",
      "admin": false
    }
    """
    Then the response status code should be 403