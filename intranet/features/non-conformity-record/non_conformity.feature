Feature: Quality / NCR

  Scenario: As an anonymous user, test that i'm not allowed to see ncr pages
    When I go to "/quality/non-conformities"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/quality/non-conformities/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see ncr pages detail
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/non-conformities"
    Then the response status code should be 200
    And The module should be "NCR"
    And I should see "NCR Count by status, location"
    And I should see "Filter"
    And I should see an "a[href$='/quality/non-conformities/add']" element
    When I go to "/quality/non-conformities/1/show"
    Then the response status code should be 200
    And I should see "NCR#1"
    And I should see "Action-Con"
    And I should see "Costs"
    And I should see "Files"
    And I should see "Links"
    And I should see "Tasks"
    And I should see "Logs"
    And I should see "Rush?"
    And I should see an "a[href$='/support/serials/14/show']" element

  Scenario: As a basic user, test that i'm allowed to see ncr report part page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/non-conformities/report-part"
    Then the response status code should be 200
    And The module should be "NCR"
    And I should see "FILTER PART MOST NCR"
    And I should see "Filter"
    And I should see an "a[href$='/quality/non-conformities/add']" element

  @javascript
  Scenario: As a superuser, test that i can add a ncr
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/non-conformities/add"
    Then I select "/locations/29" from "non_conformity_add[location]"
    And I fill in "non_conformity_add[hours]" with "15"
    And I select "IF10" from "non_conformity_add[iFactor]"
    And I check "non_conformity_add_rush"
    And I attach the file "file.pdf" to "non_conformity_add[mainFile]"
    And I select "/people/12" from "non_conformity_add[reportedBy]"
    And the "non_conformity_add[safety]" checkbox is unchecked
    And I fill in "non_conformity_add[shortDescription]" with "Balkani il prend l'argent"
    And I fill in "non_conformity_add[problem]" with "Je paye trop d'impôt"
    And I fill in "non_conformity_add[investigation]" with "Balka fini en prison"
    And I select "/equipment_records/14" from "non_conformity_add[equipmentRecords][]"
    And press "non_conformity_add[no_parts_to_add]"
    And I wait until I see "NCR has been added successfully."
    And I should be on "/quality/non-conformities/6/show"
    And I should see "Balkani il prend l'argent"
    And I should see "Produit Test"

  @javascript
  Scenario: As a superuser, test that i can edit a ncr
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/non-conformities/1/edit"
    Then I select "/locations/30" from "non_conformity_edit[location]"
    And I should see "Details"
    And I should see "Description / Investigation"
    And I should see "Actions"
    And I should see "Conclusion"
    And I should see "Costs Involved"
    And I should see "Vendor Info"
    And I should see "Models"
    And I fill in "non_conformity_edit[hours]" with "16"
    And I select "IF1" from "non_conformity_edit[iFactor]"
    And I check "non_conformity_edit_rush"
    And I select "Hydraulic" from "non_conformity_edit[failureType]"
    And I select "/quality/processes/1" from "non_conformity_edit[processes][]"
    And I select "/quality/responsibles/2" from "non_conformity_edit[responsibles][]"
    And I fill in "non_conformity_edit[purchaseOrderNumber]" with "PO125544"
    And I uncheck "non_conformity_edit_environmentalIssue"
    And I check "non_conformity_edit_safety"
    And I check "non_conformity_edit_scrap"
    And I check "non_conformity_edit_rework"
    And I check "non_conformity_edit_firstArticleInspection"
    And I check "non_conformity_edit_useAsIs"
    And I check "non_conformity_edit_returnVendor"
    And I check "non_conformity_edit_chargeVendorForRepair"
    And I check "non_conformity_edit_supplierCorrectiveActionRequest"
    And I check "non_conformity_edit_internalCorrectiveActionRequest"
    And I check "non_conformity_edit_containment"
    And I check "non_conformity_edit_other"
    And I check "non_conformity_edit_rush"
    And I fill in "non_conformity_edit[actionComment]" with "We should take action like DAB"
    And I select "/people/76" from "non_conformity_edit[repairApprover]"
    And I fill in "non_conformity_edit[repairApprovalDate]" with "05/06/2022"
    And I fill in "non_conformity_edit[solution]" with "The solution is to eat more fruit"
    And I select "CAD" from "non_conformity_edit[currency]"
    And I fill in "non_conformity_edit[cost]" with "1212"
    And I fill in "non_conformity_edit[nonQualityCost]" with "8564"
    And I fill in "non_conformity_edit[workOrderReference]" with "8564541"
    And I fill in "non_conformity_edit[invoiceNumber]" with "754951"
    And I fill in "non_conformity_edit[costBreakdown]" with "It is too much and cost like 42 bananas"
    And I select "DAN0013" from "non_conformity_edit[supplierNumber]"
    And I attach the file "picture.png" to "non_conformity_edit[mainFile]"
    And I fill in "non_conformity_edit[problem]" with "Je paye trop d'impôt"
    Then I fill in "non_conformity_edit[investigation]" with "Balka fini en prison"
    And press "non_conformity_edit[submit]"
    And I wait until I see "NCR has been successfully edited."
    And I should be on "/quality/non-conformities/1/show"
    And I should see "ENGINEERING"
    And I should see "DAN0013"

  Scenario: A superuser can upload a file
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/non-conformities/1/show"
    Then the response status code should be 200
    And I attach the file "file.pdf" to "form_files[file]"
    And I fill in "form_files[description]" with "this is a file description"
    And press "form_files[submit]"
    Then the response status code should be 200
    And I should be on "/quality/non-conformities/1/show"
    And I should see "this is a file description"

  Scenario: As a superuser, test that I can see files page
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/non-conformities/1/show"
    Then the response status code should be 200
    And I should see "Files"
    And I should see "Upload a file"
    And I should see "File description"
    And I should see "Public"
    And I should see "Change Visibility"
    And I should see "this is a file description "
    And I attach the file "file.pdf" to "form_files[file]"
    And I fill in "form_files[description]" with "this is an updated description"
    And press "form_files[submit]"
    Then the response status code should be 200
    And I should be on "/quality/non-conformities/1/show"
    And I should see "File has been successfully added."
    And I should see 2 "#files table.footable tbody tr" elements
    And I should see "this is an updated description"

  Scenario: As a basic user, test that I can not see change_visibility button on ncr files
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/non-conformities/1/show"
    Then the response status code should be 200
    And I should see "Files"
    And I should see "Public"
    And I should not see "Change Visibility"

  Scenario: As a superuser, test that I can switch status
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/non-conformities/1/show"
    Then the response status code should be 200
    And I should see "IN PROGRESS"
    When I go to "/quality/non-conformities/1/status/IN PROGRESS"
    Then the response status code should be 200
    And I should be on "/quality/non-conformities/1/show"
    And I should see "IN PROGRESS"
    And I should see "Status of NCR has been successfully updated."
    And I should see "Tx CRAB"
    And I should see "Tx EAP"
    And I should see "Tx SCAR"

  Scenario: As a superuser, test that I use NCR report
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/non-conformities/report"
    Then the response status code should be 200
    And I should see "FILTER SUPPLIER MOST NCR"
    Then I select "/locations/30" from "location"
    And press "FILTER"
    Then the response status code should be 200
    And I should be on "/quality/non-conformities/report"
    And I should see 2 "table.footable tbody tr" elements
    And I should see "DANA SAS FRANCE"
    And I should see "Nuts 2000"
    And the "table.footable tbody tr:nth-child(1) td:nth-child(3)" element should contain "2"
    When I go to "/quality/non-conformities?supplierNumber=DAN0013&location=/locations/30&responsibles=/quality/responsibles/2"
    Then the response status code should be 200

  Scenario: As a basic user, i'm allowed to see non quality costs page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/non-quality-costs"
    Then the response status code should be 200
    And I should see "Non Quality Costs"
    And I should see "location_factory"
