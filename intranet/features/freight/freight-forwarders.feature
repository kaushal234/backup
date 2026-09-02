Feature: Sales / Freight Forwarders

  Scenario: As an anonymous user, test that i'm not allowed to see freight forwarder pages
    When I go to "/sales/freight-forwarders"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/sales/freight-forwarders/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a supeuser, test that i'm allowed to see freight forwarder pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/freight-forwarders"
    Then the response status code should be 200
    And The module should be "SQR"
    And I should see an "a[href$='/sales/freight-forwarders/add']" element
    And I should see an "a[href$='/sales/freight-forwarders/1/show']" element
    And I should see an "a[href$='/sales/freight-forwarders/2/show']" element
    And I should see "Last 10 Freight Forwarders"
    When I go to "/sales/freight-forwarders/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/sales/freight-forwarders/1/edit']" element
    And I should see an "a[href$='/sales/freight-forwarders/1/delete']" element

  Scenario: As a superuser, test that i can edit a freight forwarder
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/freight-forwarders/1/edit"
    Then the response status code should be 200
    When I fill in "freight_forwarder_form[name]" with "Jason Statham"
    And press "Submit"
    Then the response status code should be 200
    And I should be on "/sales/freight-forwarders/1/show"
    And I should see "Jason Statham"

  Scenario: As a superuser, test that i can add a freight forwarder
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/freight-forwarders/add"
    Then the response status code should be 200
    When I fill in "freight_forwarder_form[name]" with "Transporteur"
    When I fill in "freight_forwarder_form[language]" with "en"
    When I fill in "freight_forwarder_form[emails][0]" with "transporteur@fedex.com"
    And press "Submit"
    Then the response status code should be 200
    And I should be on "/sales/freight-forwarders/3/show"
    And I should see "Transporteur"

  Scenario: A superuser user can delete freight forwarder
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/freight-forwarders/3/delete"
    Then the response status code should be 200
    And I should be on "/sales/freight-forwarders"
    And I should see "Freight forwarder has been deleted successfully"
