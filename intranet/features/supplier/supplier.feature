Feature: Supplier
  Scenario: As a superuser, test that i'm allowed to see supplier page
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/suppliers"
    Then the response status code should be 200
    And I should see "Code"
    And I should see "Name"
    And I should see "Buy from"
    And I should see "FILTER"
    And I should see "RESET"
    And I should see an "a[href$='/purchasing/suppliers/6/show']" element
    When I go to "/purchasing/suppliers/6/show"
    Then the response status code should be 200
    And I should see "ALBRIGHT FRANCE"
    And I should see "location_factory"
    And I should see "USD"
    And I should see "ACTIVE"
    And I should see "Vendor Users"
    And I should see "No vendor users found"
    And I should see an "a[href*='supplier_ranking'][href*='expertise_levels/1'][href*='OCM']" element
    And I should see an "a[href*='vendor-warranty-claims'][href*='ALB0018']" element
    And I should see an "a[href*='non-conformities'][href*='ALB0018']" element
    And I should see an "a[href*='supplier-corrective-action-requests'][href*='ALB0018']" element

  Scenario: As a superuser, test that i can redirect to Supplier Ranking Page from Supplier
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/supplier-rankings/5?expertiseLevel[@id]=/purchasing/supplier_ranking/expertise_levels/1&expertiseLevel[@type]=ExpertiseLevel&expertiseLevel[name]=OCM&expertiseLevel[id]=1"
    Then the response status code should be 200
    And I should see "Supplier Ranking"
    And I should see "ALBRIGHT FRANCE"
    And I should see "ALB0018"
    And I should see "5"
    And I should see "OCM"

  Scenario: As a superuser, test that i can redirect to VWC page filtered by supplier from Supplier
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-warranty-claims?filter_vendor_warranty_claims[supplierNumber][value]=ALB0018"
    Then the response status code should be 200
    And I should see "Supplier number"
    And I should see "VWC"
    And I should see "ALB0018"


  Scenario: As a superuser, test that i can redirect to NCR page filtered by supplier from Supplier
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/non-conformities?filter_non_conformity[supplierNumber][value]=ALB0018"
    Then the response status code should be 200
    And I should see "Supplier number"
    And I should see "NCR"
    And I should see "ALB0018"
    And I should see "NCR Count by status, location"
    And I should see "location_factory"
    And I should see "location_factory_2"
    And I should see "Totals"

  Scenario: As a superuser, test that i can redirect to SCAR page filtered by supplier from Supplier
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/supplier-corrective-action-requests?filter_supplier_corrective_action_request[supplierNumber][value]=ALB0018"
    Then the response status code should be 200
    And I should see "Supplier number"
    And I should see "Supplier Corrective Action Request"
    And I should see "ALB0018"
    And I should see "SCAR Count by Location, Status"
    And I should see "location_factory"
    And I should see "location_factory_2"
    And I should see "Totals"
