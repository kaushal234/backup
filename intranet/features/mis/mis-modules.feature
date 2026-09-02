Feature: Modules

  Scenario: As an anonymous user, test that i'm not allowed to see modules pages
    Given I go to "/mis/modules"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/mis/modules/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, I can access modules list
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    And I go to "/mis/modules"
    Then the response status code should be 200
    And I should see "Modules list"
    And I should not see an "a[href$='/mis/modules/add']" element

  Scenario: As a basic user, I can access module details
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    And I go to "/mis/modules/3/show"
    Then the response status code should be 200
    And I should see "LIDO"
    And I should see "une danseuse ?"
    And I should not see "Edit"
    And I should see an "div#module-title" element

  Scenario: As a super user, I can access module details and see edit action
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    And I go to "/mis/modules/3/show"
    Then the response status code should be 200
    And I should see "Edit"

  Scenario: As a superuser, test that i can add a mis module
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/modules/add"
    Then the response status code should be 200
    When I fill in the following:
      | app_mis_module[name]               | CER          |
      | app_mis_module[shortDescription]   | mode ulcere  |
      | app_mis_module[notificationColor]  | #F1F1F1F1            |
    And I select "/people/11" from "app_mis_module[keyUser]"
    And I select "/people/12" from "app_mis_module[operationalOwner]"
    And I select "/departments/2" from "app_mis_module[department]"
    And I select "/mis/applications/2" from "app_mis_module[application]"
    And I select "/people/13" from "app_mis_module[localKeyUsers][]"
    And press "submit"
    Then the response status code should be 200
    And I should see "CER"

  Scenario: As a superuser, test that i can edit a mis module
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/modules/3/edit"
    Then the response status code should be 200
    When I fill in the following:
      | app_mis_module[notificationColor] | #F1F1F1F2        |
    And I select "/departments/2" from "app_mis_module[department]"
    And I select "/modules/1" from "app_mis_module[requiredModules][]"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/mis/modules/3/show"
    And I should see "YEAH"

  Scenario: As a superuser, I should not be able to see default types assignees page
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/modules"
    Then the response status code should be 200
    And I should not see an "a[href$='/mis/modules/admin-default-assignees']" element
    When I go to "/mis/modules/admin-default-assignees"
    Then the response status code should be 200
    And I should see "You do not have permissions"

  Scenario: As a user MISM, I should be able to see default types assignees page
    Given I authenticate as "user-mism@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/modules"
    Then the response status code should be 200
    And I should see an "a[href$='/mis/modules/admin-default-assignees']" element
    When I go to "/mis/modules/admin-default-assignees"
    Then the response status code should be 200
    And I should be on "/mis/modules/admin-default-assignees"
    And I press "module_type_default_assignee_batch_submit"
    Then the response status code should be 200
    And I should be on "/mis/modules/admin-default-assignees"
    And I should see "Default assignees have been updated."

  Scenario: As a basic user, I can filter datatable
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/modules"
    And I fill in the following:
      | filter_module[name][value] | MOM |
    And press "Filter"
    Then I should see 1 "table.table tbody tr" elements

  Scenario: As a basic user, I can reset datatable
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/modules"
    And I press "Yes"
    Then I should see 25 "table.table tbody tr" elements