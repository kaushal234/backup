Feature: Purchasing / VWC

  Scenario: As an anonymous user, test that i'm not allowed to see VWC pages
    When I go to "/purchasing/vendor-warranty-claims"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/purchasing/vendor-warranty-claims/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see VWC pages detail
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-warranty-claims"
    Then the response status code should be 200
    And The module should be "VWC"
    And I should see "ID"
    And I should see "Status"
    And I should see "Created at"
    When I go to "/purchasing/vendor-warranty-claims/1/show"
    Then the response status code should be 200
    And I should see "VWC#1"
    And I should see "Finance/Shipping"
    And I should see "Files"
    And I should see "Links"
    And I should see "Tasks"
    And I should see "Logs"
    And I should see "Followers"

  Scenario: As a basic user, test that i'm allowed to see VWC matrix
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-warranty-claims/matrix"
    Then the response status code should be 200
    And I should see "VWC Count by Status, Assignee"
    And I should see "VWC Count by Status, Location"

  Scenario: As a basic user, test that i can filter by status and location
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-warranty-claims?filter_vendor_warranty_claims[department][value]=&filter_vendor_warranty_claims[location][value]=%2Flocations%2F29&filter_vendor_warranty_claims[status][value][]=%2Fpurchasing%2Fvendor_warranty_claim_statuses%2F3&filter_vendor_warranty_claims[partNumber][value]=&filter_vendor_warranty_claims[supplierNumber][value]=&filter_vendor_warranty_claims[supplierName][value]=&filter_vendor_warranty_claims[serialNumber][value]=&filter_vendor_warranty_claims[closedAt][value][from]=&filter_vendor_warranty_claims[closedAt][value][to]=&filter_vendor_warranty_claims[createdAt][value][from]=&filter_vendor_warranty_claims[createdAt][value][to]=&filter_vendor_warranty_claims[costPaidBySupplier][value]=&filter_vendor_warranty_claims[accepted][value]=&filter_vendor_warranty_claims[nonConformityId][value]=&filter_vendor_warranty_claims[warrantyClaimId][value]=&page_vendor_warranty_claims=1&limit_vendor_warranty_claims=25&filter_vendor_warranty_claims[status][value][0]=%2Fpurchasing%2Fvendor_warranty_claim_statuses%2F3"
    Then the response status code should be 200
    And I should see "DANA SAS FRANCE"
    And I should see "DAN0013"
    And I should see "SUPERUSER user"
    And I should not see "BASIC user"
    And I should not see "give the money back"

  Scenario: As basic user I should click on matrix link an be redirected to filtered VWC
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-warranty-claims/matrix"
    Then the response status code should be 200
    And I should see an "a[href$='/purchasing/vendor-warranty-claims?filter_vendor_warranty_claims%5Blocation%5D%5Bvalue%5D=/locations/29&filter_vendor_warranty_claims%5Bstatus%5D%5Bvalue%5D%5B%5D=/purchasing/vendor_warranty_claim_statuses/2']" element
    When I go to "/purchasing/vendor-warranty-claims?filter_vendor_warranty_claims%5Blocation%5D%5Bvalue%5D=/locations/29&filter_vendor_warranty_claims%5Bstatus%5D%5Bvalue%5D%5B%5D=/purchasing/vendor_warranty_claim_statuses/2"
    And I should see "WC#59"
    And I should not see "WC#58"

  @authentication
  Scenario: As a superuser, test that i can add a VWC from NCR
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-warranty-claims/2/add-from-ncr"
    Then the response status code should be 200
    And I select "DAN0013" from "vendor_warranty_claim_add[supplierNumber]"
    And I fill in "vendor_warranty_claim_add[supplierStockVerified]" with "text supplierStockVerified"
    And I fill in "vendor_warranty_claim_add[tldStockVerified]" with "text tldStockVerified"
    And I fill in "vendor_warranty_claim_add[issueOrigin]" with "text issueOrigin"
    And I fill in "vendor_warranty_claim_add[correctiveAction]" with "text correctiveAction"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/purchasing/vendor-warranty-claims/6/show"
    And I should see an "a[href$='/quality/non-conformities/2/show']" element
    And I should see "VWC#6"
    And I should see "text supplierStockVerified"
    And I should see "text tldStockVerified"
    And I should see "text issueOrigin"
    And I should see "text correctiveAction"
    And I should see "VWC has been successfully created."

  @authentication
  Scenario: As a superuser, test that i can edit a VWC
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-warranty-claims/6/edit"
    Then the response status code should be 200
    Then I select "/locations/30" from "vendor_warranty_claim_edit[location]"
    And I select "DAN0013" from "vendor_warranty_claim_edit[supplierNumber]"
    And I fill in "vendor_warranty_claim_edit[supplierStockVerified]" with "text supplierStockVerified edited"
    And I fill in "vendor_warranty_claim_edit[tldStockVerified]" with "text tldStockVerified edited"
    And I fill in "vendor_warranty_claim_edit[issueOrigin]" with "text issueOrigin edited"
    And I fill in "vendor_warranty_claim_edit[correctiveAction]" with "text correctiveAction edited"
    And I check "vendor_warranty_claim_edit_scarRequested"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/purchasing/vendor-warranty-claims/6/show"
    And I should see "location_factory_2"

  @authentication
  Scenario: A superuser can upload a file
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-warranty-claims/6/show/files"
    Then the response status code should be 200
    And I attach the file "file.pdf" to "simple_file[file]"
    And I fill in "simple_file[description]" with "this is a file description"
    And press "simple_file[submit]"
    Then the response status code should be 200
    And I should be on "/purchasing/vendor-warranty-claims/6/show/files"
    And I should see "this is a file description"

  @authentication
  Scenario: A superuser can see an uploaded VWC file
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "purchasing/vendor-warranty-claims/1/files/12"
    Then the response status code should be 200

  @authentication
  Scenario: As a superuser, test that I can switch status
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-warranty-claims/6/show"
    Then the response status code should be 200
    And I should see "QA ANALYSIS"
    When I go to "/purchasing/vendor-warranty-claims/6/status/CREATE_PO"
    Then the response status code should be 200
    And I should be on "/purchasing/vendor-warranty-claims/6/show"
    And I should see "CREATE_PO"
    And I should see "Status of VWC has been successfully updated."
    When I go to "/purchasing/vendor-warranty-claims/6/status/CLOSED_RESOLVED"
    Then the response status code should be 200

  @authentication
  Scenario: As a superuser, test that I can use VWC report
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-warranty-claims/report"
    Then the response status code should be 200
    And I should see "FILTER SUPPLIER"
    And I should see "FILTER BUYER"
    Then I select "/locations/29" from "location"
    And press "submit"
    Then the response status code should be 200
    And I should see "Top part failure"
