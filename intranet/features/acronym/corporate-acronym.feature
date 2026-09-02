Feature: Corporate / Acronyms

  Scenario: As an anonymous user, test that i'm not allowed to see acronyms pages
    When I go to "/acronyms"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/acronyms/6"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see acronyms pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/acronyms"
    Then the response status code should be 200
    And The module should be "AGR"
    And I should see "AGR Homepage"
    And I should see 4 "table.footable tr" elements
    And the "table.footable tbody tr:nth-child(1) td:nth-child(1)" element should contain "1"
    And the "table.footable tbody  tr:nth-child(1) td:nth-child(2)" element should contain a text
    And the "table.footable tbody  tr:nth-child(1) td:nth-child(3)" element should contain a text
    And I should not see an "a[href$='/acronyms/add']" element
    And the response should contain "/acronyms/2"
    When I go to "/acronyms/2"
    Then the response status code should be 200
    And I should see "Acronym detail"
    And I should see 4 ".association-table tr" elements
    And I should see "Product Demerit Claim"
    And I should see "AGR Lines"
    And I should see 4 "table.footable tr" elements
    And the "table.footable tbody tr:nth-child(1) td:nth-child(1)" element should contain "5"
    And the "table.footable tbody  tr:nth-child(1) td:nth-child(2)" element should contain a text
    And I should not see an "a[href$='/acronyms/2/edit']" element
    And I should not see an "a[href$='/acronyms/2/delete']" element

  Scenario: As a superuser, test that i'm allowed to see acronyms pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/acronyms"
    Then the response status code should be 200
    And I should see "AGR Homepage"
    And the response should contain "/acronyms/2"
    And I should see an "a[href$='/acronyms/add']" element
    And the response should contain "/acronyms/add"
    When I go to "/acronyms/2"
    Then the response status code should be 200
    And I should see an "a[href$='/acronyms/2/edit']" element
    And I should see an "a[href$='/acronyms/2/delete']" element

  Scenario: As a superuser, test that i can add an acronym
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/acronyms/add"
    When I fill in the following:
      | acronym[acronym]          | TAGR                        |
      | acronym[shortDescription] | My awesome AGR              |
      | acronym[description]      | My awesome long description |
    And I select "/acronym_categories/6" from "acronym[categories][]"
    And I additionally select "/acronym_categories/2" from "acronym[categories][]"
    Then press "submit"
    Then the response status code should be 200
    And I should see "TAGR"
    And I should see "FINANCE"
    And I should see "TLD_PROCESS"

  Scenario: As a superuser, test that I can't create acronym with same name and I keep categories in the select
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/acronyms/add"
    When I fill in the following:
      | acronym[acronym]          | TAGR                        |
      | acronym[shortDescription] | My awesome AGR              |
      | acronym[description]      | My awesome long description |
    And I select "/acronym_categories/6" from "acronym[categories][]"
    And I additionally select "/acronym_categories/5" from "acronym[categories][]"
    And press "submit"
    Then the response status code should be 200
    And I should see "This value is already used."

  Scenario: As a superuser, test that i can edit an acronym
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/acronyms/2/edit"
    Then the response status code should be 200
    When I fill in "acronym[shortDescription]" with "other AGR"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/acronyms/2"
    And I should see "other AGR"

  Scenario: Acronyms can be exported as xlsx files
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/acronyms/download"
    Then the response status code should be 200
    Then I should see response headers "content-type" with "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    Then I should see response headers "content-disposition" with 'inline; filename=acronyms.xlsx'
