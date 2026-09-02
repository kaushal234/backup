Feature: Support Manuals

  Scenario: As an anonymous user, test that i'm not allowed to see a manuals homepage
    When I go to "/support/manuals"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see manuals homepage
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/manuals"
    Then the response status code should be 200
    And I should see an "a[href$='/support/manuals']" element
    And The module should be "PUBS"

  Scenario: Quick access should work with Id and legacyId
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/manuals"
    And I fill in the following:
      | app_id_search[id] | 1 |
    And press "id-quick-access-submit"
    Then I should be on "/support/manuals/1/show"
    And the response status code should be 200
    Then I go to "/support/manuals"
    And I fill in the following:
      | app_legacy_id_search[legacyId] | 17327 |
    And press "legacy-id-quick-access-submit"
    Then I should be on "/support/manuals/1/show"
    And the response status code should be 200

  Scenario: As a basic user, test that i'm allowed to validate the cbom of a non publishable equipmentRecord
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/equipment_records/1/manuals"
    And I should see an "a[href$='/product_support/index.ps.php?m[0]=equipment&m[1]=view&id=37455']" element
    And I should see an "a[href$='/support/equipment_records/1/manuals/publish']" element
    And I should see "Manuals list"
    Then I go to "/support/equipment_records/1/manuals/publish"
    And the response status code should be 200
    And I should see "New manual generation"
    And I should see "Equipment Record Information"
    And The module should be "PUBS"
    And I should not see "Specify other equipment"
    And I should see a "#equipment_record_add_manual_cbom_submit" element
    And I should not see a "#equipment_record_add_manual_publishable_submit" element
    And I should not see "Test the CBOM for publishing a manual"

  Scenario: As a basic user, test that i'm allowed to publish a manual on a publishable equipmentRecord and test CBOM
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/equipment_records/16/manuals/publish"
    And the response status code should be 200
    And I should see "Specify other equipment"
    And I should see a "#equipment_record_add_manual_publishable_submit" element
    And I should see "Test the CBOM for publishing a manual"
    And I should see a "button[id=equipment_record_add_manual_cbom_submit]" element

  Scenario: As an anonymous user, test that i'm not allowed to see a manual
    When I go to "/support/manuals/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see a manual
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/manuals/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/support/manuals/1/show']" element
    And I should see an "a[href$='/product_support/index.ps.php?m[0]=equipment&m[1]=view&id=37455']" element
    And I should see an "a[href$='/support/manuals/1/rspl']" element
    And I should see an "a[href$='/support/manuals/1/chapter4']" element
    And I should see an "a[href$='/support/manuals/1/parts_list']" element
    And I should see an "a[href$='/support/manuals/1/zip']" element
    And I should see an "a[href$='/support/manuals/1/qrCode']" element
    And I should not see an "a[href$='/support/manuals/1/edit']" element
    And I should not see an "Other Description" element
    And I should see "Manual details"
    And I should see "Operation and Parts Manual"
    And The module should be "PUBS"

  Scenario: As a basic user, test that i'm not allowed to edit manuals
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/manuals/1/edit"
    And I should see "You do not have permissions"

  Scenario: As a super user, test that i'm allowed see edit link
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/manuals/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/support/manuals/1/edit']" element
    And The module should be "PUBS"

  Scenario: As a super  user, test that i'm allowed to edit manuals
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/manuals/1/edit"
    Then the response status code should be 200
    And I should see an "#manual_documents_0_factoryNumber" element
    And I fill in "manual[documents][0][factoryNumber]" with "LOST_ODYSSEY"
    And press "submit"
    Then the response status code should be 200
    And I should see "LOST_ODYSSEY"
    And The module should be "PUBS"

  Scenario: As an superuser, test that i'm allowed to edit manuals by category and change category on a document
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/manual_documents/4/show"
    Then the response status code should be 200
    And I should see "Document details"
    And the "table tbody tr:nth-child(4) th" element should contain "Category"
    And the "table tbody tr:nth-child(4) td" element should contain "Chapter 2"
    And the "table tbody tr:nth-child(6) td" element should contain "INSTRUMENT PANEL"
    And I should not see "Chapter 1"
    When I go to "/support/manuals/2/edit-documents-by-category/Chapter%202"
    Then the response status code should be 200
    And I fill in "manual_document_collection[documents][1][category]" with "/support/manual_document_categories/1"
    And I fill in "manual_document_collection[documents][1][description]" with "category changed"
    And press "submit"
    Then the response status code should be 200
    Then I should be on "/support/manuals/2/show"
    When I go to "/support/manual_documents/4/show"
    Then the response status code should be 200
    And the "table tbody tr:nth-child(4) th" element should contain "Category"
    And the "table tbody tr:nth-child(4) td" element should contain "Chapter 1"
    And the "table tbody tr:nth-child(6) td" element should contain "category changed"

  Scenario: As an anonymous user, test that i'm not allowed to see manual_documents
    When I go to "/support/manual_documents/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see manual_documents
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/manual_documents/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/support/manuals/1/show']" element
    And I should not see an "a[href$='/support/manual_documents/1/edit']" element
    And I should see "Document details"
    And I should see "File attached"
    And I should see "Parts list"
    And I should see an "a[href$='/support/manual_documents/1/pdf']" element
    And The module should be "PUBS"

  Scenario: As a basic user, test that i'm not allowed to edit manual_documents
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/manual_documents/1/edit"
    And I should see "You do not have permissions"

  Scenario: As a super user, test that i'm allowed see edit link
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/manual_documents/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/support/manual_documents/1/edit']" element
    And The module should be "PUBS"

  Scenario: As a super user, test that i'm allowed to edit manual_documents
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/manual_documents/1/edit"
    Then the response status code should be 200
    And I should see an "#manual_document_parts_0_partNumber" element
    And I fill in "manual_document[parts][0][partNumber]" with "LOST_ODYSSEY"
    And press "submit"
    Then the response status code should be 200
    And I should see "LOST_ODYSSEY"
    And The module should be "PUBS"

  Scenario: As an anonymous user, test that i'm not allowed to see Recommended spare Manual Parts list view (RSPL)
    When I go to "/support/manuals/1/rspl"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see Recommended spare Manual Parts list view (RSPL)
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/manuals/1/rspl"
    Then the response status code should be 200
    And I should see an "a[href$='/support/manuals/1/rspl']" element
    And I should see an "a[href$='/support/manuals/1/show']" element
    And I should not see an "a[href$='/support/manuals/1/edit']" element
    And I should see "RSPL"
    And I should see "FILTER"
    And I should see "Parts list"
    And The module should be "PUBS"
    When I check "P"
    And press "FILTER"
    Then the response status code should be 200
    And I should see 2 "table.footable tbody tr" elements
    And I should see "LOST_ODYSSEY"
    When I check "O"
    And press "FILTER"
    Then the response status code should be 200
    And I should see 1 "table.footable tbody tr" elements
    And I should see "pn-000005"

  Scenario: As a basic user, test that i'm allowed to download a chapter 4 pdf file of a manual
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/manuals/1/chapter4"
    Then the response status code should be 200
    And The module should be "PUBS"
    And I should see response headers "content-type" with "application/pdf"

  Scenario: As a basic user, test that i'm allowed to download a Parts list pdf file of a manual
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/manuals/1/parts_list"
    Then the response status code should be 200
    And The module should be "PUBS"
    And I should see response headers "content-type" with "application/pdf"

  Scenario: As a basic user, test that i'm allowed to download a Manual Document pdf file
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/manual_documents/1/pdf"
    Then the response status code should be 404

  Scenario: As a basic user, test that i'm allowed to download a Manual Document pdf file
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/manual_documents/2/pdf"
    Then the response status code should be 200
    And The module should be "PUBS"
    And I should see response headers "content-type" with "application/pdf"

  Scenario: As a basic user, test that i'm allowed to download a Zip file of a manual
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/manuals/1/zip"
    Then the response status code should be 200
    And The module should be "PUBS"
    And I should see response headers "content-type" with "application/zip"

  Scenario: As a basic user, test that i'm allowed to see QR code to extranet of a manual
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/manuals/1/qrCode"
    Then the response status code should be 200