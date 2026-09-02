  Feature: Test Market intelligence Subscriptions

    Scenario: Request all market intelligence subscriptions without being authenticated
      Given I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "sales/market_intelligence_subscriptions"
      Then the response status code should be 401

    Scenario: Request a single market intelligence subscription without being authenticated should not be permitted
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "/sales/market_intelligence_subscriptions/1"
      Then the response status code should be 401

    Scenario: Market intelligence subscriptions should not be accessible to XU
      Given I authenticate as the extranet user "julien.lepers@tld.com"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "/sales/market_intelligence_subscriptions"
      Then the response status code should be 403

    Scenario: Request all market intelligence subscriptions
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "sales/market_intelligence_subscriptions"
      Then the response status code should be 200
      And the JSON should be valid according to the schema "tests/fixtures/json/sales/market_intelligence_subscription/schemas/market_intelligence_subscriptions.json"

    Scenario: As a superuser I can only see my market intelligence subscriptions
      Given I authenticate as the intranet user "user-superuser@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "sales/market_intelligence_subscriptions"
      Then the response status code should be 200
      And the JSON should be valid according to the schema "tests/fixtures/json/sales/market_intelligence_subscription/schemas/market_intelligence_subscriptions.json"
      And the JSON node "hydra:totalItems" should be equal to 2

    Scenario: As a basic user, I can create a market intelligence subscription only for myself
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "POST" request to "/sales/market_intelligence_subscriptions" with the body "tests/fixtures/json/sales/market_intelligence_subscription/dummies/post.json"
      Then the response status code should be 201
      And the JSON should be valid according to the schema "tests/fixtures/json/sales/market_intelligence_subscription/schemas/market_intelligence_subscription.json"
      And the JSON node "subscriber.@id" should be equal to the string "/people/11"
      And the JSON node "customer.@id" should be equal to the string "/sales/customers/4"
      And the JSON node "productType.@id" should be equal to the string "/sales/product_types/1"
      And the JSON node "competitor.@id" should be equal to the string "/sales/competitors/1"
      And the JSON node "type.name" should be equal to the string "Pricing info"

    Scenario: As a superuser, I can create a market intelligence subscription only for myself
      Given I authenticate as the intranet user "user-superuser@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "POST" request to "/sales/market_intelligence_subscriptions" with body:
      """
        {
          "subscriber": "/people/4",
          "customer": "/sales/customers/2",
          "productType": "/sales/product_types/3",
          "type": "/sales/market_intelligence_types/1"
        }
      """
      Then the response status code should be 201
      And the JSON should be valid according to the schema "tests/fixtures/json/sales/market_intelligence_subscription/schemas/market_intelligence_subscription.json"
      And the JSON node "subscriber.@id" should be equal to the string "/people/12"
      And the JSON node "customer.@id" should be equal to the string "/sales/customers/2"
      And the JSON node "productType.@id" should be equal to the string "/sales/product_types/3"
      And the JSON node "type.name" should be equal to the string "Pricing info"

    Scenario: As a basic user, I can create an empty market intelligence subscription (to subscribe to every MIM notifications)
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "POST" request to "/sales/market_intelligence_subscriptions" with body:
      """
        {
        }
      """
      Then the response status code should be 201
      And the JSON should be valid according to the schema "tests/fixtures/json/sales/market_intelligence_subscription/schemas/market_intelligence_subscription.json"
      And the JSON node "subscriber.@id" should be equal to the string "/people/11"

    Scenario: Editing a market intelligence subscription should not be possible
      Given I authenticate as the intranet user "user-superuser@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "PUT" request to "/sales/market_intelligence_subscriptions/4" with body:
      """
      {
        "customer": "/sales/customers/4"
      }
      """
      Then the response status code should be 405

    Scenario: As a basic user, I can delete a market intelligence subscription I created
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "DELETE" request to "/sales/market_intelligence_subscriptions/3"
      Then the response status code should be 204

    Scenario: As a superuser, I can't delete other market intelligence subscription than mine
      Given I authenticate as the intranet user "user-superuser@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "DELETE" request to "/sales/market_intelligence_subscriptions/4"
      Then the response status code should be 403

