Feature: The mobile pages should be redirected

  Scenario: The SFR mobile pages should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mobile/?page=sfr&action=create"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/add"

  Scenario: The mobile pages should be redirected even if index.php is in URL
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mobile/index.php?page=sfr&action=create"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/add"

  Scenario: The MIM mobile pages should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mobile/?page=mim&action=create"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/market-intelligences/add"

  Scenario: The ODP mobile pages should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mobile/?page=odp&action=listing"
    Then I should not be on a legacy page
    And I should be on the exact url "/support/on_time_delivery_planning"
