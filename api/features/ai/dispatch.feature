Feature: Test endpoints of Dispatch + AI Files

  Scenario: Create an AI Log should not be possible for extranet or evendors or unauthenticated users
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/ai/dispatch"
    Then the response status code should be 401
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/ai/dispatch"
    Then the response status code should be 403
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/ai/dispatch"
    Then the response status code should be 403

  Scenario: Use dispatch route should be possible for basic users
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/ai/dispatch" with parameters:
    | key    |  value       |
    | input  | Hello        |
    | log    | /ai_logs/1   |
    Then the response status code should be 201

  Scenario: Use dispatch route with scanned pdf should be possible for basic users
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/ai/dispatch" with parameters:
    | key    |  value               |
    | input  | Summarize this file  |
    | log    | /ai_logs/1           |
    | file   | @scanned_file.pdf    |
    Then the response status code should be 201
