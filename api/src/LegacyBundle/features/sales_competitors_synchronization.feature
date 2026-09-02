Feature: Test competitors double write API

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create a competitor in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/competitors" with the body "tests/fixtures/json/sales/competitor/dummies/post.json"
    Then the response status code should be 201
    Then a new row has been inserted in the legacy table "cor"
    Then the column "name" from the "cor" legacy table has been inserted with a string containing "Compete Thor"
    And a new row has been inserted in the legacy table "mod_logs"

  Scenario: Update a competitor in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/competitors/2" with the body "tests/fixtures/json/sales/competitor/dummies/put.json"
    Then the response status code should be 200
    And the column "name" from the "cor" legacy table has been updated with string "pis t\'as tord &#33337;&#23614;"
    And the column "short_desc" from the "cor" legacy table has been updated with string "short &#33337;&#23614;"
    And the column "comments" from the "cor" legacy table has been updated with string "long &#33337;&#23614;"
    And the column "url" from the "cor" legacy table has been updated with string "http://www.you-wrong.com"
    And a new row has been inserted in the legacy table "mod_logs"
