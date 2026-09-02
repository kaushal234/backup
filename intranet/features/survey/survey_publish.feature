Feature: Survey / Publishing a survey

  Scenario: As an anonymous user, test that i'm not allowed to see campaigns pages
    When I go to "surveys/1/campaigns"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that I'm not allow on campaigns pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/campaigns"
    Then I should not be on "/surveys/1/campaigns"
    And I should see "You do not have permissions"

  Scenario: As a basic user, test that I'm not allow on campaigns addition page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/campaigns/add"
    Then I should not be on "/surveys/1/campaigns/add"
    And I should see "You do not have permissions"

#  Scenario: Publish a survey
#    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
#    When I go to "/surveys/1/campaigns/add"
#    Then the response status code should be 200
#    And The module should be "SRV"
#    And I should see "Publish this survey"
#    And I select "/sales/customers/1" from "survey[customers][]"
#    And press "Generate list of contacts"
#    Then I should be on "/surveys/1/campaigns/add"
#    And I select "/sales/extranet_users/200" from "survey[extranet_users][]"
#    And I additionally select "/sales/extranet_users/209" from "survey[extranet_users][]"
#    And I additionally select "/sales/extranet_users/210" from "survey[extranet_users][]"
#    And I additionally select "/sales/extranet_users/211" from "survey[extranet_users][]"
#    And I fill in "survey[description]" with "Pubish"
#    And press "survey[publish]"
#    Then the response status code should be 200
#    When I go to "/surveys/1/campaigns/4/published"
#    Then the response status code should be 200
#    And I should see 4 "table.report-table tbody tr" elements

  Scenario: As a basic user, test that I can't see campaigns link management
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/campaigns"
    Then the response status code should be 200
    And I should not see an "a[href$='/campaigns/1/edit']" element
    And I should not see an "a[href$='/campaigns/1/delete']" element

  Scenario: As a superuser, test I can edit a campaign
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/campaigns/1/edit"
    Then the response status code should be 200
    And I fill in "campaign_form[description]" with "A new dAIsCrPZioN"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/surveys/1/campaigns"
    And I should see "A new dAIsCrPZioN"

  Scenario: As a superuser, test I can delete a campaign
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/surveys/1/campaigns/1/delete"
    Then the response status code should be 200
    And I should be on "/surveys/1/campaigns"
    And I should not see "deleted survey"
