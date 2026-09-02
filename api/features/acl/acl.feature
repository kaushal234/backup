Feature: Test acls API

  Scenario: resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Acl" should only be available for intranet user

  Scenario: Acls should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acls"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acls/1"
    Then the response status code should be 403

  Scenario: Request all acls
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acls"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/acl/schemas/acls.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Acl" is exposed on the API
    Then the filter "location.name" should be available and its type should be "string"
    Then the filter "order[group.name]" should be available and its type should be "string"

  Scenario: Fetch all acls with restricted serialization
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/acls?normalization_groups_override[]=acl_list"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/acl/schemas/acls_override.json"

  Scenario: Request a single acl
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acls/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/acl/schemas/acl.json"

  Scenario: Adding an acl is not allowed
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "POST" request to "/acls"
    Then the response status code should be 405

  Scenario: Updating an acl is not allowed
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "PUT" request to "/acls"
    Then the response status code should be 405

  Scenario: A basic user can't delete an acl
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/acls/11"
    Then the response status code should be 403

  Scenario: A basic user can delete its own acl
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/acls/8"
    Then the response status code should be 204

  Scenario: A supervisor can delete its own team member's acls
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/acls/9"
    Then the response status code should be 204

  Scenario: A super user can delete an acl
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/acls/11"
    Then the response status code should be 204

    When I authenticate as the intranet user "user-superuser@tld.fr"
    Then I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acls/11"
    Then the response status code should be 404

  Scenario: I can filter acl by legacy id
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acls?legacyId=10197"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/acl/schemas/acls.json"

  Scenario: I can filter acl by user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acls?user=/people/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/acl/schemas/acls.json"

  Scenario: I can filter acl by feature name
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acls?group.features.name=FEATURE_TEST"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/acl/schemas/acls.json"

  Scenario: I can filter acl by group name
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acls?group.name=GG_HR"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/acl/schemas/acls.json"
