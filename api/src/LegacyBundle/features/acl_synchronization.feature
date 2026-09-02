Feature: Test acl double write API

  Scenario: Delete an acl in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/acls/11"
    Then the response status code should be 204
    Then a row has been deleted in the legacy table "people_groups"

  Scenario: Superusers can import new acl
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/people/20/import_acl" with body:
    """
     {
       "acls": ["/acls/1", "/acls/2", "/acls/3"]
     }
    """
    Then the response status code should be 200
    Then 3 new rows have been inserted in the legacy table "people_groups"
