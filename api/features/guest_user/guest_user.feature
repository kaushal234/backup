Feature: Test Guest Users API

  Scenario: Filters are declared on resource
    Given the class "App\Entity\MIS\GuestUser\GuestUser" is exposed on the API
    Then the filter "order[lastname]" should be available and its type should be "string"
    Then the filter "order[firstname]" should be available and its type should be "string"
    Then the filter "order[id]" should be available and its type should be "string"
    Then the filter "order[businessUnit.name]" should be available and its type should be "string"
    Then the filter "order[position.code]" should be available and its type should be "string"
    Then the filter "order[supervisor.lastname]" should be available and its type should be "string"
    Then the filter "order[premise.name]" should be available and its type should be "string"
    Then the filter "disabled" should be available and its type should be "bool"
    Then the filter "hidden" should be available and its type should be "bool"
    Then the filter "hidden" should be available and its type should be "bool"
    Then the filter "premise" should be available and its type should be "string"
    Then the filter "businessUnit" should be available and its type should be "string"
    Then the filter "position" should be available and its type should be "string"
    Then the filter "supervisor" should be available and its type should be "string"

  Scenario: Guest user should be downloadable on excel format
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/guest_users?columns=id,lastname,firstname,supervisor,businessUnit,premise,enableAt,plannedDisableAt,hidden,disabled"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Lastname | Firstname | Supervisor | Business Unit | Premise | Enable At | Planned Disable At | Hidden | Disabled |

  Scenario: Request a guest user should be possible only for intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/guest_users/320"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/guest_user/schemas/guest_user.json"
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/guest_users/320"
    Then the response status code should be 403
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/guest_users/320"
    Then the response status code should be 403

  Scenario: Request all guest users should be possible only for intranet users
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/guest_users"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/guest_user/schemas/guest_users.json"
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/guest_users"
    Then the response status code should be 403
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/guest_users"
    Then the response status code should be 403

  Scenario: Create a guest user should be possible for basic user (if email address does not already exists)
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/guest_users" with body and replace "plannedDisableAt" by the date "now + 3 days":
    """
    {
      "lastname": "Mccalloway",
      "firstname": "Peter",
      "position": "/positions/22",
      "premise": "/premises/1",
      "email": "user-guest@guest.fr",
      "username": "user-guest@guest.fr",
      "businessUnit": "business_units/1",
      "plannedDisableAt": "1970-01-01",
      "enableAt": "2099-01-01"
    }
    """
    And the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string "This value is already used by another Guest User."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/guest_users" with body and replace "plannedDisableAt" by the date "now + 2 years":
    """
    {
      "lastname": "Mccalloway",
      "firstname": "Peter",
      "position": "/positions/22",
      "premise": "/premises/1",
      "email": "user-basic@tld.fr",
      "username": "user-basic@tld.fr",
      "businessUnit": "business_units/1",
      "plannedDisableAt": "1970-01-01",
      "enableAt": "2099-01-01"
    }
    """
    And the response status code should be 422
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/guest_users" with body and replace "plannedDisableAt" by the date "now + 3 months":
    """
    {
      "lastname": "Mccalloway",
      "firstname": "Peter",
      "premise": "/premises/1",
      "email": "user-basic@tld.fr",
      "username": "user-basic@tld.fr",
      "businessUnit": "business_units/1",
      "plannedDisableAt": "1970-01-01",
      "enableAt": "2099-01-01"
    }
    """
    And the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string "At least one module must be selected when collaboration access is not required."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/guest_users" with body and replace "plannedDisableAt" by the date "now + 3 days":
    """
    {
      "lastname": "Mccalloway",
      "firstname": "Peter",
      "premise": "/premises/1",
      "email": "user-basic@tld.fr",
      "username": "user-basic@tld.fr",
      "businessUnit": "business_units/1",
      "plannedDisableAt": "1970-01-01",
      "enableAt": "2099-01-01",
      "needsCollaborationAccess": true,
      "modules": [
          "\/extendeds\/38"
      ]
    }
    """
    And the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/guest_user/schemas/guest_user.json"
    And the JSON node "lastname" should be equal to the string "MCCALLOWAY"
    And the JSON node "firstname" should be equal to the string "Peter"
    And the JSON node "position.@id" should be equal to the string "/positions/22"
    And the JSON node "premise.@id" should be equal to the string "/premises/1"
    And the JSON node "businessUnit.@id" should be equal to the string "/business_units/1"
    And the JSON node "email" should be equal to the string "user-basic@tld.fr"
    And the JSON node "username" should be equal to the string "user-basic@tld.fr"
    And the JSON node "hidden" should be true
    And the JSON node "disabled" should be true
    And the JSON node "plannedDisableAt" should not be null
    And the JSON node "enableAt" should not be null
    And an email should have been sent asynchronously with subject matching pattern "/SEQ #\S+: Sequence for new Guest User/"
    And this asynchronous email should be sent only to "user-basic@tld.fr"

  Scenario: Only MIS and supervisor of guest user should be able to update it
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/guest_users/323" with body:
    """
    {}
    """
    And the response status code should be 403
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/guest_users/323" with body:
    """
    {
      "lastname": "update",
      "firstname": "test",
      "position": "/positions/22",
      "premise": "/premises/2",
      "email": "user-updated@tld.fr",
      "username": "user-updated@tld.fr",
      "businessUnit": "business_units/2",
      "enableAt": null,
      "disabled": false,
      "hidden": false
    }
    """
    And the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/guest_user/schemas/guest_user.json"
    And the JSON node "lastname" should be equal to the string "UPDATE"
    And the JSON node "firstname" should be equal to the string "test"
    And the JSON node "position.@id" should be equal to the string "/positions/22"
    And the JSON node "premise.@id" should be equal to the string "/premises/2"
    And the JSON node "businessUnit.@id" should be equal to the string "/business_units/2"
    And the JSON node "email" should be equal to the string "user-updated@tld.fr"
    And the JSON node "username" should be equal to the string "user-updated@tld.fr"
    And the JSON node "hidden" should be false
    And the JSON node "disabled" should be false
    And the JSON node "plannedDisableAt" should not be null
    And the JSON node "enableAt" should not be null
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/guest_users/323" with body:
    """
    {
      "lastname": "Mccalloway",
      "firstname": "Peter",
      "position": "/positions/22",
      "premise": "/premises/1",
      "email": "user-guest@tld.fr",
      "username": "user-guest@tld.fr",
      "businessUnit": "business_units/1",
      "disabled": true,
      "hidden": true
    }
    """
    And the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/guest_user/schemas/guest_user.json"
    And the JSON node "lastname" should be equal to the string "MCCALLOWAY"
    And the JSON node "firstname" should be equal to the string "Peter"
    And the JSON node "position.@id" should be equal to the string "/positions/22"
    And the JSON node "premise.@id" should be equal to the string "/premises/1"
    And the JSON node "businessUnit.@id" should be equal to the string "/business_units/1"
    And the JSON node "email" should be equal to the string "user-guest@tld.fr"
    And the JSON node "username" should be equal to the string "user-guest@tld.fr"
    And the JSON node "hidden" should be true
    And the JSON node "disabled" should be true
    And the JSON node "plannedDisableAt" should not be null

  Scenario: Delete a guest user should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/guest_users/320"
    Then the response status code should be 405

