Feature: Quality / CRAB

  Scenario: As an anonymous user, test that i'm not allowed to see crab pages
    When I go to "/quality/crabs"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/quality/crabs/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see crab pages detail
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/crabs"
    Then the response status code should be 200
    And The module should be "CRAB"
    And I should see "CRABs"
    And I should see an "a[href$='/quality/crabs/add']" element
    And I should see an "a[href$='/quality/crabs/reports']" element
    When I go to "/quality/crabs/1/show"
    Then the response status code should be 200
    And I should see "General"
    And I should see "Initiated"
    And I should see "Files"
    And I should see "Links"
    And I should see "Tasks"
    And I should see "Logs"

  Scenario: As a basic user, test that I'm allowed to see crab dashboard page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/crabs/dashboard"
    Then the response status code should be 200
    And I should see "CRAB Count by factory"

  Scenario: As a superuser, test that I can see files page
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/crabs/1/show"
    Then the response status code should be 200
    And I should see "Files"
    And I should see "Upload a file"
    And I should see "File description"
    And I should see "Public"
    And I attach the file "file.pdf" to "form_files[file]"
    And I fill in "form_files[description]" with "this is an updated description"
    And press "form_files[submit]"
    Then the response status code should be 200
    And I should be on "/quality/crabs/1/show"
    And I should see "File has been successfully added."
    And I should see "Change Visibility"
    And I should see 1 "#files table.footable tbody tr" elements

  Scenario: A basic user can upload a file
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/crabs/1/show"
    Then the response status code should be 200
    And I attach the file "file.pdf" to "form_files[file]"
    And I fill in "form_files[description]" with "this is a file description"
    And press "form_files[submit]"
    Then the response status code should be 200
    And I should be on "/quality/crabs/1/show"
    And I should see "this is a file description"

  Scenario: As a superuser, test i can ask for a derogation for a crab
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/crabs/3/show"
    Then the response status code should be 200
    And I should see an "a[href$='/quality/crabs/3/derogations/add']" element
    When I go to "/quality/crabs/3/derogations/add"
    Then the response status code should be 200
    And I fill in "derogation[shortDescription]" with "just for short description"
    And I fill in "derogation[description]" with "just for description"
    And I press "derogation_submit"
    Then the response status code should be 200
    And I should be on "/quality/crabs/3/show"
    And I should see "Derogation has been created successfully."

  Scenario: As a superuser, test I can see an existing derogation in a crab and edit it
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/crabs/3/show"
    Then the response status code should be 200
    And I should see an "a[href$='/quality/derogations/24/show']" element
    When I go to "/quality/derogations/24/show"
    Then the response status code should be 200
    And I should see "Decision comment"
    And I fill in "decision_derogation[comment]" with "this is a derogation description"
    And press "decision_derogation[denied]"
    And I should be on "/quality/derogations/24/show"
    And I should see "Status of derogation has been updated successfully"
    And I should see "REOPEN"
    And I go to "/quality/derogations/24/OPEN"
    And I should be on "/quality/derogations/24/show"
    And I should see "Status of derogation has been updated successfully"
    And I should see "Decision comment"
    And I fill in "decision_derogation[comment]" with "this is a derogation description"
    And press "decision_derogation[accepted]"
    And I should be on "/quality/derogations/24/show"
    And I should see "Status of derogation has been updated successfully"

  Scenario: As a superuser, test I can link a derogation with my crab
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/crabs/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/quality/crabs/1/link-derogation']" element
    When I go to "quality/crabs/1/link-derogation"
    Then the response status code should be 200
    And I select "/quality/derogations/24" from "derogation_link[derogation]"
    And I press "derogation_link_submit"
    And I should be on "/quality/crabs/1/show"
    And I should see "CLOSED"

  Scenario: As a superuser, test I can duplicate a crab
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/crabs/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/quality/crabs/1/duplicate']" element
    When I go to "quality/crabs/1/duplicate"
    Then the response status code should be 200
    And I select "/equipment_records/15" from "duplicate[equipmentRecords][]"
    And I press "submit"
    And I should be on "/quality/crabs/1/show"

  Scenario: As a basic user I should see reports page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/crabs/reports"
    Then the response status code should be 200
    And I select "location_factory" from "Factory"
    And I select "/sales/products/3" from "model[]"
    And I select "/sales/product_families/5" from "family[]"
    And I press "FILTER"
    And I should be on "/quality/crabs/reports?factory=%2Flocations%2F29&createdAfter=&createdBefore=&model[]=%2Fsales%2Fproducts%2F3&family[]=%2Fsales%2Fproduct_families%2F5"

  Scenario: As a basic user CRABs can be filtered
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/crabs?filter_crab[department][value]=Paint"
    Then the response status code should be 200

  Scenario: As a basic user, test that i can add a crab
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/crabs/add"
    And I select "/equipment_records/12" from "crab_add[equipmentRecord]"
    And I select "Paint" from "crab_add[department]"
    And I select "/quality/crab_codes/3" from "crab_add[code]"
    And I select "Assy" from "crab_add[category]"
    And I select "/quality/non_conformities/1" from "crab_add[nonConformity]"
    And I fill in "crab_add[description]" with "Guillaume et Florian aiment les cheveux"
    And press "crab_add[submit]"
    Then the response status code should be 200
    And I should see "Assy"
    And I should be on "/quality/crabs/5/show"

  Scenario: As a basic user, test that i can't add a crab from sol if code: 5 - FAQ  (First Article Qualification) and no part number
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/crabs/add_from_order_line"
    Then I fill in "crab_add[orderLine]" with "18828"
    And I select "Paint" from "crab_add[department]"
    And I select "/quality/crab_codes/4" from "crab_add[code]"
    And I select "Assy" from "crab_add[category]"
    And I select "/quality/non_conformities/1" from "crab_add[nonConformity]"
    And I fill in "crab_add[description]" with "Guy et Georges sont sur un bateau"
    And press "crab_add[submit]"
    Then the response status code should be 200
    And I should see "Part number must be filled for code: 5 - FAQ (First Article Qualification)."
    And I should be on "/quality/crabs/add_from_order_line"

  Scenario: As a basic user, test that i can add a crab from sol
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/crabs/add_from_order_line"
    Then I fill in "crab_add[orderLine]" with "18828"
    And I select "Paint" from "crab_add[department]"
    And I select "/quality/crab_codes/3" from "crab_add[code]"
    And I select "Assy" from "crab_add[category]"
    And I select "/quality/non_conformities/1" from "crab_add[nonConformity]"
    And I fill in "crab_add[description]" with "Guy et Georges sont sur un bateau"
    And press "crab_add[submit]"
    Then the response status code should be 200
    And I should see "Crabs have been successfully created."
    And I should be on "/quality/crabs"

  Scenario: As a superuser, test that i can edit a crab
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/crabs/4/edit"
    And I select "/equipment_records/11" from "crab_edit[equipmentRecord]"
    And I select "Purchasing" from "crab_edit[department]"
    And I select "/quality/crab_codes/3" from "crab_edit[code]"
    And I select "Test" from "crab_edit[category]"
    And I select "/quality/non_conformities/2" from "crab_edit[nonConformity]"
    And I fill in "crab_edit[description]" with "Finalement, ils préfèrent les chevaux"
    And I select "/quality/first_article_qualifications/2" from "crab_edit[firstArticleQualification]"
    And press "crab_edit[submit]"
    Then the response status code should be 200
    And I should see "Crab has been successfully edited."
    And I should be on "/quality/crabs/4/show"