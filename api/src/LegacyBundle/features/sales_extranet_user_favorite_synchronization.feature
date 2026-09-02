Feature: Test extranet users favorite double write API

  Scenario: Create an extranet user favorite in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/extranet_user_favorites" with the body "tests/fixtures/json/sales/extranet_user_favorite/dummies/post.json"
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "extranet_users_fav"
    And a new row has been inserted in the legacy table "mod_logs"

  Scenario: Update an extranet user favorite in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_user_favorites/11" with the body "tests/fixtures/json/sales/extranet_user_favorite/dummies/put.json"
    Then the response status code should be 200
    # /sales/extranet_users/250
    And the column "parent_id" from the "extranet_users_fav" legacy table has been updated with integer 7250
    And the column "fav_user" from the "extranet_users_fav" legacy table has been updated with string "FX0007"
    And the column "fav_pn" from the "extranet_users_fav" legacy table has been updated with string "987654321"
    And the column "fav_dsca" from the "extranet_users_fav" legacy table has been updated with string "Grumpy Meow"
    And a new row has been inserted in the legacy table "mod_logs"