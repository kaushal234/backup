  Feature: Test Audit Log entity

  Scenario: Audit Logs should be accessible only for intranet users
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "audit_logs?auditType=trouble_ticket&property=status"
    Then the response status code should be 403
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "audit_logs?auditType=trouble_ticket&property=status"
    Then the response status code should be 403

  Scenario: Audit Logs should not be accessible on item route
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "audit_logs/1?auditType=trouble_ticket&property=status"
    Then the response status code should be 403
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "audit_logs/1?auditType=trouble_ticket&property=status"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "audit_logs/1?auditType=trouble_ticket&property=status"
    Then the response status code should be 404

  Scenario: Audit Logs should be accessible to intranet users but filters are mandatory
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "audit_logs"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Filter Audit Type is mandatory on this route."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "audit_logs?auditType=trouble_ticket"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Filter Property is mandatory on this route."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "audit_logs?auditType=trouble_ticket&property=status"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/audit_log/schemas/audit_logs.json"

  Scenario: Audit Logs by month should be accessible to intranet users but filters are mandatory
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "audit_logs/by_month"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Filter Audit Type is mandatory on this route."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "audit_logs/by_month?auditType=trouble_ticket"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Filter Property is mandatory on this route."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "audit_logs/by_month?auditType=trouble_ticket&property=status"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/audit_log/schemas/audit_logs.json"
    And the JSON should be valid according to the schema "tests/fixtures/json/audit_log/schemas/audit_logs.json"

  Scenario: Audit Logs Time should be accessible to intranet users but filters are mandatory
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "audit_logs/time"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Filter Audit Type is mandatory on this route."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "audit_logs/time?auditType=trouble_ticket"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Filter Property is mandatory on this route."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "audit_logs/time?auditType=trouble_ticket&property=status"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/audit_log/schemas/audit_logs.json"

  Scenario: Audit Logs by reference should be accessible to intranet users but filters are mandatory
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "audit_logs/by_reference"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Filter Reference ID is mandatory on this route."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "audit_logs/by_reference?referenceId=10"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Filter Audit Type is mandatory on this route."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "audit_logs/by_reference?auditType=trouble_ticket&referenceId=10"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Filter Property is mandatory on this route."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "audit_logs/by_reference?auditType=trouble_ticket&property=status&referenceId=10"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/audit_log/schemas/audit_logs.json"

  Scenario: Audit Logs time by reference should be accessible to intranet users but filters are mandatory
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "audit_logs/time_by_reference"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Filter Reference ID is mandatory on this route."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "audit_logs/time_by_reference?referenceId=10"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Filter Audit Type is mandatory on this route."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "audit_logs/time_by_reference?auditType=trouble_ticket&referenceId=10"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Filter Property is mandatory on this route."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "audit_logs/time_by_reference?auditType=trouble_ticket&property=status&referenceId=10"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/audit_log/schemas/audit_logs.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\AuditLog" is exposed on the API
    Then the filter "auditType" should be available and its type should be "string"
    Then the filter "property" should be available and its type should be "string"
    Then the filter "referenceId" should be available and its type should be "int"
    Then the filter "order[id]" should be available and its type should be "string"