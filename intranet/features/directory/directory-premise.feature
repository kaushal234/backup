Feature: Premise

  Scenario: As an anonymous user, test that i'm not allowed to see premises pages
    When I go to "/directory/premises"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/directory/premises/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see premises pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/premises/dashboard"
    Then the response status code should be 200
    When I go to "/directory/premises/list"
    Then the response status code should be 200
    When I go to "/directory/premises/1/show"
    Then the response status code should be 200
    And The module should be "PRE"
    And I should see an "a[href$='/directory/premises/dashboard']" element
    And I should see an "a[href$='/directory/premises/list']" element
    And I should not see an "a[href$='/directory/premises/1/transfer']" element
    And I should not see an "a[href$='/directory/premises/add']" element
    And I should not see an "a[href$='/directory/premises/1/edit']" element
    And I should not see an "a[href$='/directory/premises/type']" element

  Scenario: As a hr user, test that i'm allowed to see premises pages
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/premises/dashboard"
    Then the response status code should be 200
    When I go to "/directory/premises/list"
    Then the response status code should be 200
    When I go to "/directory/people/list?exists[premise]=0"
    Then the response status code should be 200
    When I go to "/directory/premises/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/directory/premises/1/transfer']" element
    And I should see an "a[href$='/directory/premises/add']" element
    And I should see an "a[href$='/directory/premises/1/edit']" element
    And I should see an "a[href$='/directory/premises/type']" element

  Scenario: As a hr user, test that i can add a premise if latitude and longitude are provide
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/premises/add"
    Then the response status code should be 200
    Then I fill in "premise_form[name]" with "TarbeCity"
    And I fill in "premise_form[description]" with "Au pied des pyrénées il y a un petit village"
    And I fill in "premise_form[address][street1]" with "Rue de la soif"
    And I fill in "premise_form[address][street2]" with "Block 501317"
    And I fill in "premise_form[address][postalCode]" with "33450"
    And I fill in "premise_form[address][city]" with "TarBE"
    And I fill in "premise_form[address][state]" with "LE SUD OUEST !"
    And I select "/airports/62" from "premise_form[airport]"
    And I fill in "premise_form[latitude]" with "40.71"
    And I fill in "premise_form[longitude]" with "-74,00"
    And I select "France" from "premise_form[address][country]"
    And I select "/mis/support_teams/2" from "premise_form[supportTeam]"
    And I select "/premise_tags/23" from "premise_form[tags][]"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/directory/premises/list"
    And I should see "Premise has been successfully saved"
    And I should see "TarbeCity"

  Scenario: As a hr user, test that i can edit a premise if latitude and longitude are provided
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/premises/5/edit"
    Then the response status code should be 200
    Then I fill in "premise_form[name]" with "LoreDelmar"
    And I fill in "premise_form[description]" with "Podemos ver aqui un pequeno piso"
    And I fill in "premise_form[address][street1]" with "Calle de la paella"
    And I fill in "premise_form[address][street2]" with "Blocado 501317"
    And I fill in "premise_form[address][postalCode]" with "Espa3541"
    And I fill in "premise_form[address][city]" with "ElMar"
    And I fill in "premise_form[address][state]" with "Espania del sud"
    And I fill in "premise_form[latitude]" with "40.71"
    And I fill in "premise_form[longitude]" with "-74,00"
    And I select "/airports/62" from "premise_form[airport]"
    And I select "/mis/support_teams/2" from "premise_form[supportTeam]"
    And I select "Spain" from "premise_form[address][country]"
    And I select "/premise_tags/23" from "premise_form[tags][]"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/directory/premises/5/show"
    And I should see "Premise has been successfully saved"
    And I should see "LoreDelmar"

  Scenario: A hr user can transfer a premise
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/premises/1/transfer"
    And I select "/premises/4" from "premise_form[target]"
    And press "submit"
    Then the response status code should be 200
    And I should see "The employees of the premise has been successfully transferred"
    And I should be on "/directory/premises/list"

  Scenario: As a hr user, test that i'm allowed to see premises type page
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/premises/type"
    Then the response status code should be 200
    And I should see an "a[href$='/directory/premises/type/add']" element
    And I should see an "a[href$='/directory/premises/type/22/delete']" element
    And I should see an "a[href$='/directory/premises/type/22/edit']" element

  Scenario: As a hr user, test that i can add a premise type
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/premises/type/add"
    Then the response status code should be 200
    Then I fill in "premise_tag_form[name]" with "Gros magasin"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/directory/premises/type"
    And I should see "Premise type has been successfully saved"
    And I should see "Gros magasin"

    Scenario: As a hr user, test that i can edit a premise type
      Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
      When I go to "/directory/premises/type/22/edit"
      Then the response status code should be 200
      Then I fill in "premise_tag_form[name]" with "Petit magasin"
      And press "submit"
      Then the response status code should be 200
      And I should be on "/directory/premises/type"
      And I should see "Premise type has been successfully saved"
      And I should see "Petit magasin"

    Scenario: As a hr user, test that i can delete a premise type
      Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
      When I go to "/directory/premises/type/22/delete"
      Then the response status code should be 200
      And I should be on "/directory/premises/type"
      And I should see "Premise type deleted successfully"
      And I should not see "Petit magasin"

  Scenario: As a hr user, test that i can add a premise with no country, and don't have an error 500 on the page with premise select
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/premises/add"
    Then the response status code should be 200
    Then I fill in "premise_form[name]" with "pre"
    And I fill in "premise_form[description]" with "torien"
    And I fill in "premise_form[longitude]" with "5.9"
    And I fill in "premise_form[latitude]" with "65.7"
    And I select "/mis/support_teams/2" from "premise_form[supportTeam]"
    And I select "/premise_tags/25" from "premise_form[tags][]"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/directory/premises/list"
    And I should see "Premise has been successfully saved"
    And I should see "torien"
