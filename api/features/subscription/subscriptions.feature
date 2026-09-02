Feature: subscription can be added on a given resource

  Scenario: Subscription should be accessible to XU but restricted to their own subscriptions
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/subscriptions"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/subscription/schemas/subscriptions.json"
    And the JSON node "hydra:totalItems" should be equal to 1
    Then the JSON node "hydra:member[0].@id" should be equal to "/subscriptions/3"

  Scenario: A subscription is unique for a resource and a people
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/subscriptions" with body:
    """
    {
      "resource": "/sales/sales_forecasts/1",
      "user": "/people/31"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should contain "This value is already used."

  Scenario: Request all subscriptions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/subscriptions"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/subscription/schemas/subscriptions.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Common\Subscription" is exposed on the API
    Then the filter "resource" should be available and its type should be "string"
    And the filter "user" should be available and its type should be "string"
    And the filter "order[createdAt]" should be available and its type should be "string"
    And the filter "order[user.lastname]" should be available and its type should be "string"
    And the filter "createdAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "createdAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "q" should be available and its type should be "string"
    And the filter "module[]" should be available and its type should be "string"
    And the filter "normalization_groups[]" should be available and its type should be "string"

  Scenario: Request all subscription with an additional normalization group
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/subscriptions?normalization_groups[]=people_photo"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/subscription/schemas/subscriptions.json"

  Scenario: A subscription can't be created on a non-managed resource
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/subscriptions" with body:
    """
    {
      "resource": "/news/1",
      "user": "/people/11"
    }
    """
    Then the response status code should be 403

  Scenario: As user basic, a subscription can be created on a VWC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/subscriptions" with body:
    """
    {
      "resource": "/purchasing/ncr_vendor_warranty_claims/5",
      "user": "/people/11"
    }
    """
    Then the response status code should be 201

  Scenario: As user basic, I can delete a subscription on VWC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/subscriptions/8"
    Then the response status code should be 204