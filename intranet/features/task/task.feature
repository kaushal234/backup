Feature: Task

  Scenario: As an anonymous user, test that i'm not allowed to see specification pages
    When I go to "/tasks"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, I should be allowed to go on my tasks
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/tasks"
    Then the response status code should be 200
    And I should see an "a[href$='/tasks/add']" element
    And I should see "Task"
    And I should see "Filter"
    When I go to "/tasks/9/show"
    Then the response status code should be 200
    And I should be on "/tasks/9/show"
    And I should see "Details"
    And I should see "File"
    And I should see "logs"
    And I should see "Followers"

  Scenario: As a basic user, I should be allowed to go on search task
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/tasks/search"
    Then the response status code should be 200
    And I should see an "a[href$='/tasks/add']" element
    And I should see "task"
    And I should see "Filter"
    When I go to "/tasks/9/show"
    Then the response status code should be 200
    And I should be on "/tasks/9/show"
    And I should see "Details"
    And I should see "File"
    And I should see "logs"
    And I should see "Followers"

  @javascript
  Scenario: As a basic user, I should be allowed to create task
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/tasks/add"
    And I wait until I see "Add"
    And I wait until I see "Description"
    And I wait until I see "Module"

  Scenario: As coo user, I should not be allowed to edit task
    Given I authenticate as "user-coo@tld.fr" with "P@ssw0rd15chars"
    When I go to "/tasks/9/edit"
    Then the response status code should be 200
    Then I should not be on "/tasks/9/edit"
    And I should see "You do not have permissions"

  @javascript
  Scenario: As a user who is assignor of the task, I should be allowed to edit task
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/tasks/9/show"
    And I wait for "a[href$='/tasks/9/edit']" element
    Then I go to "/tasks/9/edit"
    And I wait until I see "edit"

  Scenario: As coo user I should not be allowed to close task
    Given I authenticate as "user-coo@tld.fr" with "P@ssw0rd15chars"
    When I go to "/tasks/9/close"
    Then the response status code should be 200
    Then I should not be on "/tasks/9/close"
    And I should see "You do not have permissions"

  Scenario: As a user who is assignor of the task, I should be allowed to close task
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/tasks/9/close"
    Then the response status code should be 200
    And I should see "Closing reason"

  Scenario: As coo user I should not be allowed to transfer task
    Given I authenticate as "user-coo@tld.fr" with "P@ssw0rd15chars"
    When I go to "/tasks/9/transfer"
    Then the response status code should be 200
    Then I should not be on "/tasks/9/transfer"
    And I should see "You do not have permissions"

  Scenario: As mis user I should be allowed to transfer task
    Given I authenticate as "user-mis@tld.fr" with "P@ssw0rd15chars"
    When I go to "/tasks/9/transfer"
    Then the response status code should be 200
    And I should see "Transfer reason"

  Scenario: As coo user I should not be allowed to transfer task
    Given I authenticate as "user-coo@tld.fr" with "P@ssw0rd15chars"
    When I go to "/tasks/9/transfer"
    Then the response status code should be 200
    Then I should not be on "/tasks/9/transfer"
    And I should see "You do not have permissions"

  Scenario: As a user who is assignor or assignor of the task, I should be allowed to reschedule task
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/tasks/9/reschedule"
    Then the response status code should be 200
    And I should see "Reschedule reason"

  Scenario: As a basic user, I should be allowed to add a comment
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/tasks/9/comment"
    Then the response status code should be 200
    And I should see "Submit"

  Scenario: As user of my task I should be able to navigate between them
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/tasks"
    Then the response status code should be 200
    When I go to "/mis/trouble-tickets/3/show"
    And I should see "Previous"
    And I should see an "a[href$='/tasks/9/show']" element
    When I go to "/tasks/9/show"
    And I should see "Next"
    And I should see an "a[href$='/mis/trouble-tickets/3/show']" element

  Scenario: As mis user I should see a PAUSE button on task
    Given I authenticate as "user-mis@tld.fr" with "P@ssw0rd15chars"
    When I go to "tasks/11/show"
    Then the response status code should be 200
    And I should see "PAUSE"

  Scenario: As a user who can edit a warehouse task (creator), I should be redirected to warehouse task edit page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/tasks/13/edit"
    Then I should be on "/purchasing/warehouse-tasks/13/edit"

  Scenario: As a user who edits a warehouse task, I should be redirected to warehouse task home after submit
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/warehouse-tasks/13/edit"
    Then the response status code should be 200
    And I should see "Edit WHT #13"
    And I fill in "warehouse_task[description]" with "warehouse task edited"
    And press "warehouse_task[submit]"
    Then the response status code should be 200
    Then I should be on "/purchasing/warehouse-tasks"
    And I should see "warehouse task edited"

  Scenario: As a user who creates a warehouse task, I should be redirected to warehouse task home after submit
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/warehouse-tasks"
    And I should see "Warehouse Task"
    Then the response status code should be 200
    And I select "/locations/20" from "warehouse_task[location]"
    And I select "/people/3" from "warehouse_task[assignee]"
    And I select "/people/143" from "warehouse_task[recipients][]"
    And I additionally select "/people/32" from "warehouse_task[recipients][]"
    And I fill in "warehouse_task[description]" with "warehouse task created"
    And press "warehouse_task[submit]"
    Then the response status code should be 200
    Then I should be on "/purchasing/warehouse-tasks"
    And I should see "warehouse task created"
