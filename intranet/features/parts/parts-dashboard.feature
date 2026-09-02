Feature: Parts / dashboard

  Scenario: As a basic user, test that i'm not allowed to see this dashboard pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/dashboard/1018150-P8"
    Then the response status code should be 200
    And I should not see "Search inventory"

  Scenario: As a authorized user, test that i'm allowed to see dashboard
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/dashboard/1018150-P8"
    Then the response status code should be 200
    And I should see "Search inventory"
    And I should see "MIP Detail"
    And I should see "MIP"
    And I should see "Warehouse"
    And I should see "Images"
    And I should see "Text for PN# 1018150-P8"
    And I should see an "table[class$='table-striped']" element

  Scenario: As a sage parts user, test that i'm allowed to see dashboard with light view
    Given I authenticate as "user-sageparts@Sageparts.com" with "P@ssw0rd15chars"
    When I go to "/parts/dashboard/1018150-P8"
    Then the response status code should be 200
    And I should not see an "button#dashboard_filter_submit_show_shipments" element
    And I should not see "Multiplier" in the "table tbody tr td" element
    And I should not see "Warehouse Safety" in the "table thead tr th" element
    And I should not see "Item Safety" in the "table thead tr th" element
    And I should not see "On hand" in the "table thead tr th" element
    And I should not see "On order" in the "table thead tr th" element
    And I should not see "Alloc" in the "table thead tr th" element
    And I should not see "Pur Cur" in the "table thead tr th" element
    And I should not see "Pur Price" in the "table thead tr th" element
    And I should not see "Last Pur Price Date" in the "table thead tr th" element
    And I should not see "STD Cur" in the "table thead tr th" element
    And I should not see "UM" in the "table thead tr th" element
    And I should not see "STD Cost" in the "table thead tr th" element
    And I should not see "ADM" in the "table thead tr th" element
    And I should not see "BOM" in the "table thead tr th" element
    And I should not see "Text for PN#" in the "table thead tr th" element
    And I should not see "MIP" in the "h5" element
    And I should not see "Value" in the "table thead tr th" element
    And I should not see "Currency" in the "table thead tr th" element
    And I should not see "Effective date" in the "table thead tr th" element
    And I should not see "Expiry date" in the "table thead tr th" element


  Scenario: As a authorized user, test that i have no result for unknown PN
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/dashboard/1pt"
    Then the response status code should be 200
    And I should see "No results"

  Scenario: As a superuser, test that i can search another PN
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/dashboard/1018150-P8"
    When I fill in "Part Number" with "1234"
    When I press "Search"
    Then the response status code should be 200
    Then I should be on "/parts/dashboard/1234"

  Scenario: As a superuser, test that i can go on PN shipments
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/dashboard/1018150-P8"
    When I fill in "Part Number" with "1018150-P8"
    When I press "Show Shipments"
    Then I should be on the exact url "/parts/parts.php?m[0]=&m[1]=bySearch&erp=300&item=1018150-P8"

  Scenario: As a superuser, test that i click on EDM link table i go to edm pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/dashboard/1018150-P8"
    And I should see " 1018150-P8"
    And I should see "300SP1"
    And I should see an "a[href$='/manufacturing/eng/dev.php?erp=300&item=1018150-P8&m%5B0%5D=edm&m%5B1%5D=view']" element
    When I follow "edm"
    Then I should be on the exact url "/manufacturing/eng/dev.php?erp=300&item=1018150-P8&m%5B0%5D=edm&m%5B1%5D=view"

  Scenario: As a superuser, test that i click on BOM link table i go to bom pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/dashboard/1018150-P8"
    And I should see an "a[href$='/manufacturing/eng/dev.php?erp=300&pn=1018150-P8&m%5B0%5D=bom&m%5B1%5D=view']" element
    When I follow "bom"
    Then I should be on the exact url "/manufacturing/eng/dev.php?erp=300&pn=1018150-P8&m%5B0%5D=bom&m%5B1%5D=view"

  Scenario: As a authorized user, test that i'm allowed to see sage inventory
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/dashboard/sage-inventory/1008905-P5"
    Then the response status code should be 200
    And I should see "Sage Inventory"
    And I should see an "table[class$='table-striped']" element
    And I should see "PN#"
    And I should see "SAGE PN#"
    And I should see "DESCRIPTION"
    And I should see "SAGE UID"
    And I should see "SAGE LOCATION"
    And I should see "PRICE"
    And I should see "CURRENCY"
    And I should see "ON HAND"
    And I should see "ON HAND UNIT"
    And I should see "ON ORDER"
    And I should see "ON ORDER UNIT"

  Scenario: As a authorized user, test that i'm redirected to the dashboard, when sage inventory response give an error
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/dashboard/sage-inventory/1200676"
    Then the response status code should not be 200
    And I should see "Search inventory"
    And I should see "MIP Detail"
    And I should see "MIP"
    And I should see "Images"
    And I should see "Text for PN# 1200676"
    And I should see "An error occurred"
