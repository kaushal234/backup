Feature: Exception listener must handle and reformat properly

  Scenario: Not found exception on routes not handled by api-platform are formatted correctly
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/not-existing"
    And the response should be an error stating "Not Found" with status code 404

  Scenario: Access Denied exception on routes not handled by api-platform are formatted correctly
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/news/10/picture" with file "file" "image_1200x1200.jpg"
    And the response should be an error stating "Access Denied" with status code 403
