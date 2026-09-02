Feature: Sales / Demo

  Scenario: As an anonymous user, test that i'm not allowed to see demo pages
    When I go to "/sales/demos"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/sales/demos/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see demo pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/demos"
    Then the response status code should be 200
    And The module should be "DEMO"
    And I should see "Last Demos Created"
    And I should see "Last Sequences"
    When I go to "/sales/demos/1/show"
    Then the response status code should be 200
    And I should see "Information"
    And I should see "Representatives"
    And I should see "Files"
    And I should see "logs"

  Scenario: As a superuser, test that i'm allowed to see demo pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/demos"
    Then the response status code should be 200
    And I should see an "a[href$='/sales/demos/add']" element
    And I should see "Last Demos Created"
    And I should see "Last Sequences"
    When I go to "/sales/demos/1/show"
    Then the response status code should be 200
    And I should see "Information"
    And I should see "Representatives"
    And I should see "Files"
    And I should see "Add File"
    And I should see an "a[href$='/sales/demos/1/edit']" element
    And I should see "logs"

  Scenario: As a superuser, test that i can add a demo
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/demos/add"
    Then the response status code should be 200
    Then I select "/sales/customers/43" from "demo_type_form[customer]"
    Then I select "/people/31" from "demo_type_form[asm]"
    Then I select "/people/48" from "demo_type_form[psm]"
    Then I select "/people/65" from "demo_type_form[ast]"
    Then I select "/locations/15" from "demo_type_form[sso]"
    Then I select "/locations/15" from "demo_type_form[factory]"
    Then I select "/countries/11" from "demo_type_form[country]"
    Then I select "SUCCESSFUL" from "demo_type_form[expectedClosingStatus]"
    Then I select "/sales/products/1" from "demo_type_form[product]"
    Then I select "/emission_ratings/4" from "demo_type_form[emissionRating]"
    And I fill in "demo_type_form[comment]" with "Just for test"
    And press "Submit"
    Then the response status code should be 200
    And I should be on "/sales/demos"
    And I should see "8"
    And I should see "**DEMO**"

  Scenario: Test that a demo has been added
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/demos/8/show"
    Then the response status code should be 200

  Scenario: As a superuser, test that i can edit a demo
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/demos/1/edit"
    Then the response status code should be 200
    When I select "/people/41" from "demo_form[asm]"
    When I select "/people/48" from "demo_form[ast]"
    And press "Submit"
    Then the response status code should be 200
    And I should be on "/sales/demos/1/show"
    And I should see "user SALES"
    And I should see "user PSM"

  Scenario: A superuser can upload a file
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/demos/1/show"
    Then the response status code should be 200
    And I attach the file "file.pdf" to "demo_file[file]"
    And I fill in "demo_file[description]" with "this is a file description"
    And press "demo_file_submit"
    Then the response status code should be 200
    And I should be on "/sales/demos/1/show"
    And I should see "File has been successfully added to Demo"
    And I should see 1 "#demo-files table.footable tbody tr" elements
    And I should see "this is a file description"

  Scenario: As a superuser, test that I can add ER when status is APPROVED
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/demos/7/show"
    Then the response status code should be 200
    And I should see "Add ER To Demo"
    And I should see "Add Airport To Demo"
    When I select "Loaders / TXL-737 - THOMAS" from "demo_er_form[equipmentRecord]"
    And press "demo_er_form[submit]"
    Then the response status code should be 200
    And I should be on "/sales/demos/7/show"
    And I should see "THOMAS"
    And I should see "ER Serial Number"

  Scenario: A superuser can put a comment
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/demos/1/show"
    Then the response status code should be 200
    And I fill in "demo_comment[message]" with "Petit commentaire"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/sales/demos/1/show"

  Scenario: A superuser can close a demo
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/demos/4/close_demo"
    Then the response status code should be 200
    Then I select "SUCCESSFUL" from "demo_form[status]"
    And I fill in "demo_form[closingComment]" with "Coucou, tu veux voir ma tâche ?"
    And press "Submit"
    Then the response status code should be 200
    And I should be on "/sales/demos/4/show"
    And I should see "SUCCESSFUL"
    And I should see "Coucou, tu veux voir ma tâche ?"

  Scenario: A basic user can see reports page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/demos/reports"
    Then the response status code should be 200
    And I should see "Demos by SSO by status"
    And I should see "Opened Demos by Factory by status"
    And I should see "Delinquents Demos By factory"
    And I should see "Delinquents Demos By SSO"
    And I should see "Opened Demos By Factory - SSO"

  Scenario: A superuser user can delete demos
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/demos/4/show"
    Then the response status code should be 200
    And I should see an "a[href$='/sales/demos/4/delete']" element
    When I go to "/sales/demos/4/delete"
    Then the response status code should be 200
    And I should be on "/sales/demos"
    And I should see "Demo has been successfully deleted"

  Scenario: A superuser user can cancel demos
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/demos/7/show"
    Then the response status code should be 200
    And I should see an "a[href$='/sales/demos/7/move-to-cancel']" element
    When I go to "/sales/demos/7/move-to-cancel"
    Then the response status code should be 200
    And I should be on "/sales/demos/7/show"
    And I should see "CANCELLED"

  Scenario: A superuser user can manually set demo as ACTIVE
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/demos/6/show"
    Then the response status code should be 200
    And I should see an "a[href$='/sales/demos/6/move-to-active']" element
    When I go to "/sales/demos/6/move-to-active"
    Then the response status code should be 200
    And I should be on "/sales/demos/6/show"
    And I should not see an "a[href$='/sales/demos/6/move-to-active']" element

  Scenario: A superuser user can filter the demos
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/demos"
    And I select "/sales/customer_types/4" from "customer_type"
    And I press "FILTER"
    Then the response status code should be 200



