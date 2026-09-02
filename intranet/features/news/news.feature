Feature: News

  Scenario: As an anonymous user, test that i'm not allowed to see news pages
    When I go to "/news"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/news/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: News pagination
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/news"
    Then the response status code should be 200
    And The module should be "INN"
    And I should see "Internal News"

  Scenario: As a basic user, test that i'm allowed to see news
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/news"
    Then the response status code should be 200
    And I should see "Internal News"
    And I should not see an "a[href$='/news/add']" element
    When I go to "/news/26/show"
    Then the response status code should be 200
    And I should see "News Title"
    And I should not see an "a[href$='/news/26/edit']" element
    And I should not see an "a[href$='/news/26/delete']" element
    And I should see an "h2.news-title" element
    And I should see an "small.news-date" element

  Scenario: As a superuser, test that i'm allowed to see news
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/news"
    Then the response status code should be 200
    And I should see an "a[href$='/news/add']" element
    When I go to "/news/26/show"
    Then the response status code should be 200
    And I should see "News Title"
    And I should see an "a[href$='/news/26/edit']" element
    And I should see an "a[href$='/news/26/delete']" element

  Scenario: As a superuser, test that i can add a news
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/news/add"
    Then the response status code should be 200
    When I fill in the following:
      | news[title]    | My awesome crazy title     |
      | news[content]  | Lorem ipsum dolor sit amet |
    And I select "/people/1" from "news[people]"
    And I select "/news_categories/1" from "news[category]"
    And I select "/departments/1" from "news[department]"
    And I select "/divisions/1" from "news[division]"
    And I should see an "label.col-form-label" element
    And I should see an "button[data-action='form-collection#addCollectionElement']" element
    And press "submit"
    Then the response status code should be 200
    And I should see "My awesome crazy title"

  Scenario: As a superuser, test that i can add a news category
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/news/add-category"
    Then the response status code should be 200
    When I fill in the following:
      | name | Niouse |
    And press "submit"
    Then the response status code should be 200
    And I should be on "/news"

  Scenario: As a superuser, test that i can edit a news
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I am on "/news/26/edit"
    And I fill in the following:
      | news[title]   | Wow I m so creative to write news titles |
      | news[content] | Lorem ipsum dolor sit amet |
      | news[date] | 06/19/2042, 3:42 AM |
    And I select "/people/1" from "news[people]"
    And I select "/news_categories/1" from "news[category]"
    And I select "/departments/1" from "news[department]"
    And I select "/divisions/1" from "news[division]"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/news/26/show"
    And I should see "Wow I m so creative to write news titles"
    And I should see "Lorem ipsum dolor sit amet"

  Scenario: As a superuser, test that i can delete a news
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/news/26/delete"
    Then the response status code should be 200
    And I should be on "/news"

  Scenario: As a basic user, test that i can email a news
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/news/27/show"
    Then the response status code should be 200
    Then I should see "email"
    And I should see an "a[href$='/news/27/email']" element
    When I go to "/news/27/email"
    Then the response status code should be 200
    Then I select "user-hr@tld.fr" from "resource_email[to][]"
    And I press "submit"
    Then the response status code should be 200
    Then I should be on "/news/27/show"
    And I should see "The news \"News Title without category\" has been successfully sent by email"

  Scenario: As a superuser, test that i can add a news with banner text
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/news/add"
    Then the response status code should be 200
    When I fill in the following:
      | news[title]      | My awesome crazy very strong title     |
      | news[content]    | Lorem ipsum dolor sit amet |
      | news[bannerText] | My awesome banner text     |
    And I select "/news_categories/1" from "news[category]"
    And I select "/people/1" from "news[people]"
    And I check "news[banner]"
    And press "submit"
    Then the response status code should be 200
    And I should see "My awesome crazy very strong title"
    And I should see "My awesome banner text"