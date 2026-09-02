Feature: Part Number Task

  Scenario: As a user who can edit a part number task (creator), I should be redirected to part number task edit page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/tasks/53/edit"
    Then I should be on "/parts/part-number-tasks/53/edit"

  Scenario: As a user who creates a part number task, I should be redirected to part number task home after submit
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/part-number-tasks"
    Then the response status code should be 200
    And I should see "Part Number Task"
    And I select "/locations/27" from "part_number_task[location]"
    And I select "/people/3" from "part_number_task[assignee]"
    And I select "/people/86" from "part_number_task[recipients][]"
    And I additionally select "/people/32" from "part_number_task[recipients][]"
    And I select "!TEMP1003" from "part_number_task[partNumber]"
    And I fill in "part_number_task[description]" with "part number task created"
    And press "part_number_task[submit]"
    Then the response status code should be 200
    Then I should be on "/parts/part-number-tasks"
    And I should see "part number task created"
    And I should see "!TEMP1003"
    
  Scenario: As a user who edits a part number task, I should be redirected to task show after submit
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/part-number-tasks/53/edit"
    Then the response status code should be 200
    And I should see "Edit Task #53"
    And I select "!TEMP1003" from "part_number_task[partNumber]"
    And I additionally select "/people/12" from "part_number_task[recipients][]"
    And I fill in "part_number_task[description]" with "part number task edited"
    And press "part_number_task[submit]"
    Then the response status code should be 200
    Then I should be on "/parts/part-number-tasks"
    And I should see "part number task edited"
    And I should see "!TEMP1003"