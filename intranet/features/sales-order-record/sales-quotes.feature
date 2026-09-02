Feature: Sales / Quotes

  Scenario: As a sales admin, I can access the quote list
    Given I authenticate as "user-sa@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/quotes"
    Then I should be on "/sales/quotes"
    And I should see "LN QUotes"
    And I should see an "a[href^='/sales/quotes/1/delete']" element
    And I should see "QU123456"