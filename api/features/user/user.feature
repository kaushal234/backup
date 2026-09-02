Feature: Test users API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\User" should only be available for intranet user

  Scenario: Filters are declared on resource
    Given the class "App\Entity\User" is exposed on the API
    Then the filter "username" should be available and its type should be "string"
    And the filter "hidden" should be available and its type should be "bool"
    And the filter "disabled" should be available and its type should be "bool"
    And the filter "normalizationGroups[]" should be available and its type should be "string"

  Scenario: Request all users
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/users"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/user/schemas/users.json"

  Scenario: Request a user that does not exist
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/users/99999"
    Then the response status code should be 404

  Scenario: Request a user that does exist
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/users/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/user/schemas/user.json"

  Scenario: Basic users can't switch to an other user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/user-tokens/2"
    Then the response status code should be 403

  Scenario: Superuser can switch to an other user
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/user-tokens/2"
    Then the response status code should be 201
    And the JSON should be valid according to this schema:
    """
    {
      "type": "object",
      "properties": {
        "token": {"type": "string"}
      },
      "additionalProperties": false,
      "required": ["token"]
    }
    """
    Then the JWT token node "company" should not exist
    Then the JWT token node "origin" should be equal to "user-superuser@tld.fr"
    Then the JWT token node "username" should be equal to "user-rep"
    Then the JWT token node "portal" should be equal to "intranet"
    Then the JWT token node "@type" should be equal to "People"
    Then the JWT token node "@id" should be equal to "/people/2"
    Then the JWT token node "hidden" should be false
    Then the JWT token node "disabled" should be false
    Then the JWT token node "roles" should contain 2 element
    Then the JWT token node "roles[0]" should be equal to "ROLE_IMPERSONATED"
    Then the JWT token node "roles[1]" should be equal to "ROLE_PASSWORD_NOT_EXPIRED"
    Then the JWT token node "acls" should contain 0 element
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/comments?resource=/people/2&order[createdAt]=DESC"
    Then the JSON node "hydra:member[0].message" should be equal to "SUPERUSER, user is impersonating REP, user"

  Scenario: User service can switch to an extranet user
    Given I authenticate as the intranet user "user-service@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/user-tokens/200"
    Then the response status code should be 201
    And the JSON should be valid according to this schema:
    """
    {
      "type": "object",
      "properties": {
        "token": {"type": "string"}
      },
      "additionalProperties": false,

      "required": ["token"]
    }
    """
    Then the JWT token node "company" should not exist
    Then the JWT token node "origin" should be equal to "user-service@tld.fr"
    Then the JWT token node "username" should be equal to "julien.lepers@tld.com"
    Then the JWT token node "portal" should be equal to "extranet"
    Then the JWT token node "@type" should be equal to "ExtranetUser"
    Then the JWT token node "@id" should be equal to "/sales/extranet_users/200"
    Then the JWT token node "hidden" should be false
    Then the JWT token node "disabled" should be false
    Then the JWT token node "roles" should contain 2 element
    Then the JWT token node "roles[0]" should be equal to "ROLE_IMPERSONATED"
    Then the JWT token node "roles[1]" should be equal to "ROLE_PASSWORD_NOT_EXPIRED"
    Then the JWT token node "acls" should not exist

  Scenario: User parts can switch to a vendor user
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/user-tokens/300"
    Then the response status code should be 201
    And the JSON should be valid according to this schema:
    """
    {
      "type": "object",
      "properties": {
        "token": {"type": "string"}
      },
      "additionalProperties": false,
      "required": ["token"]
    }
    """
    Then the JWT token node "company" should not exist
    Then the JWT token node "origin" should be equal to "user-parts@tld.fr"
    Then the JWT token node "username" should be equal to "vendor.user@vendor.fr"
    Then the JWT token node "portal" should be equal to "evendors"
    Then the JWT token node "@type" should be equal to "VendorUser"
    Then the JWT token node "@id" should be equal to "/purchasing/vendor_users/300"
    Then the JWT token node "hidden" should be false
    Then the JWT token node "disabled" should be false
    Then the JWT token node "roles" should contain 2 element
    Then the JWT token node "roles[0]" should be equal to "ROLE_IMPERSONATED"
    Then the JWT token node "roles[1]" should be equal to "ROLE_PASSWORD_NOT_EXPIRED"
    Then the JWT token node "acls" should not exist

  Scenario: Extranet user can't switch to a user
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/user-tokens/2"
    Then the response status code should be 403

  Scenario: User service can't switch to an intranet user
    Given I authenticate as the intranet user "user-service@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/user-tokens/2"
    Then the response status code should be 403

  Scenario: Superuser users can't switch back when no impersonification
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/user-tokens"
    Then the response status code should be 400
    And the response should contain "No previous user for switch exiting"

  Scenario: Last_login updated when user authenticates
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/users/12"
    Then the JSON node "lastLogin" should be newer than 1 minute ago

  Scenario: Last_login updated when extranet_user authenticates
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_users/200"
    Then the JSON node "lastLogin" should be newer than 1 minute ago

  Scenario: People username updated when email is changed
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/142" with body:
    """
    {
      "email": "user_omari42@hotmail.com"
    }
    """
    Then the response status code should be 200
    And the JSON node "email" should be equal to "user_omari42@hotmail.com"
    And the JSON node "username" should be equal to "user_omari42@hotmail.com"
