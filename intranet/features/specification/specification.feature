Feature: Module / Specification

  Scenario: As an anonymous user, test that i'm not allowed to see specification pages
    When I go to "/mis/modules/1/specifications/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, I should be allowed to go on a created specification for a module
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/modules/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/mis/modules/1/specifications/1/show']" element
    When I go to "/mis/modules/1/specifications/1/show"
    Then the response status code should be 200
    And I should be on "/mis/modules/1/specifications/1/show"

  Scenario: As a MOO I can create a specification for a module
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/modules/11/show"
    And I should see an "a[href$='/mis/modules/11/specifications/add']" element
    When I go to "/mis/modules/11/specifications/add"
    And I should be on "/mis/modules/11/specifications/6/show"

  Scenario: As a MKU I can create a specification for a module
    Given I authenticate as "user-moo-esr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/modules/30/show"
    And I should see an "a[href$='/mis/modules/30/specifications/add']" element
    When I go to "/mis/modules/30/specifications/add"
    And I should be on "/mis/modules/30/specifications/7/show"

  # user-basic is the LKU of ER module
  Scenario: As a LKU I can create a specification for a module
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/modules/27/show"
    And I should see an "a[href$='/mis/modules/27/specifications/add']" element
    When I go to "/mis/modules/27/specifications/add"
    And I should be on "/mis/modules/27/specifications/8/show"

  Scenario: As a MIS I can create a specification for a module
    Given I authenticate as "user-mis@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/modules/32/show"
    And I should see an "a[href$='/mis/modules/32/specifications/add']" element
    When I go to "/mis/modules/32/specifications/add"
    And I should be on "/mis/modules/32/specifications/9/show"
