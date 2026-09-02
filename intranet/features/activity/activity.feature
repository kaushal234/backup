Feature: Comments

  Scenario: Test that I can add a comment on a people as superuser
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/activities/comments/add?resourceIriId=/people/12"
    Then the response status code should be 200
    And I should see "Enter your comment"
    When I fill in "comment_message" with "Coucou"
    And press "comment_submit"
    Then the response status code should be 200
    And I should be on the homepage
    And I should see "Your comment has been successfully posted"
