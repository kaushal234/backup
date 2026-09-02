Feature: Errors

  @javascript
  Scenario: Test that a page not found returns a 404
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/kittens/42"
    And I wait until I see "404"
    And I wait until I see "Page Not Found"
