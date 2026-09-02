Feature: Vendor items

  Scenario: As an anonymous user, test that i'm not allowed to see vendor items pages
    When I go to "/purchasing/item_reference"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to vendor items  pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/item_reference"
    Then the response status code should be 200
    And I should see "Xref"

  Scenario:  As a basic user, test that i search a vendor items
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/item_reference"
    Then the response status code should be 200
    And I fill in "app_id_search[id]" with "1200676"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/purchasing/item_reference"
    And I should see 3 "table.footable tbody tr" elements