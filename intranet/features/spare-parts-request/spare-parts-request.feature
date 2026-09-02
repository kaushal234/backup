Feature: Spare Parts Request

  Scenario: As an anonymous user, test that i'm not allowed to see spare parts request pages
    When I go to "/parts/spare-parts-requests"
    Then I should be on "/login"
    When I go to "/parts/spare-parts-requests/1/show"
    Then I should be on "/login"
    When I go to "/parts/spare-parts-requests/1/show"
    Then I should be on "/login"
    When I go to "/parts/spare-parts-requests/toc/1/add-parts"
    Then I should be on "/login"

  Scenario: As a basic user, test that i'm allowed to see spare parts request home page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/spare-parts-requests"
    Then the response status code should be 200
    And The module should be "SPR"

  Scenario: As a basic user, test that i'm allowed to see spare parts request details page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/spare-parts-requests/1/show"
    Then the response status code should be 200
    And The module should be "SPR"
    And I should see "PENDING"
    And I should not see an "a[href$='/parts/spare-parts-requests/1/status/SHIPPED']" element
    And I should see an "a[href$='/service/technician-on-calls/15/show']" element

  @javascript
  Scenario: As a basic user, test that i'm allowed to see spare parts request search page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/spare-parts-requests/search"
    And I wait until I see "Spare Parts Requests"
    And I click on the 1st "[data-bs-target='#filter_spare_parts_request__collapse']" element
    And I select "/locations/27" from "filter_spare_parts_request[sph][value]"
    And I select "/locations/29" from "filter_spare_parts_request[factory][value]"
    And I select "PENDING" from "filter_spare_parts_request[status][value]"
    And I select "Payable Services" from "filter_spare_parts_request[type][value][]"
    And I click on the 1st "button[form='filter_spare_parts_request']" element
    And I wait until I see "Showing 1 - 3 of 3"
    Then I should be on the exact url "/parts/spare-parts-requests/search?filter_spare_parts_request%5Bsph%5D%5Bvalue%5D=%2Flocations%2F27&filter_spare_parts_request%5Bfactory%5D%5Bvalue%5D=%2Flocations%2F29&filter_spare_parts_request%5Bstatus%5D%5Bvalue%5D=PENDING&filter_spare_parts_request%5Btype%5D%5Bvalue%5D%5B%5D=Payable+Services&filter_spare_parts_request%5BshippingDate%5D%5Bvalue%5D%5Bfrom%5D=&filter_spare_parts_request%5BshippingDate%5D%5Bvalue%5D%5Bto%5D=&filter_spare_parts_request%5BpartNumber%5D%5Bvalue%5D=&filter_spare_parts_request%5BsalesOrder%5D%5Bvalue%5D=&page_spare_parts_request=1&limit_spare_parts_request=25&filter_spare_parts_request%5Btype%5D%5Bvalue%5D%5B0%5D=Payable+Services&sort_spare_parts_request%5Bid%5D=desc"
    And I should see an "a[href$='/parts/spare-parts-requests/1/show']" element

  Scenario: As a basic user, I can't add parts on a TOC
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    And I go to "/parts/spare-parts-requests"
    When I go to "/parts/spare-parts-requests/toc/15/add-parts"
    Then I should be on "/parts/spare-parts-requests"
    And I should see "You do not have permissions"

  @javascript
  Scenario: As a service user, I can add parts on a TOC
    Given I authenticate as "user-service@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/spare-parts-requests/toc/15/add-parts?activity=Troubleshooting&equipmentRecord=/equipment_records/1&type=Warranty"
    And I should be on the exact url "/parts/spare-parts-requests/toc/15/add-parts?activity=Troubleshooting&equipmentRecord=/equipment_records/1&type=Warranty"
    And I should see "SPR"

  Scenario: As a basic user, I can't edit an SPR
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    And I go to "/parts/spare-parts-requests"
    When I go to "/parts/spare-parts-requests/1/edit"
    Then I should be on "/parts/spare-parts-requests"
    And I should see "You do not have permissions"

  Scenario: As a parts user, I can edit an SPR
    Given I authenticate as "user-parts@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/spare-parts-requests/1/edit"
    Then the response status code should be 200
    And The module should be "SPR"
    And I fill in "spare_parts_request[salesOrder]" with "PV0000011"
    And press "submit"
    And I should be on "/parts/spare-parts-requests/1/show"
    And I should see "OPEN"

  Scenario: As a basic user, I can't edit the parts of an SPR
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/spare-parts-requests/1/show?tab=parts"
    Then the response status code should be 200
    Then I should see "Willy Waller 2006"
    And I should not see an "input[name='parts_collection[submit]']" element
    And I should not see an "a[href$='/parts/spare-parts-requests/1/parts/1/delete']" element

  Scenario: As a parts user, I can edit the parts of an SPR
    Given I authenticate as "user-parts@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/spare-parts-requests/1/show"
    Then the response status code should be 200
    And I fill in "parts_collection[parts][4][description]" with "Willy Waller 3000"
    And I press "parts_collection[submit]"
    Then I should be on the exact url "/parts/spare-parts-requests/1/show?tab=parts"
    And I should see "The SPR has been successfully saved"

  Scenario: As a basic user, I can't delete the parts of an SPR
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    And I go to "/parts/spare-parts-requests"
    When I go to "/parts/spare-parts-requests/1/parts/3/delete"
    Then I should be on "/parts/spare-parts-requests"
    And I should see "You do not have permissions"

  Scenario: As a parts user, I can delete a parts of an SPR
    Given I authenticate as "user-parts@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/spare-parts-requests/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/parts/spare-parts-requests/1/parts/4/delete']" element
    And I should see an "a[href$='/parts/spare-parts-requests/1/parts/6/delete']" element
    When I go to "/parts/spare-parts-requests/1/parts/6/delete"
    Then I should be on "/parts/spare-parts-requests/1/show?tab=parts"
    And I should see "The part has been successfully deleted"

  Scenario: As a basic user, I can't restore a part of an SPR
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    And I go to "/parts/spare-parts-requests"
    When I go to "/parts/spare-parts-requests/1/parts/6/restore"
    Then I should be on "/parts/spare-parts-requests"
    And I should see "You do not have permissions"

  Scenario: As a parts user, I can restore a parts of an SPR
    Given I authenticate as "user-parts@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/spare-parts-requests/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/parts/spare-parts-requests/1/parts/6/restore']" element
    When I go to "/parts/spare-parts-requests/1/parts/6/restore"
    Then I should be on the exact url "/parts/spare-parts-requests/1/show?tab=parts"
    And I should see "The part has been successfully restored"

  Scenario: As a parts user, I can combine SPRs
    Given I authenticate as "user-parts@tld.fr" with "P@ssw0rd15chars"
    Then I go to "/"
    And I should not see "Combine with other pending or open SPR from the same TOC or SB"
    When I go to "/parts/spare-parts-requests/4/show"
    Then the response status code should be 200
    And I should see "Combine with other pending or open SPR from the same TOC or SB"
    And I check "spare_parts_request_combine_sparePartsRequests_0"
    And I press "spare_parts_request_combine[submit]"
    Then I should be on the exact url "/parts/spare-parts-requests/4/show"
    And I should see "The SPRs were successfully combined"

  Scenario: As a parts user, I can edit the status of an SPR
    Given I authenticate as "user-parts@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/spare-parts-requests/1/show"
    And I fill in "spare_parts_request_status[comment]" with "just for test"
    And I press "spare_parts_request_status[submit]"
    Then the response status code should be 200