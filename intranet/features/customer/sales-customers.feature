Feature: Sales / Customer

  Scenario: As an anonymous user, test that i'm not allowed to see customers pages
    When I go to "/sales/customers"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/sales/customers/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see customers pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers"
    Then the response status code should be 200
    And The module should be "ECUST"
    And I should see "Last 10 Customers Created"
    And I should not see an "a[href$='/sales/customers/export']" element
    When I go to "/sales/customers/1/show"
    Then the response status code should be 200
    And I should see "Customer Details"
    And I should see "logs"

  Scenario: As a superuser, test that i'm allowed to see customers pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers"
    Then I should see 10 "table.footable tbody tr" elements
    And the "table.footable tbody tr:nth-child(2) td:nth-child(2)" element should contain a text
    And I should not see an "a[href$='/sales/customers/export']" element
    When I go to "/sales/customers/1/show"
    Then I should see "Customer Details"
    And I should see "Change Customer status to PENDING and open a new validation SEQ"
    And the ".association-table tr:nth-child(1) td:nth-child(2)" element should contain "1"
    And the ".association-table tr:nth-child(2) td:nth-child(2)" element should contain a text

  Scenario: As a superuser, test that i can add a customer
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/add"
    Then the response status code should be 200
    When I fill in the following:
      | customer_form[name]                 | MyTestCustomerNameNew   |
      | customer_form[address][street1]     | MyTestCustomerStreet    |
      | customer_form[address][postalCode]  | MyTestPostalCode        |
      | customer_form[address][town]        | MyTestCustomerTown      |
      | customer_form[address][city]        | MyTestCustomerCity      |
      | customer_form[address][state]       | MyTestCustomerState     |
      | customer_form[status]               | NOT APPROVED            |
    And I select "/countries/11" from "customer_form[country]"
    And I select "/sales/customer_types/1" from "customer_form[customerTypes][]"
    And I select "/people/32" from "customer_form[mainSalesRepresentative][asm]"
    And I select "/sub_divisions/1" from "customer_form[mainSalesRepresentative][subDivision]"
    And press "customer_form_submit"
    Then the response status code should be 200
    And I should be on "/sales/customers"

  Scenario: Test that a customer has been added
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers"
    Then the response status code should be 200
    And I should see "MyTestCustomerNameNew"

  Scenario: As a superuser, test that i can edit a customer
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/36/edit"
    Then the response status code should be 200
    When I fill in "customer_form[name]" with "CustomerNameEdited"
    And I select "/sales/customer_types/1" from "customer_form[customerTypes][]"
    And I select "/people/32" from "customer_form[mainSalesRepresentative][asm]"
    And I select "/sub_divisions/1" from "customer_form[mainSalesRepresentative][subDivision]"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/sales/customers/36/show"
    And I should see "CustomerNameEdited"

  Scenario: As a superuser, test that i can admin credit limits for customer
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/1/credit-limits"
    Then the response status code should be 200
    When I select "INTERNAL SPH" from "creditLimits[0][type]"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/sales/customers/1/show"
    And I should see "INTERNAL SPH"

  Scenario: As a basic user, test that i can see hierarchy of a customer
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/1/hierarchy"
    Then the response status code should be 200

  Scenario: As a basic user, test that I can filter customers
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers"
    Then the response status code should be 200
    And I should see "Last 10 Customers Created"
    When I go to "/sales/customers?page=1&order[name]=asc&itemsPerPage=20000&main_representative_exists=0"
    Then the response status code should be 200
    And I should see "Customers"
    And the ".table tr:nth-child(1) td:nth-child(5)" element should contain ""

  Scenario: As a basic user, test that I can see links
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/1/show"
    Then the response status code should be 200
    And I should see "CRT Linked"
    And I should see "Sales orders"
    And I should see "Invoices"
    And I should see "Packing slips"
    And I should see "Email"
    And I should see "Zip"
    And I should see an "a[href$='/sales/sales-forecasts?customer=/sales/customers/1&open=0']" element
    And I should not see an "button[name$='customer_watch_list_form[put_on_watchlist]']" element

  Scenario: As a basic user, test that I can see sales orders linked
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/1/sales_orders"
    Then the response status code should be 200

  Scenario: As a basic user, test that I can see invoices linked
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/1/invoices"
    Then the response status code should be 200

  Scenario: As a basic user, test that I can see packing slips linked
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/1/packing_slips"
    Then the response status code should be 200

  Scenario: As a basic user, test that I can see page to send email
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/1/email"
    Then the response status code should be 200

  Scenario: As a basic user, test that I can see page to zip
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/1/zip"
    Then the response status code should be 200

  Scenario: As a superuser, test that i can delete a customer
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/5/delete"
    Then the response status code should be 200
    And I should be on "/sales/customers"
    And I should see "The customer has been successfully deleted"

  Scenario: As a basic user, test that I can see files page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/1/files"
    Then the response status code should be 200
    And I should not see "Upload a file"

  Scenario: As a super user, test that I can see files page
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/1/files"
    Then the response status code should be 200
    And I should see "Upload a file"
    And I should see "File description"
    And I should see "Subdivision"
    And I should see "This file is a contract"
    And I attach the file "file.pdf" to "customer[file]"
    And I fill in "customer[description]" with "this is a description"
    And I select "/sub_divisions/3" from "customer[subDivision]"
    And press "submit"
    Then the response status code should be 200
    And I should see "The file has been successfully uploaded"

  Scenario: As a super user, uploading a file marked as a contract redirects to the contract creation page
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/1/files"
    And I attach the file "file.pdf" to "customer[file]"
    And I fill in "customer[description]" with "a contract file"
    And I select "/sub_divisions/3" from "customer[subDivision]"
    And I check "customer[isContract]"
    And press "submit"
    Then the response status code should be 200
    And I should be on the exact url "/legal/contracts/add"

  @javascript
  Scenario: As a super user, completing the contract created from an ECUST file links back to that file
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/1/files"
    And I attach the file "file.pdf" to "customer[file]"
    And I fill in "customer[description]" with "another contract file"
    And I select "/sub_divisions/3" from "customer[subDivision]"
    And I check "customer[isContract]"
    And press "submit"
    And I wait until I see "Add Contract"
    And I fill in "shortDescription" with "My ECUST Contract"
    Then I fill in dropdown "category" with "BANK"
    Then I fill in dropdown "subCategory" with "Permanent without ending"
    And I fill in "description" with "My Description"
    And I fill in "jurisdiction" with "My Jurisdiction"
    Then I fill in date picker "startDate" with "today"
    Then I fill in dropdown "divisions" with "By Zero"
    Then I fill in dropdown "regions" with "TLD by zero"
    Then I fill in dropdown "businessUnits" with "Ritchie Group"
    Then I fill in dropdown "premises" with "Andalouza"
    And I fill in "value" with "1"
    Then I fill in dropdown "currency" with "CAD"
    Then I fill in date picker "expirationDate" with "today"
    And I fill in "externalParty" with "External Party"
    Then I click on element ID "addInternalPartyButton"
    And I fill in "internalParty[0]" with "Internal Party 1"
    And press "Submit"
    And I wait until I see "Saved"
    When I go to "/sales/customers/1/files"
    Then I should see an "a[href*='/sales/customers/1/files/']:not([href$='/edit']):not([href*='/delete'])" element

  Scenario: As superuser, I can download all files from a customer as zip
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/1/files/download-all"
    Then the response status code should be 200

  Scenario: As a super user, test that I can access customer file edit form from datatable
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/1/files"
    Then the response status code should be 200
    And I should see an "a[href*='/sales/customers/1/files/'][href$='/edit']" element
    When I click on the 1st "a[href*='/sales/customers/1/files/'][href$='/edit']" element
    Then the response status code should be 200
    And I should see "File description"
    And I should see "Subdivision"

  Scenario: As a super user, test that I can edit a customer file
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/1/files"
    Then the response status code should be 200
    When I click on the 1st "a[href*='/sales/customers/1/files/'][href$='/edit']" element
    Then the response status code should be 200
    When I fill in "editFile[description]" with "Behat updated customer file description"
    And I fill in "editFile[subDivision]" with "/sub_divisions/3"
    And press "editFile[submit]"
    Then the response status code should be 200
    And I should be on "/sales/customers/1/files"
    And I should see "Behat updated customer file description"

  Scenario: As an evp user, test that I can pu a customer on the watch list
    Given I authenticate as "user-evp@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/34/show"
    Then the response status code should be 200
    And I should see an "button[name$='customer_watch_list_form[put_on_watchlist]']" element
    And I should not see an "button[name$='customer_watch_list_form[remove_from_watchlist]']" element
    When I fill in the following:
      | customer_watch_list_form[watchListReason] | Did not pay the bill |
    And press "Put on watch list"
    Then I should be on "/sales/customers/34/show"
    And I should not see an "button[name$='customer_watch_list_form[put_on_watchlist]']" element
    And I should see an "button[name$='customer_watch_list_form[remove_from_watchlist]']" element

  Scenario: As a basic user, I'm not allowed to see the watch list quick edit page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/watch-list"
    Then I should not be on "/sales/customers/watch-list"
    And I should see "You do not have permissions"

  Scenario: As a super user, I'm not allowed to see the watch list quick edit page
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/watch-list"
    Then the response status code should be 200

  Scenario: As superuser, test that I can open validation sequence
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/47/show"
    Then the response status code should be 200
    And I should see "NOT APPROVED"
    And I should see "Change Customer status to PENDING and open a new validation SEQ"
    When I go to "/sales/customers/47/validate"
    Then the response status code should be 200
    And I should be on "/sales/customers/47/show"
    And I should see "PENDING"

  Scenario: As a superuser, deactivate button must be available if customer is not "NOT ACTIVE"
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customers/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/sales/customers/1/deactivate']" element
    When I go to "/sales/customers/1/deactivate"
    Then the response status code should be 200
    And I should be on "/sales/customers/1/show"
    And I should not see an "a[href$='/sales/customers/1/deactivate']" element
