Feature: The vendor item (xref) legacy pages should be redirected

  Scenario: The vendor PN search of XREF should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/manufacturing/pur/dev.php?m[0]=xref"
    Then I should not be on a legacy page
    And I should be on the exact url "/purchasing/item_reference"