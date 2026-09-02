Feature: Sales / Sales Forecasts

  Scenario: As an anonymous user, test that i'm not allowed to see SFR pages
    When I go to "/sales/sales-forecasts/home"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/sales/sales-forecasts/dashboard"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/sales/sales-forecasts/dashboard-asm"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/sales/sales-forecasts"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/sales/sales-forecasts/1/show"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/sales/sales-forecasts/reports/per-week"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, I'm redirected to the main dashboard
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts/home"
    Then I should be on "/sales/sales-forecasts/dashboard"
    And The module should be "SFR"
    And I should see "SFR Count By SSO, Factory For Basic, User"
    And I should see "Hot Deals SFR Count By SSO, Factory"
    And I should see "SFR Recently Ordered Count By SSO, Factory"
    And I should see "SFR Recently Lost Count By SSO, Factory"
    And I should see "Deliquent SFR Count By SSO, Factory"

  Scenario: As an ASM, I'm redirected to the ASM dashboard
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts/home"
    Then I should be on "/sales/sales-forecasts/dashboard-asm"
    And I should see "Delinquent SFR Count By SSO, Factory For Martin, Anne Sophie"
    And I should see "SFR Count By Customer, Factory For Martin, Anne Sophie"
    And I should see "Hot Deals SFR Count By SSO, Factory For Martin, Anne Sophie"
    And I should see "SFR Recently Ordered Count By SSO, Factory For Martin, Anne Sophie"
    And I should see "SFR Recently Lost Count By SSO, Factory For Martin, Anne Sophie"
    And I should see "List Of Martin, Anne Sophie Customers Without Any Active SFR's"

  Scenario: As a basic user, I can't view a SFR
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts/1/show"
    Then I should be on "/sales/sales-forecasts/dashboard"
    And I should see "You are not allowed to access this SFR"

  Scenario: As an ASM, I can view a SFR I own
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts/1/show"
    And the response status code should be 200
    And I should see "BUDGET"
    And I should see "Non-delinquent"
    And I should see "NOT FOUND"
    And I should see "location_sso"
    And I should see "AIR DE RIEN"
    And I should see "customer_35"
    And I should see "Customer Purchase Percentage"
    And I should see "Alvest Success Percentage"
    And I should see "Linked Sales Forecasts"
    And I should see 3 "#linked-sfr table.footable tbody tr" elements
    And I should see "Pur%"
    And I should see "Alvest%"
    And I should see "Est Sales Date"
    And I should see an "a[href$='/sales/sales-forecasts/1/edit']" element
    And I should not see an "a[href$='/sales/sales-forecasts/1/delete']" element
    And I should see "Link Another SFR"
    And I should see "Add A Comment"
    And I should see an "a[href$='/sales/competitor-pricings?product=/sales/products/1']" element

  Scenario: As an ASM, I can add a comment
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts/1/show"
    And the response status code should be 200
    And I should see "Forecast Closure Records"
    And I fill in "comment[comment]" with "I love adding comments"
    And I press "comment_submit"
    Then I should be on "/sales/sales-forecasts/1/show"
    And I should see "I love adding comments"

  Scenario: As an ASM, I can unlink and link SFR
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts/9/show"
    And the response status code should be 200
    And I should see an "a[href$='/sales/sales-forecasts/11/unlink']" element
    When I go to "/sales/sales-forecasts/11/unlink"
    Then I should be on "/sales/sales-forecasts/9/show"
    And the response status code should be 200
    And I should see "SFR has successfully been unlinked"
    And I should see 2 "#linked-sfr table.footable tbody tr" elements

  Scenario: As an ASM, I can unlink and link SFR
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts/9/show"
    Then the response status code should be 200
    And I should not see an "a[href$='/sales/sales-forecasts/11/unlink']" element
    When I select "/sales/sales_forecasts/11" from "linked[salesForecasts][]"
    And I press "linked_submit"
    Then I should be on "/sales/sales-forecasts/9/show"
    And I should see 3 "#linked-sfr table.footable tbody tr" elements

  @javascript
  Scenario: As a basic user, I can't edit a SFR
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts"
    Then I go to "/sales/sales-forecasts/1/edit"
    Then I should be on "/account"

  @javascript
  Scenario: As an ASM, I can't access SSO dashboard
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts"
    And I should not see an "a[href$='/sales/sales-forecasts/dashboard-sso']" element
    And I go to "/sales/sales-forecasts/dashboard-sso"
    Then I should be on "/account"

  Scenario: As an EVP, I can access SSO dashboard
    Given I authenticate as "user-evp@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts"
    And I should see an "a[href$='/sales/sales-forecasts/dashboard-sso']" element
    And I go to "/sales/sales-forecasts/dashboard-sso"
    Then the response status code should be 200

  Scenario: Anyone can access weekly dashboard
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts"
    And I should see an "a[href$='/sales/sales-forecasts/reports/per-week']" element
    And I go to "/sales/sales-forecasts/reports/per-week"
    Then the response status code should be 200
    And I select "/finance/currencies/1" from "currency"
    And I select "90" from "saleDateDistance"
    And I select "/sales/products/31" from "product[]"
    And I select "/sales/product_types/2" from "productTypes[]"
    And I select "/locations/30" from "sso[]"
    And I select "/locations/29" from "factory[]"
    And I press "submit"
    And I should be on "/sales/sales-forecasts/reports/per-week"
    Then the response status code should be 200

  Scenario: Anyone can access Customer dashboard
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts"
    And I should see an "a[href$='/sales/sales-forecasts/reports/customers']" element
    And I go to "/sales/sales-forecasts/reports/customers"
    Then the response status code should be 200


  Scenario: As an ASM, I can't delete a SFR
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts/1/delete"
    Then I should be on "/sales/sales-forecasts/1/show"
    And I should see "You are not allowed to delete this SFR"

  Scenario: As MOO, I can delete a SFR
    Given I authenticate as "user-moo-sfr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts/3/show"
    And I should see "Delete"
    When I go to "/sales/sales-forecasts/3/delete"
    Then I should be on "/sales/sales-forecasts/dashboard"
    And I should see "SFR has successfully been deleted"

  Scenario: As an ASM, I can edit a SFR I own
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts/1/edit"
    And the response status code should be 200
    Then I fill in "sales_forecasts_form[quantity]" with "50"
    Then I fill in "sales_forecasts_form[comment]" with "Test comment"
    Then I check "sales_forecasts_form[notifyPackage]"
    And I press "submit"
    Then the response status code should be 200
    And I should be on "/sales/sales-forecasts/1/show"

  Scenario: As a basic user, I can access the basic listing page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts"
    Then the response status code should be 200

  Scenario: As a basic user, I can access the gantt listing page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts/gantt"
    Then the response status code should be 200

  Scenario: As an ASM, I can access the listing page of the SFR on the countries I am ASM of
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts/my-area"
    Then the response status code should be 200

  Scenario: As superuser, test that I can close SFR
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts/1/close"
    Then the response status code should be 200
    Then I select "Customer has placed order with competitor" from "status"
    And press "Close"
    Then the response status code should be 200
    And I should be on "sales/sales-forecasts/1/open-fcr/LOST"
    Then I select "LOST" from "forecastClosures[0][status]"
    And I fill in "forecastClosures[0][orderedQuantity]" with "5"
    And I select "Price" from "forecastClosures[0][reason]"
    And I fill in "forecastClosures[0][price]" with "150"
    And I select "CAD" from "forecastClosures[0][currency]"
    And I fill in "forecastClosures[0][comment]" with "Coucou"
    And press "submit"
    Then the response status code should be 200
    Then I should be on "/sales/sales-forecasts/1/add-cpr"
    And I should see an "a[href$='/sales/sales-forecasts/1/add-cpr?finish=1']" element
    And I select "FCR#5 - LOST" from "forecast_closure"
    And press "submit"
    Then the response status code should be 200
    Then I should be on the exact url "/sales/sales-forecasts/1/add-cpr?forecast_closure=5"
    And I fill in "quotationDate" with "02/21/2022"
    And I fill in "competitor" with "/sales/competitors/1"
    And I fill in "competitorModel" with "Modele test"
    And I fill in "quantity" with "5"
    And I fill in "exchangeRate" with "5"
    And I fill in "price" with "150"
    And I select "CAD" from "currency"
    And I select "EXW" from "incoterm"
    And press "submit_and_back"
    Then the response status code should be 200
    And I should be on "sales/sales-forecasts/1/show"

  Scenario: A superuser can upload a file
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts/2/show"
    Then the response status code should be 200
    And I attach the file "file.pdf" to "salesForecast[file]"
    And I fill in "salesForecast[description]" with "this is a file description"
    And press "salesForecast_submit"
    Then the response status code should be 200
    And I should be on "/sales/sales-forecasts/2/show"
    And I should see "The file has been successfully uploaded"

  Scenario: SFR can be transferred
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts/transfer"
    Then the response status code should be 200
    Then I select "/locations/23" from "transfer[sso]"
    Then I select "/people/32" from "transfer[asmSource]"
    Then I select "/people/31" from "transfer[asmTarget]"
    Then I select "/countries/1" from "transfer[country]"
    Then I select "/sales/customers/1" from "transfer[buyer]"
    Then I select "/sales/customers/35" from "transfer[endUser]"
    And press "transfer_submit"
    Then the response status code should be 200
    And I should be on the exact url "/sales/sales-forecasts?search=1&sso=/locations/23&country=/countries/1&asm=/people/31&buyer=/sales/customers/1&endUser=/sales/customers/35"
    And I should see "SFR have successfully been transferred"

  Scenario: SFR can be exported as csv files
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts/export"
    And I fill in "sfr_export[createdAt]" with "04/19/1983, 12:01 AM"
    When I press "sfr_export_submit"
    Then the response status code should be 200
    Then I should see response headers "content-type" with "text/csv; charset=utf-8"
    Then I should see response headers "content-disposition" with 'inline; filename=sales_forecasts.csv'

  @javascript
  Scenario: SFR can be summarized
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/sales-forecasts/1/show#sfrLogs"
    Then I click on element ID "generate_summary"
    And I wait for the AJAX results to appear in "#confirm_summary"
    And I wait 5 seconds
    And I should see an "#confirm_summary" element

#  can't make it work, tried everything I could
#  Scenario: SFR can be exported as xlsx files
#    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
#    When I go to "/sales/sales-forecasts/export"
#    And I fill in "sfr_export[createdAt]" with "1983-04-19"
#    And I fill in "sfr_export[format]" with "xlsx"
#    When I press "sfr_export_submit"
#    Then the response status code should be 200
#    Then I should see response headers "content-type" with "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
#    Then I should see response headers "content-disposition" with 'inline; filename=sales_forecasts.xlsx'
