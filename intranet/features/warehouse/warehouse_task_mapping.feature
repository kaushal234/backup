Feature: Warehouse tasks mappings

  Scenario: As an anonymous user, test that i'm not allowed to see task mappings pages
    When I go to "/materials/warehouse/tasks-mappings"
    Then I should be on "/login"
    When I go to "//materials/warehouse/tasks-mappings/add"
    Then I should be on "/login"
    When I go to "//materials/warehouse/tasks-mappings/1/edit"
    Then I should be on "/login"

  Scenario: As a basic user, test that i'm allowed to see task mapping home page but not create nor edit mapping
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/materials/warehouse/tasks-mappings"
    Then the response status code should be 200
    And The module should be "WHSE"
    And I should be on "/materials/warehouse/tasks-mappings"
    And I should see 3 "table.report-table tbody tr" elements
    And I should not see an "a[href$='/materials/warehouse/tasks-mappings/add']" element
    And I should not see an "a[href$='/materials/warehouse/tasks-mappings/1/edit']" element
    When I go to "/materials/warehouse/tasks-mappings/add"
    Then the response status code should be 200
    And I should see "You do not have permissions"
    When I go to "/materials/warehouse/tasks-mappings/1/edit"
    Then the response status code should be 200
    And I should see "You do not have permissions"

  Scenario: As a warehouse user, test that i'm allowed to see task mapping home page
    Given I authenticate as "user-ws@tld.fr" with "P@ssw0rd15chars"
    When I go to "/materials/warehouse/tasks-mappings"
    Then the response status code should be 200
    And I should be on "/materials/warehouse/tasks-mappings"
    And I should see an "a[href$='/materials/warehouse/tasks-mappings/add']" element
    And I should not see an "a[href$='/materials/warehouse/tasks-mappings/1/edit']" element
    Then I should see an "a[href$='/materials/warehouse/tasks-mappings/add']" element
    When I go to "/materials/warehouse/tasks-mappings/add"
    Then I should be on "/materials/warehouse/tasks-mappings/add"
    And the response status code should be 200
    When I select "/locations/32" from "tasks_mapping[location]"
    And I fill in "tasks_mapping[inboundTasks][0]" with "1910"
    And I fill in "tasks_mapping[outboundTasks][0]" with "1911"
    And I fill in "tasks_mapping[administrativeTasks][0]" with "1912"
    And I fill in "tasks_mapping[excludedTasks][0]" with "1913"
    And press "Submit"
    Then I should be on "/materials/warehouse/tasks-mappings"
    And I should see "the tasks mapping has been created."
    And I should see "1910"
    And I should see an "a[href$='/materials/warehouse/tasks-mappings/4/edit']" element

  Scenario: Test that it is possible edit a task mapping matching user's location
    Given I authenticate as "user-ws@tld.fr" with "P@ssw0rd15chars"
    When I go to "/materials/warehouse/tasks-mappings/1/edit"
    Then the response status code should be 200
    Then I should not be on "/materials/warehouse/tasks-mappings/1/edit"
    And I should see "You do not have permissions"
    When I go to "/materials/warehouse/tasks-mappings/4/edit"
    Then the response status code should be 200
    And I should be on "/materials/warehouse/tasks-mappings/4/edit"
    When I fill in "tasks_mapping[inboundTasks][0]" with "1914"
    And press "Submit"
    Then I should be on "/materials/warehouse/tasks-mappings"
    And I should see "the tasks mapping has been updated."
    And I should see "1914"
