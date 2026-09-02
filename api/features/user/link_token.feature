Feature: Test link token creation

  Scenario: Try to create a token for link with bad credentials
    Given I send a "POST" request to "/token" with parameters:
      | key      | value                |
      | username | user-link-eng@tld.fr |
      | password | bad                  |
      | portal   | link                 |
    Then the response status code should be 401

  Scenario: Create a token for link with valid credentials for an intranet user with right permissions
    Given I send a "POST" request to "/token" with parameters:
      | key      | value                |
      | username | user-link-eng@tld.fr |
      | password | P@ssw0rd15chars      |
      | portal   | link                 |
    Then the response status code should be 200
    Given I send a "POST" request to "/token" with parameters:
      | key      | value                 |
      | username | user-link-prod@tld.fr |
      | password | P@ssw0rd15chars       |
      | portal   | link                  |
    Then the response status code should be 200
    Given I send a "POST" request to "/token" with parameters:
      | key      | value                              |
      | username | user-link-sso-commissioning@tld.fr |
      | password | P@ssw0rd15chars                    |
      | portal   | link                               |
    Then the response status code should be 200
    Given I send a "POST" request to "/token" with parameters:
      | key      | value                    |
      | username | user-link-sso-ast@tld.fr |
      | password | P@ssw0rd15chars          |
      | portal   | link                     |
    Then the response status code should be 200

  Scenario: Create a token for link with valid credentials but no link permissions
    Given I send a "POST" request to "/token" with parameters:
      | key      | value             |
      | username | user-basic@tld.fr |
      | password | P@ssw0rd15chars   |
      | portal   | link              |
    Then the response status code should be 401
