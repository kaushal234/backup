Feature: Third party app
  Scenario: As a basic user, I can access an extended module details
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    And I go to "/mis/modules/38/show"
    Then the response status code should be 200
    And I should see "TPA Extended"
    And I should see "Business Unit / Position"
    And I should see "Whitelist"
    And I should see "Blacklist"
    And I should see "Main admin"
    And I should see "THIRD PARTY APP / EXTENDED"
    And I should not see an "a[href$='/mis/modules/38/edit']" element
    And I should not see an "a[href$='/mis/modules/38/account_reviews']" element
    And I should not see an "a[href$='/mis/modules/38/security_reviews']" element
    And I should not see an "a[href$='/mis/modules/38/update_tasks']" element

  Scenario: As a basic user, I can access a light module details
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    And I go to "/mis/modules/39/show"
    Then the response status code should be 200
    And I should see "TPA Light"
    And I should not see "Business Unit / Position"
    And I should not see "Whitelist"
    And I should not see "Blacklist"
    And I should see "Main admin"
    And I should see "SSO"
    And I should see "MFA Admin"
    And I should see "Security review initial date"
    And I should see "Account review initial date"

  @javascript
  Scenario: As a super user, I can add business unit position and see new update tasks even for a user who will arrive soon
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    And I go to "/mis/modules/38/show#businessUnitPosition"
    Then I select "/business_units/1" from "business_unit_position[businessUnit]"
    Then I select "/positions/14" from "business_unit_position[position]"
    And press "Add"
    And I wait until I see "Business unit position added"
    When I go to "/mis/modules/38/update_tasks"
    And I wait until I see "DSS user"
    And I wait until I see "GRANT_ACCESS"
    When I click on the 1st "div[data-kreyu--data-table-bundle--bootstrap-modal-url-value$='/en/private/mis/modules/38/update_tasks/4/confirmed_modal']" element
    And I wait until I see "By clicking confirm you certify that the user MIS user access to the application TPA Extended is actually granted."
    And I wait until I see "Comment"
    And I wait for "form[action$='/38/update_tasks/4/grant_access_accepted']" element

  @javascript
  Scenario: As a super user, I can confirmed grant access for a user coming soon and see member
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/modules/38/update_tasks?filter_update_task[__search][value]=ARRIVE-DANS-2-JOURS"
    When I click on the 1st "div[data-kreyu--data-table-bundle--bootstrap-modal-url-value$='confirmed_modal']" element
    And I wait until I see "Yes, confirmed"
    And I click on the 1st "form[action$='grant_access_accepted'] .btn[type=submit]" element
    And I wait until I see "Task granted access accepted successfully"
    When I go to "/mis/modules/38/members"
    And I wait until I see "ARRIVE-DANS-2-JOURS jean"

  @javascript
  Scenario: As a super user, I can confirmed grant access with comment and see member
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/modules/38/update_tasks?filter_update_task[__search][value]=DSS+user"
    When I click on the 1st "div[data-kreyu--data-table-bundle--bootstrap-modal-url-value$='confirmed_modal']" element
    And I wait until I see "Yes, confirmed"
    When I fill in "update_task_confirmation[comment]" with "Test comment update task DSS"
    And I click on the 1st "form[action$='grant_access_accepted'] .btn[type=submit]" element
    And I wait until I see "Task granted access accepted successfully"
    When I go to "/mis/modules/38/update_tasks?filter_update_task[status][value]=CONFIRMED"
    And I wait until I see "Comment"
    And I click on the 1st "a[data-bs-target='#update_task--row-action--comment--0']" element
    And I wait until I see "Test comment update task DSS"
    When I go to "/mis/modules/38/members"
    And I wait until I see "dss"

  @javascript
  Scenario: As a super user, I can refuse access and the user should be blacklisted
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/modules/38/update_tasks?filter_update_task[__search][value]=USER_WILL_BECOME_SFE"
    When I click on the 1st "div[data-kreyu--data-table-bundle--bootstrap-modal-url-value$='denied_modal']" element
    And I wait until I see "Yes, denied"
    And I click on the 1st "form[action$='grant_access_denied'] .btn[type=submit]" element
    And I wait until I see "Task granted access denied successfully"
    When I go to "/mis/modules/38/show#blacklist"
    And I wait until I see "user_will_become_sfe"

  Scenario: As a super user, test I can edit an extended module
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    And I go to "/mis/modules/38/edit"
    Then the response status code should be 200
    And I fill in "app_mis_module[securityLevel]" with "/security_levels/4"
    And I check "app_mis_module[mfaAdmin]"
    And I press "submit"
    Then the response status code should be 200
    And I should be on "/mis/modules/38/show"
    And I should see "Self Assessed"

  Scenario: As a super user,  test I can see account reviews
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    And I go to "/mis/modules/38/account_reviews"
    Then the response status code should be 200
    When I go to "/mis/modules/38/account_reviews/add"
    Then the response status code should be 200

  Scenario: As a superuser, test that I can add an account review
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/modules/38/account_reviews/add"
    Then the response status code should be 200
    When I fill in the following:
      | account_review[countStartAdminUsers]       | 4210                    |
      | account_review[countEndAdminUsers]         | 4211                    |
      | account_review[adminsComment]              | test adminsComment      |
      | account_review[countAppUsers]              | 4212                    |
      | account_review[countStartMembers]          | 4213                    |
      | account_review[countEndMembers]            | 4214                    |
      | account_review[countDisabledAccounts]      | 4215                    |
      | account_review[disabledAccountsComment]    | test disabledAccountsComment |
      | account_review[countEnabledAccounts]       | 4216                    |
      | account_review[enabledAccountsComment]     | test enabledAccountsComment |
    And I check "account_review[adminAccountsConfirmed]"
    And I check "account_review[userAccountsConfirmed]"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/mis/modules/38/account_reviews"
    And I should see "4210"
    And I should see "4211"
    And I should see "4212"
    And I should see "4213"
    And I should see "4214"
    And I should see "4215"
    And I should see "4216"

  Scenario: As a super user,  test I can see security reviews
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    And I go to "/mis/modules/38/security_reviews"
    Then the response status code should be 200
    When I go to "/mis/modules/38/security_reviews/add"
    Then the response status code should be 200

  @javascript
  Scenario: As a super user,  Test that If you remove the user from the third party application then they should be blacklisted immediately
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/31/third_party_apps?filter_profile_update_task[user][value]=/people/31&filter_profile_update_task[status][value]=IN_PROGRESS&filter_profile_update_task[updatedAt][value][from]=&filter_profile_update_task[updatedAt][value][to]=&page_profile_update_task=1&limit_profile_update_task=25&sort_profile_update_task[updatedAt]=desc"
    And I wait until I see "TPA Extended"
    And I accept the alert
    When I click on the 1st "a[href*='/directory/people/31/third_party_apps/delete/1']" element
    And I wait until I see "New update task created"
    When I click on the 1st "a[href*='#updateTasksList']" element
    And I wait until I see "TPA Extended"
    When I click on the 1st "div[data-kreyu--data-table-bundle--bootstrap-modal-url-value$='denied_modal']" element
    And I wait until I see "Yes, denied"
    When I click on the 1st "form[action$='/grant_access_denied'] .btn[type=submit]" element
    And I wait until I see "Task granted access denied successfully"
    When I go to "/mis/modules/38/show?page_business_unit_position=1&limit_business_unit_position=10#blacklist"
    And I wait until I see "MARTIN Anne Sophie"


  @javascript
  Scenario: As a super user,  Test that if the user is already blacklisted and they have been granted access then they come back to access list
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/126/third_party_apps?filter_profile_update_task[user][value]=/people/126&filter_profile_update_task[status][value]=IN_PROGRESS&filter_profile_update_task[updatedAt][value][from]=&filter_profile_update_task[updatedAt][value][to]=&limit_profile_update_task=25&page_profile_update_task=1&sort_profile_update_task[updatedAt]=desc"
    And I wait until I see "TPA Extended"
    And I accept the alert
    When I click on the 1st "a[href*='/directory/people/126/third_party_apps/delete/3']" element
    And I wait until I see "New update task created"
    And I wait until I see "Total number of records: 0"
    When I click on the 1st "a[href*='#updateTasksList']" element
    And I wait until I see "TPA Extended"
    When I click on the 1st "div[data-kreyu--data-table-bundle--bootstrap-modal-url-value$='confirmed_modal']" element
    And I wait until I see "Yes, confirmed"
    When I click on the 1st "form[action$='/grant_access_accepted'] .btn[type=submit]" element
    And I wait until I see "Task granted access accepted successfully"
    When I click on the 1st "a[href*='#accessList']" element
    And I wait until I see "TPA Extended"

