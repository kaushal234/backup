Feature: Test SageParts SSO Authentication

  Scenario: Send a valid token from Sage Parts with valid username
    When I send a "valid" Token with Username "user-sageparts@sageparts.com" to "/token-sage"
    Then the response status code should be 200
    And the JSON node token should exist
    And the JSON node username should exist
    Then the JWT token node "username" should be equal to "user-sageparts@sageparts.com"
    Then the JWT token node "portal" should be equal to "intranet"
    Then the JWT token node "@type" should be equal to "People"
    Then the JWT token node "@id" should be equal to "/people/15"
    Then the JWT token node "hidden" should be false
    Then the JWT token node "disabled" should be false
    Then the JWT token node "roles" should contain 2 elements
    Then the JWT token node "roles[0]" should be equal to "ROLE_PASSWORD_NOT_EXPIRED"
    Then the JWT token node "roles[1]" should be equal to "ROLE_PASSWORD_NOT_EXPIRING"

  Scenario: Send a valid token with invalid username
    When I send a "valid" Token with Username "user-basic@tld.fr" to "/token-sage"
    Then the response status code should be 401
    And the JSON node token should not exist

  Scenario: Send an expired token from Sage Parts
    When I send a "expired" Token with Username "user-sageparts@sageparts.com" to "/token-sage"
    Then the response status code should be 401
    And the JSON node token should not exist
