Feature: Test Extranet users favorites

  Scenario: Request all extranet users favorites without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_favorites"
    Then the response status code should be 401

  Scenario: Request a single extranet users favorites without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_favorites/1"
    Then the response status code should be 401

  Scenario: Request all extranet users favorites
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_favorites"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user_favorite/schemas/extranet_user_favorites.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\ExtranetUserFavorite" is exposed on the API
    Then the filter "legacyId" should be available and its type should be "int"

  Scenario: Request a single extranet user favorite
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_favorites/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user_favorite/schemas/extranet_user_favorite.json"

  Scenario: basic users can't update an extranet user favorites
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_user_favorites/1" with the body "tests/fixtures/json/sales/extranet_user_favorite/dummies/put.json"
    Then the response status code should be 403

  Scenario: Superuser can update an extranet user favorite
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_user_favorites/1" with the body "tests/fixtures/json/sales/extranet_user_favorite/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user_favorite/schemas/extranet_user_favorite.json"
    And an update log should have been inserted on resource "/sales/extranet_user_favorites/1" with a changeset on the property "aeroUsername"

  Scenario: As an extranet user, I can only see my own favorites
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_favorites"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user_favorite/schemas/extranet_user_favorites.json"
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0].extranetUser.username" should be equal to "julien.lepers@tld.com"

  Scenario: As an extranet user, I can see my own favorites
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_favorites/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user_favorite/schemas/extranet_user_favorite.json"

  Scenario: As an extranet user, I can edit my own favorites
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_user_favorites/11" with the body "tests/fixtures/json/sales/extranet_user_favorite/dummies/put_julien_lepers.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user_favorite/schemas/extranet_user_favorite.json"

      Scenario: As an extranet user, I can create a favorite
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/extranet_user_favorites" with the body "tests/fixtures/json/sales/extranet_user_favorite/dummies/post_julien_lepers.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user_favorite/schemas/extranet_user_favorite.json"

  Scenario: As an extranet user, I can't edit someone else's favorites
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_user_favorites/1" with the body "tests/fixtures/json/sales/extranet_user_favorite/dummies/put_julien_lepers.json"
    Then the response status code should be 403

  Scenario: As an extranet user, I can't see a favorite for someone else
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_favorites/1"
    Then the response status code should be 403

  Scenario: As an extranet user, I can't create a favorite for someone else
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/extranet_user_favorites" with the body "tests/fixtures/json/sales/extranet_user_favorite/dummies/post.json"
    Then the response status code should be 403

