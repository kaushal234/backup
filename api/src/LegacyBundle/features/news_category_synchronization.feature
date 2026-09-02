Feature: test news category double write

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create a news category in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/news_categories" with body:
    """
    {
      "name": "category_test"
    }
    """
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "lists"

  Scenario: Update a news category in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/news_categories/1" with body:
    """
    {
      "name": "category_test_2"
    }
    """
    Then the response status code should be 200
    And the column "list_item" from the "lists" legacy table has been updated with string "category_test_2"
    And the column "parent_id" from the "lists" legacy table has been updated with integer 0
    And the column "list_name" from the "lists" legacy table has been updated with string "categories"
    And the column "list_key" from the "lists" legacy table has been updated with string ""
