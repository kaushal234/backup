Feature: Service / Customer Service Record

  Scenario: As an anonymous user, test that i'm not allowed to see csr pages
    When I go to "/service/customer-service-records"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/service/customer-service-records/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see csr pages detail
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/customer-service-records"
    Then the response status code should be 200
    And The module should be "CSR"
    And I should see "Last 10 CSRs created"
    And I should see "FILTER"
    And I should see an "a[href$='/service/customer-service-records/add']" element
    And I should see an "a[href$='/service/customer-service-records']" element
    And I should see an "a[href$='/service/customer-service-records/reports']" element
    And I should see an "a[href$='/service/customer-service-records/planner']" element
    And I should see an "a[href$='/service/customer-service-records/search']" element
    When I go to "/service/customer-service-records/1/show"
    Then the response status code should be 200
    And I should see "CSR"
    And I should see "Equipment Record"
    And I should see "Airport"
    And I should see "Customer"
    And I should see "Interventions"

  Scenario: As a basic user, test that I'm allowed to see csr report page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/customer-service-records/reports"
    Then the response status code should be 200
    And I should see "CSRs count by status by SSO Service"

  Scenario: As a basic user, test that I'm not allowed to see csr add page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/customer-service-records"
    Then the response status code should be 200
    When I go to "/service/customer-service-records/add"
    Then the response status code should be 200
    And I should be on "/service/customer-service-records/search"
    And I should see "You do not have permissions"

  Scenario: As a csm user, test that i can add a csr
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/customer-service-records/add"
    And I select "/equipment_records/13" from "customer_service_record[equipmentRecord]"
    And I select "/airports/62" from "customer_service_record[airport]"
    And I fill in "customer_service_record[title]" with "William va etre papa ?"
    And I fill in "customer_service_record[description]" with "Guillaume est quelqu'un de super sympa, des fois il sourit"
    And I fill in "customer_service_record[hourmeter]" with "666"
    And I select "Commissioning" from "customer_service_record[module]"
    And press "customer_service_record[submit]"
    Then the response status code should be 200
    And I should see "William va etre papa ?"
    And I should see "Guillaume est quelqu'un de super sympa, des fois il sourit"
    And I should see "CSR has been successfully created."
    And I should see " Hourmeter updated to 666"
    And I should be on "/service/customer-service-records/22/show"

  Scenario: As a basic user, test that I'm not allowed to see csr edit page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/customer-service-records"
    Then the response status code should be 200
    When I go to "/service/customer-service-records/8/edit"
    Then the response status code should be 200
    And I should see "You do not have permissions"

  Scenario: As a csm, test that i can edit a csr
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/customer-service-records/20/edit"
    Then the response status code should be 200
    And I should be on "/service/customer-service-records/20/edit"
    Then I should not see "ER SN#"
    Then I should not see "Hourmeter"
    Then I should not see "CSR Type"
    Then I should not see "Type Number#"
    And I fill in "customer_service_record[title]" with "Et il est toujours en congés pour son anniversaire"
    And I fill in "customer_service_record[description]" with "Mais la plupart du temps il fait la tronche"
    And press "customer_service_record[submit]"
    Then the response status code should be 200
    And I should be on "/service/customer-service-records/20/show"

  Scenario: As a basic user I should see filtered search page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/customer-service-records"
    Then the response status code should be 200
    And I select "toc" from "discriminator"
    And I select "commissioning" from "discriminator"
    And I select "/locations/31" from "location"
    And I select "/locations/31" from "salesOrganisation"
    And I press "FILTER"
    And I should be on "/service/customer-service-records/search?createdBy=&leader=&endUser=&createdAfter=&createdBefore=&location=&salesOrganisation=%2Flocations%2F31&salesOrganisationService=%2Flocations%2F31&equipmentRecord=&airport=&discriminator[]=toc&discriminator[]=commissioning"
    And I should see "MAP will display a maximum of 500 CSR if one or multiple filters are applied for loading speed reason"
    And I should see "CSRs filtered"

  Scenario: As a basic user I should be able to use date filters
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/customer-service-records"
    Then the response status code should be 200
    And I fill in "createdAfter" with "01/01/2022"
    And I fill in "createdBefore" with "01/01/2023"
    And I fill in "completedAfter" with "01/01/2022"
    And I fill in "completedBefore" with "01/01/2023"
    And I press "FILTER"
    Then I should be on "/service/customer-service-records/search?createdBy=&leader=&endUser=&createdAfter=01%2F01%2F2022&createdBefore=01%2F01%2F2023&completedAfter=01%2F01%2F2022&completedBefore=01%2F01%2F2023&location=&salesOrganisation=&salesOrganisationService=&equipmentRecord=&airport="
    And I should see "MAP will display a maximum of 500 CSR if one or multiple filters are applied for loading speed reason"
    And I should see "CSRs filtered"

  Scenario: As a basic user, test that i can download filtered CSR
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/customer-service-records"
    And I press "download"
    Then the response status code should be 200

  Scenario: As a basic user, test that I'm allowed to see csr planner page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/customer-service-records/planner"
    Then the response status code should be 200
    And I should see "Filter"
    And I should see "Pool"

  Scenario: As a basic user, test I'm able to filter CSR on csr planner page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/customer-service-records/planner"
    Then the response status code should be 200
    And I should see "CSR#1"
    And I select "/airports/62" from "planner_filter[airport]"
    And I select "commissioning" from "planner_filter[discriminator]"
    And I select "/locations/31" from "planner_filter[salesOrganisation]"
    And I press "FILTER"
    And I should be on "/service/customer-service-records/planner?planner_filter[airport]=%2Fairports%2F62&planner_filter[discriminator]=commissioning&planner_filter[equipmentRecord]=&planner_filter[deliveredCountry]=&planner_filter[salesOrganisation]=%2Flocations%2F31&planner_filter[salesOrganisationService]="
    And I should not see "CSR#1 "

  Scenario: As a csm user, test I should see my dashboard on home page
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/customer-service-records"
    Then the response status code should be 200
    And I should see "CSR - Customer Service Request- dashboard"
    And I should be on the exact url "/service/dashboard/dashboard-csm"

  Scenario: As a csm user, test I should see my completed CSR list
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/customer-service-records/multiple_closure"
    Then the response status code should be 200
    And I should see "CSR to be closed (max 500)"

  Scenario: As an ast user, test I should see my dashboard on home page
    Given I authenticate as "user-ast@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/customer-service-records"
    Then the response status code should be 200
    And I should be on the exact url "/service/dashboard/dashboard-ast"
    And I should not see "CSR Completed List"

  Scenario: As a basic user, test I should see the home page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/customer-service-records"
    Then the response status code should be 200
    And I should see "MAP will display only the 10 most recent CSR if no filters are applied"
    And I should see "Last 10 CSRs created"
    And I should see "FILTER"

  Scenario: As an DSS user, test I should see my team dashboard
    Given I authenticate as "user-dss@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/customer-service-records"
    Then the response status code should be 200
    And I should not see "My Team Dashboard"

  Scenario: A basic user I can't delete a CSR
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/customer-service-records/8/delete"
    Then the response status code should be 200
    And I should see "You do not have permissions"

  Scenario: A CSM user I can delete a CSR
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/customer-service-records/8/edit"
    Then I should see "delete"

  @javascript
  Scenario: As a service user, I can show KPI
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/kpi/backlog"
    Then I should see "CSR Back log"
