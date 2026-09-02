Feature: Test news double write

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create a news in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/news" with body:
    """
    {
      "title": "This is a title",
      "content": "This is a content",
      "category": "/news_categories/2",
      "people": "/people/12"
    }
    """
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "internal_news"

  Scenario: Update a news in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/news/26" with the body "tests/fixtures/json/news/dummies/put.json"
    Then the response status code should be 200
    And the column "title" from the "internal_news" legacy table has been updated with string "This&eacute; is a title"
    And the column "en" from the "internal_news" legacy table has been updated with string "This&eacute; is a <b>content</b>"
    And the column "date" from the "internal_news" legacy table has been updated with string "2016-03-07"
     # /category/2 legacyId = 4118
    And the column "cat" from the "internal_news" legacy table has been updated with integer 4118
