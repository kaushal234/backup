Feature: Prints

  Scenario: As an anonymous user, test that i'm not allowed to see prints pages of a manual
    When I go to "/support/manuals/1/prints"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see prints pages of a manual
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/manuals/1/prints"
    Then the response status code should be 200
    And I should see "Manual details"
    And I should see "Prints list"
    And I should see an "a[href$='/support/manual_prints/e99e8e95-0245-4dc7-ace0-749c6838991c/show']" element
    And I should not see an "a[href$='/support/manuals/1/prints/add']" element
    And I should not see an "a[href$='/support/manuals/1/edit']" element

  Scenario: As a superuser, test that i can add a print for a manual
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/manuals/1/prints/add"
    Then the response status code should be 200
    Then I select "/support/manual_printers/1" from "print_form[manualPrinter]"
    And I fill in "print_form[requestedDeliveryDate]" with "06/01/2028"
    And I fill in "print_form[standard]" with "1"
    And I fill in "print_form[full]" with "2"
    And I fill in "print_form[extra]" with "3"
    And I fill in "print_form[chapter5]" with "4"
    And I fill in "print_form[comment]" with "jeanMiCheng"
    And press "print_form_submit"
    Then the response status code should be 200
#    And I should be on "/support/manual_prints/2/show" can't guess the id
    And I should see "Print request has been successfully created. The printer will be notified by mail shortly."
    And I should see "jeanMiCheng"
    And I should see "2028-06-01"

  Scenario: As a non authenticated user, test that i'm allowed to download a ManualPrint Zip file on the public dedicated route
    When I go to "/public/prints/e99e8e95-0245-4dc7-ace0-749c6838991c"
    Then the response status code should be 200
    And I should see response headers "content-type" with "application/zip"

  Scenario: As a non authenticated user, if I try to re download this ManualPrint Zip file I should land on be on the public resources page with an error
    When I go to "/public/prints/e99e8e95-0245-4dc7-ace0-749c6838991c"
    Then the response status code should be 200
    And I should see "already downloaded."
    And I should see "Please contact the requestor of this ManualPrint to generate a new Manual Print request"
    And I should see "Welcome to the ALVEST Resources public page"
