Feature: On time delivery Planning

  Scenario: As a basic user, I can access to odp homepage
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/on_time_delivery_planning"
    Then the response status code should be 200
    And I should see "ODP by Factory, Sales Organisation"
    And I should see "Factory Dashboard"
    And I should see "SSO Dashboard"

  Scenario: As a basic user, I can follow link to 'green tag not shipped no estimated pick up date'
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/on_time_delivery_planning/show?odpFilter=gt_not_shipped_no_estimated_pick_up_date&salesOrganisation=/locations/30"
    Then the response status code should be 200
    And I should see "ODP"

  Scenario: As a basic user, I can follow link to 'yellow tag not shipped no estimated pick up date'
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/on_time_delivery_planning/show?odpFilter=gt_not_shipped_no_estimated_pick_up_date&salesOrganisation=/locations/30"
    Then the response status code should be 200
    And I should see "ODP"

  Scenario: As a basic user, test that I can filter odp
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/on_time_delivery_planning/show"
    Then the response status code should be 200
    Then I select "location_sso" from "salesOrganisation"
    Then I select "location_factory" from "manufacturerLocation"
    Then I select "catalog_product_10" from "product"
    Then I select "Type Ex" from "productType"
    Then I select "iBS" from "emissionRating"
    Then I select "ER COMBINED" from "combinationMode"
    Then I fill in "serialNumber" with "T78842"
    Then I fill in "orderLineNumber" with "12345"
    Then I fill in "greenTagDateBefore" with "06/01/2028"
    Then I fill in "greenTagDateAfter" with "06/01/2028"
    And press "FILTER"
    Then the response status code should be 200
    And I should be on "/support/on_time_delivery_planning/show"
    And I should see "ODP"

  Scenario: As an ASM, test the filter Main Contact Point (ASM)
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/on_time_delivery_planning/show"
    Then the response status code should be 200
    And I should see "Martin"