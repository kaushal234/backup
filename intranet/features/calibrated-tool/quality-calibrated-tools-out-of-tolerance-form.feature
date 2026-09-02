Feature: Quality / Calibrated Tools / Out Of Tolerance Form

  Scenario: As an anonymous user, test that i'm not allowed to see out of tolerance forms pages
    When I go to "/quality/calibrated-tools/out-of-tolerance-forms"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/quality/calibrated-tools/out-of-tolerance-forms/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see out of tolerance forms
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/calibrated-tools/out-of-tolerance-forms"
    Then the response status code should be 200
    And The module should be "CT"
    And I should see "OUT OF TOLERANCE FORMS"
    And I should not see an "a[href$='/quality/calibrated-tools/out-of-tolerance-forms/add']" element
    When I go to "/quality/calibrated-tools/out-of-tolerance-forms/1/show"
    Then the response status code should be 200
    And I should see "Out Of Tolerance Details"
    And I should not see an "a[href$='/quality/calibrated-tools/out-of-tolerance-forms/1/edit']" element
    And I should see an "h5" element
    And I should see an "div.tabs-container" element

  Scenario: As a basic user, test that i'm allowed to filter out of tolerance forms
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/calibrated-tools/out-of-tolerance-forms?out_of_tolerance_forms%5Bstatus%5D=PENDING&out_of_tolerance_forms%5BimpactAnalysis%5D=&out_of_tolerance_forms%5BcorrectiveMeasures%5D=&out_of_tolerance_forms%5BanalysisBy%5D=&out_of_tolerance_forms%5BserialNumber%5D=&out_of_tolerance_forms%5Bfactory%5D=&out_of_tolerance_forms%5BlocationArea%5D=&out_of_tolerance_forms%5BtoolType%5D=&out_of_tolerance_forms%5BitemsPerPage%5D="
    Then the response status code should be 200
    And I should see "OUT OF TOLERANCE FORMS"
    And I should see "Total number of records:"

  Scenario: As a superuser, test that i'm allowed to see out of tolerance forms
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/calibrated-tools/out-of-tolerance-forms"
    Then the response status code should be 200
    And I should see "Tools"
    And I should see "Location Areas"
    And I should see "Tool Types"
    And I should see "Out Of Tolerance Forms"
    And I should see "FILTER"
    And I should see "RESET"
    And I should see 1 "table" element
    And I should see 12 "tr" elements
    And I should see "IN PROGRESS"
    When I go to "/quality/calibrated-tools/out-of-tolerance-forms/1/show"
    Then the response status code should be 200
    And I should see "Out Of Tolerance Details"
    And I should see an "a[href$='/quality/calibrated-tools/out-of-tolerance-forms/1/edit']" element
    And I should see "Corrective Measures"

  Scenario: As a superuser, test that i can edit an out of tolerance form
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/calibrated-tools/out-of-tolerance-forms/1/edit"
    Then the response status code should be 200
    And I should see "Analysis By"
    And I should see "Status"
    And I select "IN_PROGRESS" from "out_of_tolerance_form[status]"
    And I attach the file "file.pdf" to "out_of_tolerance_form[files][]"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/quality/calibrated-tools/out-of-tolerance-forms/1/show"
    And I should see "The Out of tolerance has been modified."
    And I should see "IN PROGRESS"

