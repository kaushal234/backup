Feature: Evendors News

  Scenario: As an anonymous user, test that i'm not allowed to see evendors news pages
    When I go to "/materials/evendors_news"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/materials/evendors_news/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: News pagination
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/materials/evendors_news"
    Then the response status code should be 200
    And I should see "eVendors News"
    And I should see an "a[href$='/materials/evendors_news?page=2']" element

  Scenario: As a basic user, test that i'm allowed to see evendors news
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/materials/evendors_news"
    Then the response status code should be 200
    And I should see "eVendors News"
    And I should not see an "a[href$='/materials/evendors_news/add']" element
    And I should see 25 "div.news-list-element" elements
    When I go to "/materials/evendors_news/1/show"
    Then the response status code should be 200
    And I should see "My little pony 10/10 best drama ever"
    And I should see "location_factory"
    And I should not see an "a[href$='/materials/evendors_news/1/edit']" element
    And I should not see an "a[href$='/materials/evendors_news/1/delete']" element
    And I should not see an "a[href$='/en/private/materials/evendors_news/29/show-file/1']" element
    And I should see an "small.news-date" element
    And I should see an "span.label.label-info" element

  Scenario: As a superuser, test that i'm allowed to see evendors news
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/materials/evendors_news"
    Then the response status code should be 200
    And I should see 25 "div.news-list-element" elements
    And I should see an "a[href$='/materials/evendors_news/add']" element
    When I go to "/materials/evendors_news/1/show"
    Then the response status code should be 200
    And I should see "My little pony 10/10 best drama ever"
    And I should see an "a[href$='/materials/evendors_news/1/edit']" element
    And I should see an "a[href$='/materials/evendors_news/1/delete']" element

  Scenario: As a superuser, test that i can add a evendors news
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/materials/evendors_news/add"
    Then the response status code should be 200
    When I fill in the following:
      | evendors_news[content] | Lorem ipsum dolor sit amet new|
      | evendors_news[publishedAt] | 11/30/2022, 11:42 PM |
      | evendors_news[unpublishedAt] | 12/30/2022, 11:42 PM |
    And I select "/people/1" from "evendors_news[createdBy]"
    And I select "/locations/29" from "evendors_news[factories][]"
    And I attach the file "picture.png" to "evendors_news[files][]"
    And press "submit"
    Then the response status code should be 200
    And I should see "Lorem ipsum dolor sit amet"
    And I should see "picture.png"
    And I should see an "a[href^='/materials/evendors_news/31/show-file/']" element

  Scenario: As a superuser, test that i can edit a evendors news
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I am on "/materials/evendors_news/26/edit"
    And I fill in the following:
      | evendors_news[content] | Lorem ipsum dolor sit amet edit |
      | evendors_news[publishedAt] | 11/30/2022, 11:42 PM |
      | evendors_news[unpublishedAt] | 12/30/2022, 11:42 PM |
    And I select "/people/1" from "evendors_news[createdBy]"
    And I select "/locations/29" from "evendors_news[factories][]"
    And I attach the file "picture.png" to "evendors_news[files][]"
    And press "submit"
    Then the response status code should be 200
    And I should see "picture.png"
    And I should be on "/materials/evendors_news/26/show"
    And I should see "Lorem ipsum dolor sit amet"

  Scenario: As a superuser, test that i can delete a file
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I am on "/materials/evendors_news/26/edit"
    When I follow "deleteFile"
    And I should be on "/materials/evendors_news/26/edit"
    And I should not see "picture.png"

  Scenario: As a superuser, test that i can delete a news
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/materials/evendors_news/24/delete"
    Then the response status code should be 200
    And I should be on "/materials/evendors_news"
