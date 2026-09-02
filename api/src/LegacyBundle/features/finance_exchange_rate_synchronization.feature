Feature: Test exchange rate double write API

  Scenario: Create an exchange rate
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/exchange_rates" with the body "tests/fixtures/json/finance_exchange_rate/dummies/post.json"
    Then the response status code should be 201
    And the column "dt" from the "erp_forex2" legacy table has been inserted with a string containing today's date
    And the column "nam_year" from the "erp_forex2" legacy table has been inserted with integer 2019
    And the column "nam_month" from the "erp_forex2" legacy table has been inserted with integer 2
    And the column "nam_cur" from the "erp_forex2" legacy table has been inserted with string "EUR"
    And the column "typ" from the "erp_forex2" legacy table has been inserted with string "END"
    And the column "rate" from the "erp_forex2" legacy table has been inserted with string "2.03"
    And a new row has been inserted in the legacy table "erp_forex2" 
  
  Scenario: Update an exchange rate
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/exchange_rates/1" with the body "tests/fixtures/json/finance_exchange_rate/dummies/put.json"
    Then the response status code should be 200
    And the column dt from the erp_forex2 legacy table has not been updated
    And the column nam_year from the erp_forex2 legacy table has been updated with integer 2050
    And the column nam_month from the erp_forex2 legacy table has been updated with integer 2
    And the column nam_cur from the erp_forex2 legacy table has been updated with string CNY
    And the column typ from the erp_forex2 legacy table has been updated with string AVG
    And the column rate from the erp_forex2 legacy table has been updated with string "12.05"

  Scenario: Delete an exchange rate
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/exchange_rates/2"    
    Then the response status code should be 204
    Then a row has been deleted in the legacy table "erp_forex2"
        
