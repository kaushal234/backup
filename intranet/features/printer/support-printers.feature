Feature: Printers

  Scenario: As an anonymous user, test that i'm not allowed to see printer pages
    When I go to "/support/printers"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to printer  pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/printers"
    Then the response status code should be 200
    And I should see "Printers"
    And I should see "1primeur"
    And I should see an "a[href$='/support/printers/1/show']" element
    And I should not see an "a[href$='/support/printers/add']" element
    And I should not see an "a[href$='/support/printers/1/edit']" element
    And I should not see an "a[href$='/support/printers/1/delete']" element

  Scenario: As a superuser, test that i'm allowed to see printer pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/printers"
    Then the response status code should be 200
    And I should see an "a[href$='/support/printers/add']" element
    And I should see an "a[href$='/support/printers/1/edit']" element
    And I should see an "a[href$='/support/printers/1/delete']" element

  Scenario: As a superuser, test that i can add a printer
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/printers/add"
    Then the response status code should be 200
    Then I fill in "printer_form[companyName]" with "OnePriceDie"
    Then I fill in "printer_form[firstname]" with "Jean Michelle"
    Then I fill in "printer_form[lastname]" with "Caligraphie"
    Then I fill in "printer_form[email]" with "jeanMich@gmail.com"
    And I fill in "printer_form[address][street1]" with "Rue de Wellington"
    And I fill in "printer_form[address][street2]" with "A gauche et à droite de DD"
    And I fill in "printer_form[address][postalCode]" with "121212"
    And I fill in "printer_form[address][city]" with "BrigeTown"
    And I fill in "printer_form[address][state]" with "BibiState"
    And I select "France" from "printer_form[address][country]"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/support/printers"
    And I should see "Printer has been successfully saved"
    And I should see "OnePriceDie"

  Scenario: As a superuser, test that i can see a printer
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/printers/2/show"
    Then the response status code should be 200
    And I should see "OnePriceDie"

  Scenario: As a superuser, test that i can edit a printer
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/printers/2/edit"
    Then the response status code should be 200
    And I fill in "printer_form[companyName]" with "OunoPrecioMuerte"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/support/printers/2/show"
    And I should see "Printer has been successfully saved"
    And I should see "OunoPrecioMuerte"

  Scenario: A superuser user can delete a printer
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/printers/2/delete"
    Then the response status code should be 200
    And I should be on "/support/printers"
    And I should see "Printer deleted successfully"
