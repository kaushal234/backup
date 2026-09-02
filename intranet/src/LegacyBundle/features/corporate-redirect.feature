Feature: The corporate pages should be redirected

  Scenario: The corporate main page should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/corporate/index.php"
    Then I should not be on a legacy page
    And I should be on the exact url "/corporate"
