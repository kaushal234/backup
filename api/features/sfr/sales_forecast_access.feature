Feature: Sales Forecasts visibility depends on the authenticated user's permissions

  Scenario: Sales Forecasts API requires authentication
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts"
    Then the response status code should be 401

  Scenario: A basic user can't access a SFR
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/1"
    Then the response status code should be 403

  Scenario: A basic user should not see any SFR
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 0

  Scenario: An ASM should see his SFR of the country he's ASM of on the specific my area route
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts_my_area"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecasts.json"

  Scenario: An ASM can only view the SFR he created and the SFR related to his customers and their children
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 5
    And the JSON node "hydra:member[0].@id" should be equal to "/sales/sales_forecasts/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/sales/sales_forecasts/9"
    And the JSON node "hydra:member[2].@id" should be equal to "/sales/sales_forecasts/10"
    And the JSON node "hydra:member[3].@id" should be equal to "/sales/sales_forecasts/11"
    And the JSON node "hydra:member[4].@id" should be equal to "/sales/sales_forecasts/12"
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecasts.json"
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/5"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/9"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"

  Scenario: A PSM or a COO can only view SFR of his factory
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 7
    And the JSON node "hydra:member[0].@id" should be equal to "/sales/sales_forecasts/2"
    And the JSON node "hydra:member[1].@id" should be equal to "/sales/sales_forecasts/4"
    And the JSON node "hydra:member[2].@id" should be equal to "/sales/sales_forecasts/9"
    And the JSON node "hydra:member[3].@id" should be equal to "/sales/sales_forecasts/11"
    And the JSON node "hydra:member[5].@id" should be equal to "/sales/sales_forecasts/14"
    And the JSON node "hydra:member[6].@id" should be equal to "/sales/sales_forecasts/15"
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecasts.json"
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/1"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"

  Scenario: A SSD or a FC or a SAM can only view SFR of his SSO or related to his customers and their children
    Given I authenticate as the intranet user "user-fc@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 6
    And the JSON node "hydra:member[0].@id" should be equal to "/sales/sales_forecasts/3"
    And the JSON node "hydra:member[1].@id" should be equal to "/sales/sales_forecasts/4"
    And the JSON node "hydra:member[2].@id" should be equal to "/sales/sales_forecasts/5"
    And the JSON node "hydra:member[3].@id" should be equal to "/sales/sales_forecasts/6"
    And the JSON node "hydra:member[4].@id" should be equal to "/sales/sales_forecasts/13"
    And the JSON node "hydra:member[5].@id" should be equal to "/sales/sales_forecasts/15"
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecasts.json"
    Given I authenticate as the intranet user "user-fc@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/1"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-fc@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/6"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"

  Scenario: A CEO or a CFO can only view the SFR of his locations (SSO or factory) and the SFR related to his customers and their children
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 5
    And the JSON node "hydra:member[0].@id" should be equal to "/sales/sales_forecasts/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/sales/sales_forecasts/2"
    And the JSON node "hydra:member[2].@id" should be equal to "/sales/sales_forecasts/5"
    And the JSON node "hydra:member[3].@id" should be equal to "/sales/sales_forecasts/6"
    And the JSON node "hydra:member[4].@id" should be equal to "/sales/sales_forecasts/13"
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecasts.json"
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/3"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/5"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"

  Scenario: The SFR MOO can see every SFR
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 5
    And the JSON node "hydra:member[0].@id" should be equal to "/sales/sales_forecasts/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/sales/sales_forecasts/2"
    And the JSON node "hydra:member[2].@id" should be equal to "/sales/sales_forecasts/5"
    And the JSON node "hydra:member[3].@id" should be equal to "/sales/sales_forecasts/6"
    And the JSON node "hydra:member[4].@id" should be equal to "/sales/sales_forecasts/13"
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecasts.json"
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/3"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/5"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"

  Scenario: A Chairman or a GCEO or a GTD or a CSD can see all TLD's SFR
    Given I authenticate as the intranet user "user-gceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 15
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecasts.json"
    Given I authenticate as the intranet user "user-gceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"

  Scenario: The MOO can see all TLD's SFR
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 15
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecasts.json"
    Given I authenticate as the intranet user "user-gceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"

  Scenario: A VPM can see all military TLD's SFR
    Given I authenticate as the intranet user "user-vpm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 5
    And the JSON node "hydra:member[0].@id" should be equal to "/sales/sales_forecasts/3"
    And the JSON node "hydra:member[1].@id" should be equal to "/sales/sales_forecasts/6"
    And the JSON node "hydra:member[2].@id" should be equal to "/sales/sales_forecasts/7"
    And the JSON node "hydra:member[3].@id" should be equal to "/sales/sales_forecasts/8"
    And the JSON node "hydra:member[4].@id" should be equal to "/sales/sales_forecasts/13"
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecasts.json"
    Given I authenticate as the intranet user "user-vpm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/1"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-vpm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/7"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"

  Scenario: A subscriber can see all SFR he's subscriber to
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0].@id" should be equal to "/sales/sales_forecasts/1"
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecasts.json"
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/2"
    Then the response status code should be 403

  Scenario: The csv export should keep on working if a linked customer has been soft deleted
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "text/csv"
    When I send a "GET" request to "/sales/sales_forecasts?properties[sso][]=name&properties[buyer][]=name"
    Then the response status code should be 200
    # /sales/sales_forecasts/15 should have a buyer null (as softdeleted)
    Then the "csv" cell "P3" should be equal to ""
    And a comment should have been inserted on resource "/people/12" with message 'downloaded a <b>csv</b> export of SFR with the following parameters: { "properties": { "sso": [ "name" ], "buyer": [ "name" ] } }' by "user-superuser@tld.fr"

  Scenario: User can get CSV reports from sales forecasts
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "text/csv"
    When I send a "GET" request to "/sales/sales_forecasts?normalization_groups_override[0]=sfr_export&context[datetime_format]=Y-m&properties[]=id&properties[]=createdAt&properties[]=lastCommentedAt&properties[]=status&properties[]=lastComment&properties[sso][]=name&properties[factory][]=name&properties[]=asm&properties[buyer][]=name&properties[endUser][]=name&properties[airport][]=code&properties[product][]=name&properties[]=quantity&properties[]=margin&properties[]=price&properties[sso][]=currency&properties[]=estimatedSaleDate&properties[]=customerSuccessPercentage&properties[]=successPercentage&properties[]=totalSuccessPercentage&properties[country][]=name&properties[tier][]=name&context[csv_headers_enabled]=1"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "text/csv; charset=utf-8"
    And the csv file headers are:
      | id | createdAt | lastCommentedAt | status | lastComment | sso.name | sso.currency | factory.name | asm | buyer.name | endUser.name | airport.code | product.name | quantity | margin | price | estimatedSaleDate | customerSuccessPercentage | successPercentage | totalSuccessPercentage | country.name | tier.name |
    And the csv file should have 6 lines

  Scenario: User can get Excel reports from sales forecasts
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/sales/sales_forecasts?columns=id,createdAt,lastCommentedAt,status,lastComment,sso,sso.currency,factory,asm,buyer,endUser,airport,product,quantity,margin,price,estimatedSaleDate,customerSuccessPercentage,successPercentage,totalSuccessPercentage,country,tier"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Created At | Last Commented At | Status | Last Comment | Sso | Sso Currency | Factory | Asm | Buyer | End User | Airport | Product | Quantity | Margin | Price | Estimated Sale Date | Customer Success Percentage | Success Percentage | Total Success Percentage | Country | Tier |
    And the xlsx file should have 6 lines
