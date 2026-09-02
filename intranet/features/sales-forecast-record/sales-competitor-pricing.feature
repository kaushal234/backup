Feature: Sales / Competitor Pricing

  Scenario: As an anonymous user, test that i'm not allowed to see CPR pages
    When I go to "/sales/competitor-pricings"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/sales/competitor-pricings/1/show"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/sales/competitor-pricings/1/edit"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/sales/competitor-pricings/add"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, I can view a CPR
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/competitor-pricings/1/show"
    And the response status code should be 200
    And The module should be "CPR"

  Scenario: CPR can be listed and filtered
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/competitor-pricings"
    Then the response status code should be 200
    And I should see "Competitor Pricing Records"
    And I select "/sales/competitors/2" from "competitor"
    And I select "/sales/products/1" from "product"
    And I fill in "status" with "ORDERED"
    And I press "FILTER"
    Then the response status code should be 200

  Scenario: As a basic user, I can't edit a CPR
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/competitor-pricings/1/edit"
    Then I should be on "/sales/competitor-pricings"
    And I should see "You are not allowed to edit this CPR"

  Scenario: As a basic user, I can't create a CPR
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/competitor-pricings/add"
    Then I should be on "/sales/competitor-pricings"
    And I should see "You are not allowed to create a CPR"

  Scenario: As an ASM, I can edit a CPR
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/competitor-pricings/2/edit"
    And the response status code should be 200
    And I fill in "quotationDate" with "04/19/1983"
    And I fill in "competitor" with "/sales/competitors/2"
    And I fill in "competitorModel" with "TOP"
    And I fill in "competitorOptions" with "FUR"
    And I fill in "quantity" with "66"
    And I fill in "price" with "12.53"
    And I fill in "currency" with "/finance/currencies/8"
    And I fill in "exchangeRate" with "1.51"
    And I fill in "markupPercentage" with "50"
    And I fill in "incoterm" with "/sales/incoterms/1"
    And I fill in "incotermsLocation" with "somewhere"
    And I press "submit"
    Then I should be on "/sales/competitor-pricings/2/show"
    And I should see "winner"
    And I should see "Competitor Pricing has been successfully updated"

  Scenario: As an ASM, I can create a CPR
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/competitor-pricings/add"
    And the response status code should be 200
    And I fill in "quotationDate" with "04/19/1983"
    And I fill in "competitor" with "/sales/competitors/2"
    And I fill in "competitorModel" with "TOP"
    And I fill in "competitorOptions" with "FUR"
    And I fill in "quantity" with "66"
    And I fill in "price" with "12.53"
    And I fill in "currency" with "/finance/currencies/8"
    And I fill in "exchangeRate" with "1.51"
    And I fill in "markupPercentage" with "50"
    And I fill in "incoterm" with "/sales/incoterms/1"
    And I fill in "incotermsLocation" with "somewhere"
    And I press "submit"
    Then I should be on "/sales/competitor-pricings/3/show"
    And I should see "winner"
    And I should see "Competitor Pricing has been successfully created"

  Scenario: A superuser can upload a file
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/competitor-pricings/1/show"
    Then the response status code should be 200
    And I attach the file "file.pdf" to "competitorPricing[file]"
    And I fill in "competitorPricing[description]" with "this is a file description"
    And press "competitorPricing_submit"
    Then the response status code should be 200
    And I should be on "/sales/competitor-pricings/1/show"
    And I should see "The file has been successfully uploaded"

  Scenario: As a basic user, I can't delete a CPR
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/competitor-pricings/2/delete"
    Then I should be on "/sales/competitor-pricings/2/show"
    And I should see "You are not allowed to delete this CPR"

  Scenario: As ASM, I can delete a CPR
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/competitor-pricings/2/show"
    And I should see "Delete"
    When I go to "/sales/competitor-pricings/2/delete"
    Then I should be on "/sales/competitor-pricings"
    And I should see "CPR has successfully been deleted"
