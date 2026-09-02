Feature: Test Extranet users group

  Scenario: Request all extranet users groups without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_groups"
    Then the response status code should be 401

  Scenario: Request a single extranet users groups without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_groups/1"
    Then the response status code should be 401

  Scenario: as a basic user you should be able to request all extranet user groups and see only public groups
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_groups"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user_group/schemas/extranet_user_groups.json"
    And the JSON node "hydra:totalItems" should be equal to 4

  Scenario: as superuser user you should be able to request all extranet user groups and see public and private groups
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_groups"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user_group/schemas/extranet_user_groups.json"
    And the JSON node "hydra:totalItems" should be equal to 5

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\ExtranetUserGroup" is exposed on the API
    Then the filter "order[id]" should be available and its type should be "string"

  Scenario: Request a single extranet user group
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_groups/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user_group/schemas/extranet_user_group.json"

  Scenario: A new extranet user group should be public by default
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/extranet_user_groups" with body:
    """
    {
        "name": "role_KIKOU",
        "description": "Role for KIKOUS"
    }
    """
    Then the response status code should be 201
    And the JSON node "public" should be true
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_groups"
    Then the JSON node "hydra:totalItems" should be equal to 6
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_groups"
    Then the JSON node "hydra:totalItems" should be equal to 5

  Scenario: A new extranet user group with public = false can be created
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/extranet_user_groups" with body:
    """
    {
        "name": "role_PRIVATE_KIKOU",
        "description": "Role for KIKOUS private",
        "public": false
    }
    """
    Then the response status code should be 201
    And the JSON node "public" should be false
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user_group/schemas/extranet_user_group.json"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_groups"
    Then the JSON node "hydra:totalItems" should be equal to 7
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_groups"
    Then the JSON node "hydra:totalItems" should be equal to 5


  Scenario: An extranet user group property "public" can be edited
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_user_groups/6" with body:
    """
    {
        "public": true
    }
    """
    Then the response status code should be 200
    And the JSON node "public" should be true
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_groups"
    Then the JSON node "hydra:totalItems" should be equal to 7
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_groups"
    Then the JSON node "hydra:totalItems" should be equal to 5
