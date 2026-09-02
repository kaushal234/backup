Feature: Test FAQItemTypes API

  Scenario: Request all plan item types
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/plan_item_types"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/first_article_qualification/schemas/plan_item_types.json"

  Scenario: Request one FAQ
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/plan_item_types/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/first_article_qualification/schemas/plan_item_type_detail.json"

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Quality\FirstArticleQualification\PlanItemType" should only be available for intranet user

  Scenario: A basic user cannot create a plan item type
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/plan_item_types" with the body "tests/fixtures/json/first_article_qualification/dummies/post_plan_item_type.json"
    Then the response status code should be 403

  Scenario: the CMO can create a plan item type
    Given I authenticate as the intranet user "user-cmo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/plan_item_types" with the body "tests/fixtures/json/first_article_qualification/dummies/post_plan_item_type.json"
    Then the response status code should be 201
    And the JSON node "description" should be equal to the string "Philippe Carle"
    And the JSON node "requestablePriorDelivery" should be true
    And the JSON node "requestableAtPurchaseOrder" should be false

  Scenario: Basic user cannot create a plan item type
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/plan_item_types/5" with body:
    """
    {
        "description": "L'Agent chimique X",
        "requestablePriorDelivery": false,
        "requestableAtPurchaseOrder": true

    }
    """
    Then the response status code should be 403


  Scenario: the CMO can create a plan item type
    Given I authenticate as the intranet user "user-cmo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/plan_item_types/5" with body:
    """
    {
        "description": "We all work for tld-gse",
        "requestablePriorDelivery": false,
        "requestableAtPurchaseOrder": true

    }
    """
    Then the response status code should be 200
    And the JSON node "description" should be equal to the string "We all work for tld-gse"
    And the JSON node "requestablePriorDelivery" should be false
    And the JSON node "requestableAtPurchaseOrder" should be true
