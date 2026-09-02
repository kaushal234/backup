Feature: Sales / Forecast Closures

  Scenario: As an anonymous user, test that i'm not allowed to see FCR pages
    When I go to "/sales/forecast-closures"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/sales/forecast-closures/1/show"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/sales/forecast-closures/1/edit"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, I can view a FCR
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/forecast-closures/1/show"
    And the response status code should be 200
    And The module should be "FCR"
    And I should see "FCR#1"
    And I should see "customer_35"
    And I should see "customer_very_very_nested"
    And I should see "catalog_product_3"
    And I should see "location_factory"
    And I should see "location_sso"
    And I should see an "a[href$='/sales/sales-forecasts/2/show']" element
    And I should not see an "a[href$='/sales/forecast-closures/1/edit']" element

  Scenario: As an ASM I can view the edit button
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/forecast-closures/1/show"
    And the response status code should be 200
    And I should see an "a[href$='/sales/forecast-closures/1/edit']" element

  Scenario: As a basic user, I can't edit a FCR
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/forecast-closures/2/edit"
    Then I should be on "/sales/forecast-closures"
    And I should see "You are not allowed to edit this FCR"

  Scenario: As an ASM, I can edit a FCR I own
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/forecast-closures/3/edit"
    And the response status code should be 200
    And I fill in "reason" with "PERFORMANCE"
    And I fill in "competitor" with "/sales/competitors/1"
    And I fill in "orderedQuantity" with "15"
    And I fill in "price" with "123.50"
    And I fill in "currency" with "/finance/currencies/8"
    And I fill in "comment" with "Laure aime Ipsum"
    And I press "submit"
    Then I should be on "/sales/forecast-closures/3/show"
    And I should see an "a[href$='/sales/competitors/1/show']" element
    And I should see "The FCR has successfully been edited"

  Scenario: As a basic user, I can access to the FCR list
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/forecast-closures"
    Then the response status code should be 200
    And I should see "Forecast Closure Records"
#    And I fill in "competitor" with "/sales/competitors/2"
#    And I fill in "product" with "/sales/products/3"
#    And I fill in "productType" with "/sales/product_types/1"
#    And I fill in "status" with "ORDERED"
#    And I fill in "reason" with "PRICE"
#    And I fill in "asm" with "/people/31"
#    And I fill in "factory" with "/locations/29"
#    And I fill in "sso" with "/locations/23"
#    And I fill in "country" with "/countries/11"
#    And I press "FILTER"
#    Then the response status code should be 200

  Scenario: As an ASM, I can't delete a FCR
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/forecast-closures/4/delete"
    Then I should be on "/sales/forecast-closures/4/show"
    And I should see "You are not allowed to delete this FCR"

  Scenario: As a superuser, I can delete a FCR
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/forecast-closures/4/show"
    And I should see "Delete"
    When I go to "/sales/forecast-closures/4/delete"
    Then I should be on "/sales/forecast-closures"
    And I should see "FCR has successfully been deleted"

  Scenario: A superuser can upload a file
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/forecast-closures/1/show"
    Then the response status code should be 200
    And I attach the file "file.pdf" to "forecastClosure[file]"
    And I fill in "forecastClosure[description]" with "this is a file description"
    And press "forecastClosure_submit"
    Then the response status code should be 200
    And I should be on "/sales/forecast-closures/1/show"
    And I should see "The file has been successfully uploaded"

