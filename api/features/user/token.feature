Feature: Test token creation

  Scenario: Try to create a token with no credentials
    Given I send a "POST" request to "/token"
    Then the response status code should be 400

  Scenario: Try to create a token with bad credentials
    Given I send a "POST" request to "/token" with parameters:
      | key      | value    |
      | username | toto     |
      | password | bad      |
      | portal   | intranet |
    Then the response status code should be 401

  Scenario: Try to create a token with valid credentials but no portal
    Given I send a "POST" request to "/token" with parameters:
      | key      | value             |
      | username | user-basic@tld.fr |
      | password | P@ssw0rd15chars   |
    Then the response status code should be 401

  Scenario: Try to create a token with valid credentials but wrong portal
    Given I send a "POST" request to "/token" with parameters:
      | key      | value             |
      | username | user-basic@tld.fr |
      | password | P@ssw0rd15chars   |
      | portal   | extranet          |
    Then the response status code should be 401

  Scenario: Try to create a token for guest user
    Given I send a "POST" request to "/token" with parameters:
      | key      | value             |
      | username | user-guest@guest.fr |
      | password | P@ssw0rd15chars   |
      | portal   | intranet          |
    Then the response status code should be 401
    Given I send a "POST" request to "/token" with parameters:
      | key      | value             |
      | username | user-guest@guest.fr |
      | password | P@ssw0rd15chars   |
      | portal   | extranet          |
    Then the response status code should be 401
    Given I send a "POST" request to "/token" with parameters:
      | key      | value             |
      | username | user-guest@guest.fr |
      | password | P@ssw0rd15chars   |
      | portal   | evendors          |
    Then the response status code should be 401

  Scenario: Create a token with valid credentials for an intranet user
    Given I send a "POST" request to "/token" with parameters:
      | key      | value             |
      | username | user-basic@tld.fr |
      | password | P@ssw0rd15chars   |
      | portal   | intranet          |
    Then the response status code should be 200
    And the JSON node "token" should exist
    Then the JWT token node "company" should not exist
    Then the JWT token node "username" should be equal to "user-basic@tld.fr"
    Then the JWT token node "firstname" should be equal to "user"
    Then the JWT token node "lastname" should be equal to "BASIC"
    Then the JWT token node "portal" should be equal to "intranet"
    Then the JWT token node "@type" should be equal to "People"
    Then the JWT token node "@id" should be equal to "/people/11"
    Then the JWT token node "hidden" should be false
    Then the JWT token node "disabled" should be false
    Then the JWT token node "roles" should contain 1 element
    Then the JWT token node "roles[0]" should be equal to "ROLE_PASSWORD_NOT_EXPIRED"
    Then the JWT token node "acls" should contain 5 elements
    Then the JWT token node "acls[0]" should be equal to "ACL_AUTH_INTRANET"
    Then the JWT token node "acls[1]" should be equal to "group_FOR_PEOPLE_FILTER"
    Then the JWT token node "acls[2]" should be equal to "group_FOR_PEOPLE_FILTER_14"
    Then the JWT token node "acls[3]" should be equal to "group_FOR_PEOPLE_FILTER_WITH_FEATURE"
    Then the JWT token node "acls[4]" should be equal to "group_FOR_PEOPLE_FILTER_WITH_FEATURE_13"
    Then the JWT token node "businessUnit[@id]" should be equal to "/business_units/2"
    Then the JWT token node "businessUnit[@type]" should be equal to "BusinessUnit"
    Then the JWT token node "businessUnit[id]" should be equal to "2"
    Then the JWT token node "businessUnit[name]" should be equal to "business_unit_2"

  Scenario: Create a token with valid credentials for an intranet user with a username different of the email
    Given I send a "POST" request to "/token" with parameters:
      | key      | value             |
      | username | flore.shop        |
      | password | P@ssw0rd15chars   |
      | portal   | intranet          |
    Then the response status code should be 200
    And the JSON node "token" should exist
    Then the JWT token node "username" should be equal to "flore.shop"
    Then the JWT token node "@id" should be equal to "/people/73"
    Then the JWT token node "acls" should contain 0 element

  Scenario: Create a token with valid credentials for an extranet user
    Given I send a "POST" request to "/token" with parameters:
      | key      | value                 |
      | username | julien.lepers@tld.com |
      | password | P@ssw0rd15chars       |
      | portal   | extranet              |
    Then the response status code should be 200
    And the JSON node "token" should exist
    Then the JWT token node "acls" should not exist
    Then the JWT token node "company" should not exist
    Then the JWT token node "username" should be equal to "julien.lepers@tld.com"
    Then the JWT token node "portal" should be equal to "extranet"
    Then the JWT token node "@type" should be equal to "ExtranetUser"
    Then the JWT token node "@id" should be equal to "/sales/extranet_users/200"
    Then the JWT token node "hidden" should be false
    Then the JWT token node "disabled" should be false
    Then the JWT token node "roles" should contain 1 element
    Then the JWT token node "roles[0]" should be equal to "ROLE_PASSWORD_NOT_EXPIRED"

  Scenario: Create a token with valid credentials and expired account
    Given I send a "POST" request to "/token" with parameters:
      | key      | value               |
      | username | user-expired@tld.fr |
      | password | P@ssw0rd15chars     |
      | portal   | intranet            |
    Then the response status code should be 200
    And the JSON node "token" should exist
    Then the response status code should be 200
    And the JSON node "token" should exist
    Then the JWT token node "company" should not exist
    Then the JWT token node "username" should be equal to "user-expired@tld.fr"
    Then the JWT token node "portal" should be equal to "intranet"
    Then the JWT token node "@id" should be equal to "/people/14"
    Then the JWT token node "hidden" should be false
    Then the JWT token node "disabled" should be false
    Then the JWT token node "roles" should contain 0 element
