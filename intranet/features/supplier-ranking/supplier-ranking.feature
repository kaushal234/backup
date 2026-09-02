Feature: Supplier Rankings
  Scenario: As an anonymous user, test that i'm not allowed to see supplier rankings pages
    When I go to "/purchasing/supplier-rankings"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/purchasing/supplier-rankings/1"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/purchasing/supplier-rankings/files"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/purchasing/supplier-rankings/1/files"
    Then I should be on "/login"
    And I should see "Password"
    
  Scenario: As a superuser, test that i'm allowed to see supplier rankings pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/supplier-rankings"
    Then the response status code should be 200
    When I go to "/purchasing/supplier-rankings/10"
    Then the response status code should be 200
    And The module should be "SRM"
    Then the response status code should be 200
    When I go to "/purchasing/supplier-rankings/10/files"
    And The module should be "SRM"

  Scenario: As a superuser, test that i'm allowed to see supplier rankings pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/supplier-rankings"
    And I should see "Supplier Rankings"
    And I should see "Supplier number"
    And I should see "Supplier name"
    And I should see an "a[href$='/purchasing/supplier-rankings/10']" element
    When I go to "/purchasing/supplier-rankings/10"
    And I should see "TLD WIC (For Mig and First Go-Live)"
    And I should see "9MIG400"
    And I should see "227,861"
    And I should see "USD"
    And I should see "OCM"
    And I should see "Unrestricted"
    And press "Supplier rankings actions"
    And wait until I see "Edit"
    When I go to "/purchasing/supplier-rankings/files"
    And I should see "Supplier Rankings Files"
    And I should see "QLF"
    And I should see an "a[href$='/purchasing/supplier-rankings/10/files']" element
    When I go to "/purchasing/supplier-rankings/10/files"
    And I should see "Supplier Rankings Files"
    And I should see "TLD WIC (For Mig and First Go-Live)"

  Scenario: As a CPO, test that i'm allowed to see supplier rankings pages
    Given I authenticate as "user-cpo@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/supplier-rankings/1"
    Then the response status code should be 200
    And I should see an "a[href$='/purchasing/supplier-rankings/1/edit']" element
    When I go to "/purchasing/supplier-rankings/2"
    Then the response status code should be 200
    When I go to "/purchasing/supplier-rankings/3"
    Then the response status code should be 200

  Scenario: As a Buyer, test that I'm allowed to see supplier rankings pages
    Given I authenticate as "user-buyer@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/supplier-rankings"
    Then the response status code should be 200
    And I should see "Supplier Rankings"
    And I should see "Supplier number"
    And I should see "Supplier name"
    And I should see an "a[href$='/purchasing/supplier-rankings/9']" element
    When I go to "/purchasing/supplier-rankings/9"
    Then the response status code should be 200
    And The module should be "SRM"
    And I should see "ACE HARDWARE"
    And I should see "ACE0013"
    And I should see "Specialist"
    And I should see "Monitored"
    And I should see an "a[href$='/purchasing/supplier-rankings/9/edit']" element
    When I go to "/purchasing/supplier-rankings/files"
    Then the response status code should be 200
    And I should see "Supplier Rankings Files"
    And I should see "QLF"
    And I should see an "a[href$='/purchasing/supplier-rankings/9/files']" element
    When I go to "/purchasing/supplier-rankings/9/files"
    Then the response status code should be 200
    And I should see "Supplier Rankings Files"
    And The module should be "SRM"
    And I should see "ACE HARDWARE"

#  Scenario: As a basic user, test that I'm not allowed to see supplier ranking pages
#    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
#    When I go to "/purchasing/supplier-rankings"
#    Then I should be on "/account"
#    When I go to "/purchasing/supplier-rankings/2"
#    Then I should be on "/account"
#    When I go to "/purchasing/supplier-rankings/files"
#    Then I should be on "/account"
#    When I go to "/purchasing/supplier-rankings/2/files"
#    Then I should be on "/account"

  Scenario: As a superuser, test that i'm allowed to see level expertise pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/supplier-rankings/expertise-level"
    Then the response status code should be 200
    And I should see "Expertise level"
    And I should see "OCM"
    And I should see an "a[href$='/purchasing/supplier-rankings/expertise-level/add']" element
    And I should see an "a[href$='/purchasing/supplier-rankings/expertise-level/1/edit']" element
    And The module should be "SRM"

  Scenario: As a superuser, test that i'm allowed to add and edit level expertise
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/supplier-rankings/expertise-level/add"
    Then the response status code should be 200
    And I should see "Expertise level edition"
    And I fill in "form_expertise[name]" with "Expert ami-ami"
    And I fill in "form_expertise[description]" with "The null l'integrul must be watched"
    And I press "submit"
    Then I should be on "/purchasing/supplier-rankings/expertise-level"
    And I should see "Expert ami-ami"
    When I go to "/purchasing/supplier-rankings/expertise-level/4/edit"
    Then the response status code should be 200
    And I should see "Expertise level edition"
    And I fill in "Name" with "Expert Los angeles"
    And I fill in "Description" with "I think the tarte au pomme is the murderer"
    And I press "submit"
    Then I should be on "/purchasing/supplier-rankings/expertise-level"
    And I should see "Expert Los angeles"

  Scenario: As a superuser, test that i'm allowed to delete and transfer level expertise
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/supplier-rankings/expertise-level/4/transfer"
    Then the response status code should be 200
    And I should see "Expertise level transfer"
    And I select "/purchasing/supplier_ranking/expertise_levels/2" from "target"
    And I check "delete"
    And I press "submit"
    Then I should be on "/purchasing/supplier-rankings/expertise-level"
    And I should see "The expertise level have been successfully transferred"
    And I should not see "Expert Los angeles"

  Scenario: As a superuser, test that i'm allowed to see classification pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/supplier-rankings/classification"
    Then the response status code should be 200
    And I should see "Classification"
    And I should see "Locked"
    And I should see an "a[href$='/purchasing/supplier-rankings/classification/add']" element
    And I should see an "a[href$='/purchasing/supplier-rankings/classification/1/edit']" element
    And The module should be "SRM"

  Scenario: As a superuser, test that i'm allowed to add and edit classification
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/supplier-rankings/classification/add"
    Then the response status code should be 200
    And I should see "Classification edition"
    And I fill in "name" with "Classique"
    And I fill in "description" with "Baitovent"
    And I fill in "workflowLevel" with "45"
    And I fill in "color" with "rouche"
    And I select "/purchasing/supplier_ranking/classifications/4" from "targetClassifications[]"
    And I check "isSupplierApproved"
    And I press "submit"
    Then I should be on "/purchasing/supplier-rankings/classification"
    And I should see "Classique"
    When I go to "/purchasing/supplier-rankings/classification/6/edit"
    Then the response status code should be 200
    And I fill in "name" with "Classico"
    And I press "submit"
    Then I should be on "/purchasing/supplier-rankings/classification"
    And I should see "Classico"

  Scenario: As a superuser, test that i'm allowed to delete and transfer classification
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/supplier-rankings/classification/6/transfer"
    Then the response status code should be 200
    And I should see "Classification transfer"
    And I select "/purchasing/supplier_ranking/classifications/4" from "target"
    And I check "delete"
    And I press "submit"
    Then I should be on "/purchasing/supplier-rankings/classification"
    And I should not see "Classico"

  Scenario: As a superuser, test that i'm allowed to see file category pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/supplier-rankings/file_category"
    Then the response status code should be 200
    And I should see "File categories"
    And I should see an "a[href$='/purchasing/supplier-rankings/file_category/add']" element
    And I should see an "a[href$='/purchasing/supplier-rankings/file_category/1/edit']" element
    And The module should be "SRM"

  Scenario: As a superuser, test that i'm allowed to add and edit file category
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/supplier-rankings/file_category/add"
    Then the response status code should be 200
    And I should see "Category edition"
    And I fill in "form_category[name]" with "BBC"
    And I press "submit"
    Then I should be on "/purchasing/supplier-rankings/file_category"
    And I should see "BBC"
    When I go to "/purchasing/supplier-rankings/file_category/10/edit"
    Then the response status code should be 200
    And I should see "Category edition"
    And I fill in "Name" with "ABC"
    And I press "submit"
    Then I should be on "/purchasing/supplier-rankings/file_category"
    And I should see "ABC"

  Scenario: As a superuser, test that i'm allowed to delete and transfer file category
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/supplier-rankings/file_category/9/transfer"
    Then the response status code should be 200
    And I should see "Category transfer"
    And I select "/purchasing/supplier_ranking/file_categories/3" from "target"
    And I check "delete"
    And I press "submit"
    Then I should be on "/purchasing/supplier-rankings/file_category"
    And I should see "The category have been successfully transferred"

  Scenario: As a superuser, test that i'm allowed to see periodicity pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/supplier-rankings/periodicity"
    Then the response status code should be 200
    And I should see "Periodicities"
    And I should see an "a[href$='/purchasing/supplier-rankings/periodicity/add']" element
    And I should see an "a[href$='/purchasing/supplier-rankings/periodicity/expertiseLevel=1;classification=1/edit']" element
    And The module should be "SRM"

  Scenario: As a superuser, test that i'm allowed to add and edit periodicity
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/supplier-rankings/periodicity/add"
    Then the response status code should be 200
    And I should see "Periodicity edition"
    And I select "/purchasing/supplier_ranking/expertise_levels/3" from "form_periodicity[expertiseLevel]"
    And I select "/purchasing/supplier_ranking/classifications/4" from "form_periodicity[classification]"
    And I fill in "form_periodicity[months]" with "73"
    And I press "submit"
    Then I should be on "/purchasing/supplier-rankings/periodicity"
    And I should see "73"
    When I go to "/purchasing/supplier-rankings/periodicity/expertiseLevel=1;classification=1/edit"
    Then the response status code should be 200
    And I should see "Periodicity edition"
    And I fill in "form_periodicity[months]" with "75"
    And I press "submit"
    Then I should be on "/purchasing/supplier-rankings/periodicity"
    And I should see "75"

  Scenario: As a superuser, test that i'm allowed to delete periodicity
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/supplier-rankings/periodicity/expertiseLevel=1;classification=1/delete"
    Then the response status code should be 200
    Then I should be on "/purchasing/supplier-rankings/periodicity"
    And I should see "The periodicity have been successfully deleted"

  Scenario: As a superuser, test that i'm allowed to see supplier rankings pages with the by-code route
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/supplier-rankings/by-code/9ADHFRA"
    Then  I should be on "/purchasing/supplier-rankings/1"
    And the response status code should be 200

  Scenario: As a superuser, test that I get 404 when trying to see supplier ranking with the wrong code on by-code route
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/supplier-rankings/by-code/randdomcode"
    Then I should be on "purchasing/supplier-rankings/"
    And I should see "Supplier not found"

  @javascript
  Scenario:As a superuser, test that i'm allowed to filter supplier rankings with criteria
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/supplier-rankings/report/criteria"
    And I wait until I see "Supplier Rankings"
    And I select "/locations/29" from "supplier_ranking_report_criteria[location]"
    And I press "submit"
    And I wait until I see "ESG : 0"
    And I wait until I see "Anti Corruption : 0"
    And I wait until I see "Cybersecurity : 0"
    When I click on the 1st ".esgLink" element
    Then I should be on "/purchasing/supplier-rankings/"