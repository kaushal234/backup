Feature: First Article Qualifications
  Scenario: As an anonymous user, test that i'm not allowed to see FAQ pages
    When I go to "/quality/first-article-qualifications"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/quality/first-article-qualifications/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see FAQ homepage
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/first-article-qualifications"
    Then the response status code should be 200
    And I should see an "a[href$='/quality/first-article-qualifications/search']" element
    And I should not see an "a[href$='/quality/first-article-qualifications/add']" element
    And I should see "First article qualifications status by factory"
    And I should see "First article qualifications plan status by factory"

  Scenario: As a basic user, test that i'm allowed to filter FAQ
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/first-article-qualifications/search"
    And I select "/locations/29" from "location"
    And I select "BOB, Patrick" from "buyer"
    And I select "QAM, user" from "owner"
    And I select "AP, user" from "poster"
    And I fill in "planDefinitionDueDate-after" with "06/01/2023"
    And I fill in "planDefinitionDueDate-before" with "06/01/2043"
    And I select "PENDING" from "status"
    And I fill in "partNumber" with "PN1231"
    And I fill in "revision" with "2"
    And I fill in "description" with "Urgent"
    And I fill in "eap" with "123456"
    And I fill in "meap" with "654321"
    And I fill in "supplierName" with "Airbouze"
    And I fill in "supplierNumber" with "Air75"
    And I select "CAB- Rear lights" from "tags"
    And I fill in "dueDate-after" with "06/01/2024"
    And I fill in "dueDate-before" with "06/01/2044"
    And I fill in "equipmentRecord" with "T91754"
    And I select "APPROVED" from "planApprovalStatus"
    And I fill in "createdAt-after" with "06/01/2025"
    And I fill in "createdAt-before" with "06/01/2045"
    And I fill in "completedAt-after" with "06/01/2026"
    And I fill in "completedAt-before" with "06/01/2046"
    And I select "IF1" from "iFactor"
    And I check "noOpenTasks"
    And I check "noPlan"
    And I press "FILTER"
    Then the response status code should be 200
    And I should be on "/quality/first-article-qualifications/search"

  Scenario: As a basic user, test that i'm allowed to download FAQ
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/first-article-qualifications/search"
    And I select "/locations/29" from "location"
    And I press "DOWNLOAD"
    Then the response status code should be 200

  Scenario: As a superuser, test that i'm allowed to add FAQ
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/first-article-qualifications/add"
    Then the response status code should be 200

  Scenario: As a superuser, test that i'm allowed to edit FAQ
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/first-article-qualifications/1/edit"
    Then the response status code should be 200

  Scenario: As a superuser, test that i'm allowed to edit FAQ plan
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/first-article-qualifications/1/plan"
    Then the response status code should be 200

  Scenario: As a superuser, test that i'm allowed to add FAQ files
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/first-article-qualifications/1/files"
    Then the response status code should be 200

  Scenario: As a basic user, test that i'm allowed to see FAQ detail
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/first-article-qualifications/1/show"
    Then the response status code should be 200
    And I should not see an "a[href$='/quality/first-article-qualifications/1/plan']" element
    And I should not see an "a[href$='/quality/first-article-qualifications/1/edit']" element
    And I should see an "a[href$='/quality/first-article-qualifications/1/files']" element
    And I should see "First Article Qualification"
    And I should see "First Article Qualification Files"
    And I should see "Plan Definition"
    And I should see "Equipment Record"
    And I should see "Part Number"
    And I should see "members"
    And I should see "tasks"
    And I should see "tags"
    And I should see "links"
    And I fill in "comment[message]" with "Hello mon pepito"
    And I press "submit"
    Then the response status code should be 200
    And I should be on "/quality/first-article-qualifications/1/show"
    And I should see "Hello mon pepito"

  Scenario: As a superuser, test that i'm allowed to change FAQ status
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/first-article-qualifications/1/status/REJECTED"
    Then the response status code should be 200
    And I should be on "/quality/first-article-qualifications/1/show"
    And I should see "REJECTED"
    And I should not see an "a[href$='/quality/first-article-qualifications/1/plan']" element
    And I should not see an "a[href$='/quality/first-article-qualifications/1/edit']" element

  Scenario: As a superuser, test that I can add a first article qualification
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/first-article-qualifications/add"
    Then I should see "Add FAQ"
    And I select "/locations/11" from "first_article_qualification[location]"
    And I select "/people/60" from "first_article_qualification[buyer]"
    And I select "/people/29" from "first_article_qualification[owner]"
    And I select "IF10" from "first_article_qualification[iFactor]"
    And I fill in "first_article_qualification[planDefinitionDueDate]" with "05/06/2099"
    And I fill in "first_article_qualification[deliverablesDueDate]" with "07/20/2099"
    And I fill in "first_article_qualification[dueDate]" with "06/06/2099"
    And I fill in "first_article_qualification[eap]" with "100"
    And I fill in "first_article_qualification[meap]" with "150"
    And I select "/quality/first_article_qualification_tags/13" from "first_article_qualification[tags][]"
    And I select "/people/3" from "first_article_qualification[members][]"
    And I additionally select "/people/32" from "first_article_qualification[members][]"
    And I select "/equipment_records/14" from "first_article_qualification[equipmentRecords][]"
    And I select "/sales/product_families/13" from "first_article_qualification[productFamily]"
    And I select "/ion/items/site=400;item=00571060" from "first_article_qualification[partNumbers][0][number]"
    And I fill in "first_article_qualification[partNumbers][0][revision]" with "A"
    And I fill in "first_article_qualification[partNumbers][0][description]" with "Test part number"
    When I press "Save"
    And I wait until I see "FAQ successfully created"
    And I should be on "/quality/first-article-qualifications/32/show"
    And I should see "00571060"
    And I should see "Jul 20, 2099"

  Scenario: As a superuser, test that I can add a first article qualification
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/first-article-qualifications/add?erp=400"
    Then I should see "Add FAQ"
    And I select "/people/60" from "first_article_qualification[buyer]"
    And I select "/people/29" from "first_article_qualification[owner]"
    And I select "IF10" from "first_article_qualification[iFactor]"
    And I select "7FTA001" from "first_article_qualification[supplier]"
    And I fill in "first_article_qualification[planDefinitionDueDate]" with "05/06/2099"
    And I fill in "first_article_qualification[dueDate]" with "06/06/2099"
    And I fill in "first_article_qualification[eap]" with "100"
    And I fill in "first_article_qualification[meap]" with "150"
    And I select "/quality/first_article_qualification_tags/13" from "first_article_qualification[tags][]"
    And I select "/people/3" from "first_article_qualification[members][]"
    And I additionally select "/people/32" from "first_article_qualification[members][]"
    And I select "/equipment_records/14" from "first_article_qualification[equipmentRecords][]"
    And I select "/sales/product_families/13" from "first_article_qualification[productFamily]"
    And I select "/ion/items/site=400;item=00571060" from "first_article_qualification[partNumbers][0][number]"
    And I fill in "first_article_qualification[partNumbers][0][revision]" with "A"
    And I fill in "first_article_qualification[partNumbers][0][description]" with "Test part number"
    When I press "Save"
    And I wait until I see "FAQ successfully created"
    And I should be on "/quality/first-article-qualifications/33/show"
    And I should see "00571060"
    And I should see "7FTA001"

  Scenario: As a superuser, test that I can edit a first article qualification
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/first-article-qualifications/32/edit"
    Then I should see "Edit FAQ"
    And I select "/locations/20" from "first_article_qualification[location]"
    And I select "IF1" from "first_article_qualification[iFactor]"
    And I fill in "first_article_qualification[planDefinitionDueDate]" with "10/06/2099"
    And I fill in "first_article_qualification[deliverablesDueDate]" with "07/25/2099"
    And I fill in "first_article_qualification[dueDate]" with "11/06/2099"
    And I fill in "first_article_qualification[eap]" with "250"
    And I fill in "first_article_qualification[meap]" with "350"
    And I select "/quality/first_article_qualification_tags/10" from "first_article_qualification[tags][]"
    And I select "/people/76" from "first_article_qualification[members][]"
    And I select "/equipment_records/23" from "first_article_qualification[equipmentRecords][]"
    And I select "/sales/product_families/9" from "first_article_qualification[productFamily]"
    And I select "/ion/items/site=500;item=0039616" from "first_article_qualification[partNumbers][0][number]"
    And I fill in "first_article_qualification[partNumbers][0][revision]" with "B"
    And I fill in "first_article_qualification[partNumbers][0][description]" with "Updated WD40"
    When I press "Save"
    And I wait until I see "FAQ successfully updated"
    And I should be on "/quality/first-article-qualifications/32/show"
    And I should see "0039616"
    And I should see "Jul 25, 2099"
    And I should see "Transfer EAP"
    And I should see "Transfer NCR"

  Scenario: As a superuser, arriving via legacy link with erp pre-fills the factory
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/first-article-qualifications/add?erp=500"
    Then the response status code should be 200
    And I should see "Add FAQ"

  Scenario: As a superuser, arriving via legacy link with erp and part number pre-fills the factory and item details
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/first-article-qualifications/add?erp=500&partNumbers[]=0024635"
    Then I should see "Add FAQ"
    And I select "/people/60" from "first_article_qualification[buyer]"
    And I select "/people/29" from "first_article_qualification[owner]"
    And I select "IF10" from "first_article_qualification[iFactor]"
    And I fill in "first_article_qualification[planDefinitionDueDate]" with "05/06/2099"
    And I fill in "first_article_qualification[dueDate]" with "06/06/2099"
    And I fill in "first_article_qualification[eap]" with "100"
    And I fill in "first_article_qualification[meap]" with "150"
    And I select "/quality/first_article_qualification_tags/13" from "first_article_qualification[tags][]"
    And I select "/people/3" from "first_article_qualification[members][]"
    And I additionally select "/people/32" from "first_article_qualification[members][]"
    And I select "/equipment_records/14" from "first_article_qualification[equipmentRecords][]"
    And I select "/sales/product_families/13" from "first_article_qualification[productFamily]"
    And I fill in "first_article_qualification[partNumbers][0][revision]" with "A"
    When I press "Save"
    And I wait until I see "FAQ successfully created"
    And I should be on "/quality/first-article-qualifications/34/show"
    And I should see "0024635"

  Scenario: As a superuser, test that i'm allowed to transfer a FAQ to a new non conformity
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/non-conformities/add?faq=1"
    Then the response status code should be 200
    And I should see "Add NCR"
