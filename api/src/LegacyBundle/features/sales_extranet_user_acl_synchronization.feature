Feature: Test extranet users roles double write API

  Scenario: Create an extranet user role in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/extranet_user_acls" with body:
    """
    {
      "extranetUser": "/sales/extranet_users/249",
      "crt": "/sales/customer_relationship_teams/7",
      "extranetUserGroup": "/sales/extranet_user_groups/2"
    }
    """
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "extranet_users_roles"
    And a new row has been inserted in the legacy table "mod_logs"

