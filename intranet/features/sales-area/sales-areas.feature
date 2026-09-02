Feature: Sales / Customer

  Scenario: As an anonymous user, test that i'm not allowed to see sales areas pages
    When I go to "/sales/sales-areas"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/sales/sales-areas/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see sales areas pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-areas"
    Then the response status code should be 200
    And I should see "Sales Areas"
    When I go to "/sales/sales-areas/11/show"
    Then the response status code should be 200
    And I should see "Kinder"
    And I should not see "Add An ASM"

  Scenario: As a sales admin user, test that i'm allowed to see the add an asm forms
    Given I authenticate as "user-sa@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-areas/11/show"
    Then the response status code should be 200
    And I should see "Kinder"
    And I should see "Add An ASM"

  Scenario: As a sales admin user, test that I see a delete button
    Given I authenticate as "user-sa@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-areas/11/show"
    Then the response status code should be 200
    Then I should see 1 ".fa-trash" elements

  Scenario: As a basic user, test that i'm not allowed to delete an asm
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-areas/1/delete"
    Then I should be on "/sales/sales-areas/11/show"
    And the response status code should be 200
    And I should see "You do not have permissions"

  Scenario: As a sales admin user, test that i'm allowed to delete an asm
    Given I authenticate as "user-sa@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-areas/1/delete"
    Then I should be on "/sales/sales-areas/11/show"
    And the response status code should be 200
    And I should see "ASM has been unassigned from Kinder"

  Scenario: As a sales admin user, test that i'm allowed to submit one of the "add an asm" forms
    Given I authenticate as "user-sa@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-areas/8/show"
    Then the response status code should be 200
    And I select "/people/41" from "sales_area_asm_t_l_d_asm"
    And I select "/locations/23" from "sales_area_asm_t_l_d_sso"
    And I press "sales_area_asm_t_l_d_save"
    Then I should be on "/sales/sales-areas/8/show"
    And the response status code should be 200
