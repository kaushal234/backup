Feature: Sales / Product Certificates

  Scenario: As an anonymous user, test that i'm not allowed to see certificates pages
    When I go to "/sales/catalogue/certificates"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/sales/catalogue/certificates/1/show"
    Then I should be on "/login"

  Scenario: As a basic user, test that i'm allowed to see certificates pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/certificates"
    Then the response status code should be 200
    And The module should be "CAT"
    And I should see "10 Latest Certificates"
    And I should see an "a[href$='/sales/catalogue/certificates/1/show']" element
    When I go to "/sales/catalogue/types/1/show"
    Then the response status code should be 200

  Scenario: As a superuser, test that i can add a certificate
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/certificates/add"
    Then the response status code should be 200
    When I select "/sales/products/5" from "form[product]"
    And I select "/locations/29" from "form[factory]"
    And press "Submit"
    Then the response status code should be 200
    And I should be on "/sales/catalogue/certificates"

  Scenario: As a superuser, test that i can edit a certificate
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/certificates/4/edit"
    Then the response status code should be 200
    When I fill in "form[testReportNumber]" with "6969696969"
    And press "Submit"
    Then the response status code should be 200
    And I should be on "/sales/catalogue/certificates/4/show"
    And I should see "6969696969"

  Scenario: As a superuser, test that I can delete a certificate
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/certificates/4/show"
    Then the response status code should be 200
    And I should see an "a[href$='/sales/catalogue/certificates/4/delete']" element
    When I go to "/sales/catalogue/certificates/4/delete"
    Then the response status code should be 200
    And I should be on "/sales/catalogue/certificates"
