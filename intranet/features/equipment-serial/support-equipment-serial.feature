Feature: Support Equipment serial numbers

  Scenario: As an anonymous user, test that i'm not allowed to see serials
    When I go to "/support/serials/1/show"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/support/serials/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see serials
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/serials/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/product_support/index.ps.php?m[0]=equipment&m[1]=view&id=37455']" element
    And I should see "Equipment Record Information"
    And I should see "Serial numbers list"

  Scenario: As a basic user, test that i'm not allowed to edit serials
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/serials/1/edit"
    And I should see "You do not have permissions"

  Scenario: As a super user, test that i'm allowed see edit link
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/serials/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/support/serials/1/edit']" element
    And I should see an "a[href$='/support/serials/1/logs']" element

  Scenario: As a basic user, test that i'm allowed to edit serials
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/serials/1/edit"
    Then the response status code should be 200
    And I should see an "#equipment_record_serials_serials_0_brand" element
    And I fill in "equipment_record_serials[serials][0][brand]" with "LOST_ODYSSEY"
    And press "submit"
    Then the response status code should be 200
    And I should see "LOST_ODYSSEY"

  Scenario: As a basic user, test that i'm allowed to see serials logs
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/serials/1/logs"
    Then the response status code should be 200

