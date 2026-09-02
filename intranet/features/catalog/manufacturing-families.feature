Feature: Manufacturing families

  Scenario: As an anonymous user, test that i'm not allowed to see manufacturing families pages
    When I go to "/manufacturing-families"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to manufacturing families  pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/manufacturing-families"
    Then the response status code should be 200
    And I should see "Manufacturing families"
    And I should see "Stan"
    And I should not see an "a[href$='/sales/catalogue/manufacturing-families/add']" element
    And I should not see an "a[href$='/sales/catalogue/manufacturing-families/2/edit']" element
    And I should not see an "a[href$='/sales/catalogue/manufacturing-families/2/delete']" element


  Scenario: As a mlm user, test that i'm allowed to see manufacturing families pages
    Given I authenticate as "user-mlm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/manufacturing-families"
    Then the response status code should be 200
    And I should see an "a[href$='/sales/catalogue/manufacturing-families/add']" element
    And I should see an "a[href$='/sales/catalogue/manufacturing-families/2/edit']" element
    And I should see an "a[href$='/sales/catalogue/manufacturing-families/2/delete']" element

  Scenario: As a mlm user, test that i can add a manufacturing families
    Given I authenticate as "user-mlm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/manufacturing-families/add"
    Then the response status code should be 200
    And I fill in "manufacturing_family_form[name]" with "NBA 2K"
    And I fill in "manufacturing_family_form[testDuration]" with "12"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/sales/catalogue/manufacturing-families"
    And I should see "Manufacturing family has been successfully saved"
    And I should see "NBA 2K"

  Scenario: As a mlm user, test that i can edit a manufacturing families
    Given I authenticate as "user-mlm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/manufacturing-families/29/edit"
    Then the response status code should be 200
    And I fill in "manufacturing_family_form[name]" with "NBA 2K20"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/sales/catalogue/manufacturing-families"
    And I should see "Manufacturing family has been successfully saved"
    And I should see "NBA 2K20"

  Scenario: A mlm user user can delete a manufacturing families
    Given I authenticate as "user-mlm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/manufacturing-families/29/delete"
    Then the response status code should be 200
    And I should be on "/sales/catalogue/manufacturing-families"
    And I should see "Manufacturing family has been successfully deleted"
