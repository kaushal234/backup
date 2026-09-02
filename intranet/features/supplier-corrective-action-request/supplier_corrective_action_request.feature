@scar
Feature: Quality / SCAR

  Scenario: As an anonymous user, test that i'm not allowed to see scar pages
    When I go to "/quality/supplier-corrective-action-requests"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/quality/supplier-corrective-action-requests/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see scar pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/supplier-corrective-action-requests"
    Then the response status code should be 200
    And The module should be "SCAR"
    And I should see "Supplier Corrective Action Request"
    And I should see "SCAR Count by Location, Status"
    And I should see an "a[href$='/quality/supplier-corrective-action-requests/add']" element
    When I go to "/quality/supplier-corrective-action-requests/1/show"
    Then the response status code should be 200
    And I should see "SUPPLIER CORRECTIVE ACTION REQUEST #1"
    And I should see "Details"
    And I should see "Conversation"
    And I should see "Links"
    And I should see "Files"
    And I should see "Logs"
    And I should see "Followers"
    And I should see "Tasks"

  Scenario: A superuser can upload a file
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/supplier-corrective-action-requests/1/show"
    Then the response status code should be 200
    And I attach the file "file.pdf" to "supplier_corrective_action_request_file[file]"
    And I fill in "supplier_corrective_action_request_file[description]" with "this is a file description"
    And press "supplier_corrective_action_request_file[submit]"
    Then the response status code should be 200
    And I should be on "/quality/supplier-corrective-action-requests/1/show"
    And I should see "The file has been successfully uploaded"
    And I should see "this is a file description"

  Scenario: As a superuser, test that I can switch status
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/supplier-corrective-action-requests/1/show"
    Then the response status code should be 200
    And I should not see an "Add a new scar conversation comment" element
    And I should see "VENDOR TO FILL FORM"
    When I go to "/quality/supplier-corrective-action-requests/1/status/VENDOR TO FILL FORM"
    Then the response status code should be 200
    And I should be on "/quality/supplier-corrective-action-requests/1/show"
    And I should see "VENDOR TO FILL FORM"
    And I should see "Add a new scar conversation comment"

  Scenario: As a superuser, test that I can add a comment on conversation
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/supplier-corrective-action-requests/1/show"
    Then the response status code should be 200
    And I fill in "supplier_corrective_action_request_comment_file[message]" with "Whats up Supplier ?"
    And I attach the file "file.pdf" to "supplier_corrective_action_request_comment_file[file]"
    And press "supplier_corrective_action_request_comment_file[submit]"
    Then the response status code should be 200
    And I should be on "/quality/supplier-corrective-action-requests/1/show"
    And I should see "Whats up Supplier ?"
    And I should see "user, superuser"
    And I should see an "a[href$='/comments/1/files/10']" element

  Scenario: A superuser change status of SCAR
    Given I authenticate as "user-ceo@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/supplier-corrective-action-requests/1/status/CLOSED"
    Then the response status code should be 200
    And I should be on "/quality/supplier-corrective-action-requests/1/show"
    And I should not see "Status CLOSED is not allowed"

  Scenario: A basic user can't see some logic element
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/supplier-corrective-action-requests/1/show"
    Then the response status code should be 200
    And I should not see an "a[href$='/quality/supplier-corrective-action-requests/1/edit']" element
    And I should not see an "a[href$='/quality/supplier-corrective-action-requests/1/admin-parts']" element
    And I should not see an "Add a new scar conversation comment" element

  Scenario: As a basic, test that i can filter a scar
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/supplier-corrective-action-requests"
    Then the response status code should be 200
    Then I select "IF100" from "filter_supplier_corrective_action_request[iFactor][value]"
    Then I select "CLOSED" from "filter_supplier_corrective_action_request[status][value][]"
    Then I fill in "filter_supplier_corrective_action_request[id][value]" with "1"
    Then I fill in "filter_supplier_corrective_action_request[shortDescription][value]" with "Generator Failure"
    Then I fill in "filter_supplier_corrective_action_request[supplierNumber][value]" with "DAN0013"
    Then I fill in "filter_supplier_corrective_action_request[supplierName][value]" with "DANA"
    And press "Filter"
    Then the response status code should be 200
    And I should be on "/quality/supplier-corrective-action-requests"
    And I should see "Showing 1 - 1 of 1"
    And I should see "DANA SAS FRANCE"
    And I should see "DAN0013"
    And I should see "1"
    And I should not see an "C30001" element
    And I should not see an "AMAZONPAR" element

  Scenario: As a superuser, test that I can use SCAR report
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/supplier-corrective-action-requests/report"
    Then the response status code should be 200
    And I should see "FILTER SUPPLIER"
    Then I fill in "supplierNumber" with "DAN0013"
    And press "submit"
    Then the response status code should be 200
    And I should see "CLOSED"
    And I should see "2"
