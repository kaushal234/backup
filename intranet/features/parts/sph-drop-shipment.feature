Feature: SPH KPI Drop Shipment

  Scenario: As an anonymous user, test that i'm not allowed to see drop shipment report
    When I go to "/parts/kpi/drop-shipments"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see drop shipment report
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/kpi/drop-shipments?orderDate[after]=2019-12-01&orderDate[before]=2019-12-31"
    Then the response status code should be 200
    And The module should be "SPH"
    And I should not see a "#invoice-lines" element
    And I should see "Drop Shipment Report"

  Scenario: As a basic user, test that i'm allowed to see drop shipment report invoice lines
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/kpi/drop-shipments?supplier=/erp/suppliers/suno%3D10057E%3Berp%3D300&sph=/locations/24&orderDate%5Bafter%5D=2019-12-01&orderDate%5Bbefore%5D=2019-12-31"
    Then the response status code should be 200
    And The module should be "SPH"
    And I should see a "#invoice-lines" element
