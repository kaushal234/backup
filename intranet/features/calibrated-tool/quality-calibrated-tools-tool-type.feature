Feature: Quality / Calibrated Tools / Tool Type

  Scenario: As an anonymous user, test that i'm not allowed to see tool types pages
    When I go to "/quality/calibrated-tools/tool-types"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/quality/calibrated-tools/tool-types/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see tool types
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/calibrated-tools/tool-types"
    Then the response status code should be 200
    And The module should be "CT"
    And I should see "TOOL TYPES"
    And I should not see an "a[href$='/quality/calibrated-tools/tool-types/add']" element
    When I go to "/quality/calibrated-tools/tool-types/1/show"
    Then the response status code should be 200
    And I should see "Calibrated Tools"
    And I should not see an "a[href$='/quality/calibrated-tools/tool-types/1/edit']" element
    And I should not see an "a[href$='/quality/calibrated-tools/tool-types/1/delete']" element
    And I should see an "h5" element

  Scenario: As a basic user, test that i'm allowed to filter tool types
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/calibrated-tools/tool-types?tool_type_filters%5Bdescription%5D=&tool_type_filters%5BitemsPerPage%5D=10&tool_type_filters%5B_token%5D=t7hvgkpJ6rvprMrvNwrJiUzS-eIcqlognnjvL_UUvSg"
    Then the response status code should be 200
    And I should see "TOOL TYPES"
    And I should see "Total number of records:"

  Scenario: As a superuser, test that i'm allowed to see tool types
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/calibrated-tools/tool-types"
    Then the response status code should be 200
    And I should see "Home"
    And I should see "Tools"
    And I should see "Location Areas"
    And I should see "Tool Types"
    And I should see "Out Of Tolerance Forms"
    And I should see an "a[href$='/quality/calibrated-tools/tool-types/add']" element
    And I should see "FILTER"
    And I should see "RESET"
    And I should see 1 "table" element
    And I should see 7 "tr" elements
    When I go to "/quality/calibrated-tools/tool-types/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/quality/calibrated-tools/tool-types/1/edit']" element
    And I should not see an "a[href$='/quality/calibrated-tools/tool-types/1/delete']" element
    And I should see "Type Description : Hammers"

  Scenario: As a superuser, test that i can add a tool type
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/calibrated-tools/tool-types/add"
    Then the response status code should be 200
    When I fill in the following:
      | tool_type[description]   | newToolType |
    And press "submit"
    When the response status code should be 200
    And I should be on "/quality/calibrated-tools/tool-types/6/show"
    And I should see "The tool type has been created successfully."

  Scenario: As a superuser, test that i can edit a tool type
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/calibrated-tools/tool-types/1/edit"
    Then the response status code should be 200
    And press "submit"
    Then the response status code should be 200
    And I should be on "/quality/calibrated-tools/tool-types/1/show"
    And I should see "The tool type has been modified."
    And I should see "Type Description : Hammers"

  Scenario: As a superuser, test that i can delete a tool type
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/calibrated-tools/tool-types/6/delete"
    Then the response status code should be 200
    And I should be on "/quality/calibrated-tools/tool-types"
    And I should see "The tool type has been removed."
