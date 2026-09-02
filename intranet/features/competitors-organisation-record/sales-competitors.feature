Feature: Sales / Customer

  Scenario: An anonymous user can't see competitors pages
    When I go to "/sales/competitors"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/sales/competitors/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: A basic user can see competitors pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/competitors"
    Then the response status code should be 200
    And The module should be "COR"
    And I should not see an "a[href$='/sales/competitors/add']" element
    And I should see "Competitors"
    When I go to "/sales/competitors/1/show"
    Then the response status code should be 200
    And I should not see an "a[href$='/sales/competitors/1/edit']" element
    And I should not see an "a[href$='/sales/competitors/1/delete']" element
    And I should see "Loser"
    And I should see "5 latest MIM"
    And I should see "5 Latest Lost Order (FCR)"
    And I should see "5 latest CPR"

  Scenario: A superuser can see competitors pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/competitors"
    Then the response status code should be 200
    And I should see an "a[href$='/sales/competitors/add']" element
    And I should see "Competitors"
    And I should see "this short is blue and fitted"
    When I go to "/sales/competitors/1/show"
    Then I should see "Loser"
    And I should see "Edit"
    And I should see "Delete"
    And I should see "Loser"
    And I should see "5 latest MIM"
    And I should see "5 Latest Lost Order (FCR)"
    And I should see "5 latest CPR"

  Scenario: A superuser can add a competitor
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/competitors/add"
    Then the response status code should be 200
    When I fill in the following:
      | competitor_form[name]  | Thor 2    |
      | competitor_form[shortDescription]  | short desc    |
      | competitor_form[description]  | desc |
      | competitor_form[url]  | http://www.thor2.com  |
    And I check "Type English"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/sales/competitors"
    And I should see "4"
    And I should see "Thor 2"

  Scenario: A superuser can edit a competitor
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/competitors/1/edit"
    Then the response status code should be 200
    When I fill in "competitor_form[name]" with "CompetitorEdited"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/sales/competitors/1/show"
    And I should see "Competitor successfully updated"
    And I should see "CompetitorEdited"

  Scenario: A basic user can filter competitors
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/competitors?filter_competitor[productType][value][]=sales/product_types/2"
    Then the response status code should be 200
    And I should see "this short is blue and fitted"
    And I should not see "winner"
    And I should not see "Thor 2"

  Scenario: A basic user can see the links related to a competitor
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/competitors/1/show"
    Then the response status code should be 200
    And I should see "MIM"
    And I should see "FCR"
    And I should see "CPR"

  Scenario: A basic user can see files page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/competitors/1/files"
    Then the response status code should be 200
    And I should not see "Upload a file"
    And I should see "Competitor Files"

  Scenario: A superuser can see files page and upload a file
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/competitors/1/files"
    Then the response status code should be 200
    And I should see "Competitor Files"
    And I should see "Upload a file"
    And I should see "File description"
    And I should see "No results"
    And I attach the file "file.pdf" to "competitor[file]"
    And I fill in "competitor[description]" with "this is a file description"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/sales/competitors/1/files"
    And I should see "File successfully uploaded"
    And I should see 1 "#competitor-files table.footable tbody tr" elements
    And I should see "this is a file description"

  Scenario: A superuser can't delete a used competitor
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/competitors/1/delete"
    Then the response status code should be 200
    And I should be on "/sales/competitors"
    And I should see "Competitor can't be deleted because it's used in another resource"
    And I should see "CompetitorEdited"
    And I should see "winner"
    And I should see "new"
    And I should see "Thor 2"

  Scenario: A superuser can delete an unused competitor
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/competitors/3/delete"
    Then the response status code should be 200
    And I should be on "/sales/competitors"
    And I should see "Competitor successfully deleted"
    And I should see "CompetitorEdited"
    And I should see "winner"
    And I should not see "pantacourt"
    And I should see "Thor 2"

  Scenario: As a user, test that i can download competitors
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "sales/competitors?export[exporter]=xlsx&export[filename]=competitors&export[strategy]=include_all&export[useHeaderRow]=1&columns=id,name,shortDescription,url,productTypes&page_competitor=1&limit_competitor=25"
    Then the response status code should be 200