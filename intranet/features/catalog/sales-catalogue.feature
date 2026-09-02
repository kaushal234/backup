@sales_catalogue
Feature: Sales / Catalogue

  Scenario: As an anonymous user, test that i'm not allowed to see catalogue pages
    When I go to "/sales/catalogue"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/sales/catalogue/types/1/show"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/sales/catalogue/families/1/show"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/sales/catalogue/products/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see catalogue pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/types"
    Then the response status code should be 200
    And The module should be "CAT"
    And I should see "Type English"
    And I should see "Type Ex"
    When I go to "/sales/catalogue/types/1/show"
    Then the response status code should be 200
    And I should see "Type English"
    And I should see "logs"
    When I go to "/sales/catalogue/families/1/show"
    Then the response status code should be 200
    And I should see "Information"
    And I should see "Products"
    And I should see "logs"
    When I go to "/sales/catalogue/products/show"
    Then the response status code should be 200
    And I should see "Products"

  Scenario: As a superuser, test that i can add a product type
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/types/add"
    Then the response status code should be 200
    When I fill in the following:
      | product_type_form[englishName]     | Tractopelle    |
      | product_type_form[frenchName]      | Tractor    |
      | product_type_form[spanishName]     | Tractopelo |
      | product_type_form[portugueseName]  | Tractoupel  |
      | product_type_form[chineseName]     | 反铲  |
      | product_type_form[japaneseName]    | バックホー  |
      | product_type_form[russianName]     | Экскаваторы-погрузчики  |
      | product_type_form[germanName]      | TRAKTOPELHE  |
    And I check "product_type_form[publicForTLD]"
    And press "Submit"
    Then the response status code should be 200
    And I should be on "/sales/catalogue/types"
    And I should see "Tractopelle"

  Scenario: As a superuser, test that i can edit a product type
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/types/4/edit"
    Then the response status code should be 200
    When I fill in "product_type_form[portugueseName]" with "Houloucouptere"
    When I fill in "product_type_form[englishName]" with "K2000"
    And press "Submit"
    Then the response status code should be 200
    And I should be on "/sales/catalogue/types/4"
    And I should see "K2000"
    And I should see "Houloucouptere"

  @javascript
  Scenario: As a superuser, test that i access to product family edit page
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/families/7/edit"
    And I wait until I see "Edit Family"
    And I wait until I see "Manufacturing Factories"
    And I wait until I see "Equipment Type Name"
    And I wait until I see "Equipment Type"

  @javascript
  Scenario: As a superuser, test that i can access to product family add page
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/types/3/add_family"
    And I wait until I see "Add Family"
    And I wait until I see "Manufacturing Factories"
    And I wait until I see "Equipment Type Name"
    And I wait until I see "Equipment Type"

  Scenario: As a superuser, test that i can access to add a product page
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/families/26/add_product"
    Then the response status code should be 200
    When I fill in the following:
      | product_form[name]  | TEST-XX-100 |
    And I select "/locations/29" from "product_form[erpLocation]"
    And I select "/finance/finance_families/4" from "product_form[financeFamily]"
    And I select "/manufacturing/manufacturing_families/2" from "product_form[manufacturingFamily]"
    And press "Submit"
    Then the response status code should be 200
    And I should be on "/sales/catalogue/families/26"
    And I should see "TEST-XX-100"
    And I should see "Not Hidden"

  Scenario: As a superuser, test that i can edit a product
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/products/4/edit"
    Then the response status code should be 200
    When I check "product_form[hidden]"
    And I select "/finance/finance_families/4" from "product_form[financeFamily]"
    And I select "/manufacturing/manufacturing_families/2" from "product_form[manufacturingFamily]"
    And press "Submit"
    Then the response status code should be 200
    And I should be on "/sales/catalogue/products/4/show"
    And I should see "Hidden"

  Scenario: As a superuser, test that I can download csv files
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/types/1"
    Then the response status code should be 200
    And I should see "Download"
    When I go to "/sales/catalogue/types/1/download-er-by-product-user"
    Then the response status code should be 200

  Scenario: As a superuser, test that I can download csv files
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/types/1/download-er-by-product-buyer"
    Then the response status code should be 200

  Scenario: As a superuser, test that I can download csv files
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/types/1/download-er-count-customer-buyer"
    Then the response status code should be 200

  Scenario: As a superuser, test that I can download csv files
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/types/1/download-er-count-customer-user"
    Then the response status code should be 200

  Scenario: As a superuser, test that I can download csv files
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/types/1/download-er-by-customer-user"
    Then the response status code should be 200

  Scenario: As a superuser, test that I can download csv files
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/types/1/download-er-by-customer-buyer"
    Then the response status code should be 200

  Scenario: As a superuser, test that I can download csv files
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/products/download-all-products-show"
    Then the response status code should be 200

  Scenario: As a superuser, test that I can download csv files
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/products/download-all-products-show-all"
    Then the response status code should be 200

  Scenario: As a superuser, test that I can delete product family
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/families/7/show"
    Then the response status code should be 200
    And I should see an "a[href$='/sales/catalogue/families/7/delete']" element
    When I go to "/sales/catalogue/families/7/delete"
    Then the response status code should be 200
    And I should be on "/sales/catalogue/types/2"

  Scenario: As a superuser, test that I can delete product
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/families/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/sales/catalogue/products/31/delete']" element
    When I go to "/sales/catalogue/products/31/delete"
    Then the response status code should be 200
    And I should be on "/sales/catalogue/families/1"

  Scenario: As a superuser, test that I can see hidden products
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/families/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/sales/catalogue/families/1/show-all']" element

  Scenario: As a PSE, I can add a family DMS
    Given I authenticate as "user-pse@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/families/1/show"
    Then the response status code should be 200
    And I should see "Add Family DMS"

  Scenario: As a PSE, I can add a product DMS
    Given I authenticate as "user-pse@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/products/1/show"
    Then the response status code should be 200
    And I should see "Add Model DMS"
