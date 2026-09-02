Feature: The news legacy pages should be redirected

  Scenario: The news should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/internal_news/internal_news.php"
    Then I should not be on a legacy page
    And I should be on the exact url "/news"
    When I go to "/internal_news/internal_news.php?mode=record_view&id=6265652"
    Then the response status code should be 404
    When I go to "/internal_news/internal_news.php?mode=record_view&id=5355"
    Then I should not be on a legacy page
    And I should be on the exact url "/news/1/show"
    When I go to "/internal_news/internal_news.php?mode=form_add"
    Then I should not be on a legacy page
    And I should be on the exact url "/news/add"
    When I go to "/internal_news/internal_news.php?mode=form_edit&id=5355"
    Then I should not be on a legacy page
    And I should be on the exact url "/news/1/edit"
    When I go to "/internal_news/internal_news.php?mode=del&id=5356"
    Then I should not be on a legacy page
    And I should be on the exact url "/news"
    And I should see "The news has been deleted."
