Feature: Mis / Groups

  Scenario: As an anonymous user, test that i'm not allowed to see groups pages
    When I go to "/mis/groups"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/mis/groups/2/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see groups pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/groups"
    Then the response status code should be 200
    And The module should be "MIS"
    And I should see "groups list"
    And the "table.footable tbody tr:nth-child(1) td:nth-child(1)" element should contain a text
    And the "table.footable tbody tr:nth-child(1) td:nth-child(2)" element should contain a text
    And the response should not contain "/groups/add"
    And the response should contain "/groups/3/show"
    When I go to "/mis/groups/3/show"
    Then the response status code should be 200
    And I should see 5 "table.association-table tr" elements
    And I should see "Group detail"
    And the response should not contain "/groups/3/edit"
    And the response should not contain "/groups/3/delete"
    And the response should not contain "/features/1/show"

  Scenario: As a superuser, test that i'm allowed to see groups pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/groups"
    Then the response status code should be 200
    And I should see "Groups list"
    And the response should contain "/groups/3/show"
    And the response should contain "/groups/add"
    When I go to "/mis/groups/3/show"
    Then the response status code should be 200
    And the response should contain "/groups/3/edit"
    And the response should contain "/groups/3/delete"
    And I should see "Features for"

  Scenario: As a superuser, test that i can add a group
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/groups/add"
    Then the response status code should be 200
    When I fill in the following:
      | app_group[name]              | DUMMYGROUP                    |
      | app_group[description]       | My awesome group description  |
      | app_group[legacyPermissions] | My awesome legacy description |
    And press "submit"
    Then the response status code should be 200
    And I should be on "/mis/groups"
    And I should see "DUMMYGROUP"

  Scenario: As a superuser, test that i can edit a group
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/groups/2/edit"
    Then the response status code should be 200
    When I fill in "app_group[description]" with "other group description"
    When I fill in "app_group[legacyPermissions]" with "other legacy permissions"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/mis/groups/2/show"
    And I should see "other group description"
    And I should see "other legacy permissions"

  Scenario: As a basic user, test that i can see group members
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/groups/1/people"
    Then the response status code should be 200
    And I should see "members of"

  Scenario: As a superuser, test that i can download group members list
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/groups/1/people/download"
    Then the response status code should be 200
    Then I should see response headers "content-type" with "text/csv; charset=utf-8"
    Then I should see response headers "content-disposition" with 'inline; filename=data.csv'
