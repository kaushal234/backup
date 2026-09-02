Feature: Test Extranet users roles

  Scenario: Request all extranet users roles without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_acls"
    Then the response status code should be 401

  Scenario: Request a single extranet users roles without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_acls/1"
    Then the response status code should be 401

  Scenario: Request all extranet users roles
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_acls"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user_acl/schemas/extranet_user_acls.json"

  Scenario: Request all extranet users roles from an xu should only expose his own acls
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_acls"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user_acl/schemas/extranet_user_acls.json"
    And the JSON node "hydra:totalItems" should be equal to 3
    And the JSON node "hydra:member[0].extranetUser.username" should be equal to "julien.lepers@tld.com"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\ExtranetUserAcl" is exposed on the API
    Then the filter "extranetUser" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "order[id]" should be available and its type should be "string"
    And the filter "crt" should be available and its type should be "string"
    And the filter "normalizationGroups[]" should be available and its type should be "string"

  Scenario: Request a single extranet user role
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_acls/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user_acl/schemas/extranet_user_acl.json"

  Scenario: Delete an extranet user role should remove CRT linked if no other acl on CRT
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/extranet_user_acls/1"
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customer_relationship_teams/6"
    Then the response status code should be 404

  Scenario: Superuser can't update an extranet user role
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_user_acls/2" with body:
    """
    {
      "extranetUser": "/sales/extranet_users/250",
      "crt": "/sales/customer_relationship_teams/8",
      "extranetUserGroup": "/sales/extranet_user_groups/3"
    }
    """
    Then the response status code should be 405

  Scenario: Basic user can't add an extranet user role
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/extranet_user_acls" with body:
    """
    {
      "extranetUser": "/sales/extranet_users/249",
      "crt": "/sales/customer_relationship_teams/2",
      "extranetUserGroup": "/sales/extranet_user_groups/4"
    }
    """
    Then the response status code should be 403

  Scenario: Superuser can add an extranet user role, and an email is sent when eCustomer of CRT is different than the one on the contact profile
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/extranet_user_acls" with body:
    """
    {
      "extranetUser": "/sales/extranet_users/249",
      "crt": "/sales/customer_relationship_teams/2",
      "extranetUserGroup": "/sales/extranet_user_groups/2"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user_acl/schemas/extranet_user_acl.json"
    And an email should have been sent asynchronously with subject matching pattern "/SEQ #\S+: Sequence to approve extranet access on different eCustomer than the one set on the contact profile/"
    And this asynchronous email should be sent only "to" "user-superuser@tld.fr"

  Scenario: Superuser can't add an extranet user role that already exist
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/extranet_user_acls" with body:
    """
    {
      "extranetUser": "/sales/extranet_users/249",
      "crt": "/sales/customer_relationship_teams/2",
      "extranetUserGroup": "/sales/extranet_user_groups/2"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "extranetUser"
    And the JSON node "violations[0].message" should contain "This value is already used."
