Feature: Directory / People

  Scenario: As an anonymous user, test that i'm not allowed to see people pages
    When I go to "/directory/people/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see people pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/1/show"
    Then the response status code should be 200
    And I should see "people Details"

  Scenario: As a superuser, test that i'm allowed to see people pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/11/show"
    And The module should be "USER"
    Then I should see "user-basic@tld.fr"
    When I go to "/directory/people/12/show"
    Then I should see "user-superuser@tld.fr"

  Scenario: As a superuser, test that i can edit a people
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/5/edit"
    And I wait until I see "Request to add new premise"
    When I fill in the following:
      | people[email]       | MYEDITTEST@hane.net |
      | people[firstname]   | Mark                |
      | people[lastname]    | DOE                 |
      | people[jobTitle]    | Magasin supervisor  |
    And I select "/business_units/1" from "people[businessUnit]"
    And I select "/business_units/19" from "people[legalEntity]"
    And I select "/positions/6" from "people[position]"
    And I select "/departments/2" from "people[department]"
    And I select "/people/32" from "people[supervisor]"
    And I select "/contract_types/1" from "people[contractType]"
    And I fill in "people[coefficient]" with "42"
    And I select "/premises/1" from "people[premise]"
    And press "submit"
    Then the response status code should be 200
    And I should see "MYEDITTEST@hane.net"
    And I should see "Andalouza"
    And I should see "The people have been updated"

  Scenario: As a superuser, test that I can manage user acl
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/13/acls"
    Then the response status code should be 200
    Then I should see "GG_HR"
    Then the response should contain "/directory/people/13/acls/add"
    Then the response should contain "/directory/people/13/acls/import"
    Then the response should contain "/directory/people/13/acls/5/remove?_token="

  Scenario: As a superuser, test that I can import acls from a user to another and force a new location
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/13/acls/import"
    Then the response status code should be 200
    Then I should see "GG_HR"
    And I select "/people/13" from "import_acl[user]"
    And I select "/locations/18" from "import_acl[location]"
    And press "import_acl_submit_step_1"
    Then the response should contain "/acls/4"
    Then the response should contain "/acls/5"
    And I check "import_acl_acl_choice_acls_1"
    And press "import_acl_submit_step_2"
    Then the response status code should be 200

  Scenario: As a superuser, test that i can download people vcard
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/5/vcard"
    Then the response status code should be 200
    Then I should see response headers "content-type" with "text/x-vcard;charset=UTF-8"

  Scenario: As a basic user, test that I can't download people list
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/download"
    Then the response status code should be 200
    Then I should see "You do not have permissions"

  Scenario: As a super user, test that I can't download people list
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/download"
    Then the response status code should be 200
    And I press "submit"
    Then I should see response headers "content-type" with "text/csv; charset=utf-8"

  Scenario: As a basic, test that i can download people list from search
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/search/byname"
    And I fill in the following:
      | app_people_search[name] | Unknown |
    And press "submit"
    Then I should not see "CSV version"
    When I fill in the following:
      | app_people_search[name] | e |
    And press "submit"
    Then I should not see "CSV version"

  Scenario: As a superuser, test that i can download people list from search
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/search/byname"
    And I fill in the following:
      | app_people_search[name] | Unknown |
    And press "submit"
    Then I should not see "CSV version"
    When I fill in the following:
      | app_people_search[name] | e |
    And press "submit"
    Then I should see "CSV version"
    When I follow "CSV version"
    Then the response status code should be 200
    Then I should see response headers "content-type" with "text/csv; charset=utf-8"
    Then I should see response headers "content-disposition" with 'inline; filename=data.csv'

  Scenario: As a superuser, test that i can see a people disabled but not hidden
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/search/byname"
    And I fill in the following:
      | app_people_search[name] | pas |
    And press "submit"
    Then I should see "jean-pas-encore-la@tld.fr"
    Then I should see "INACTIVE ARRIVING SOON"

  Scenario: As a superuser, test that i can see a people disabled but not hidden
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/search/byname"
    And I fill in the following:
      | app_people_search[name] | partie |
    And press "submit"
    Then I should see "jean-partie-depuis-peu@tld.fr"
    Then I should see "INACTIVE, LEFT THE COMPANY"

  Scenario: As a GTCD user, test that i'm allowed to see download directory and complete the form
    Given I authenticate as "user-gtcd@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/search/byname"
    And the response status code should be 200
    And I should see "Download directory"
    When I follow "Download directory"
    Then the response status code should be 200
    And I should be on "/directory/people/download"
    Then press "directory_people_download_filter[submit]"
    Then the response status code should be 200
    Then I should see response headers "content-type" with "text/csv; charset=utf-8"

  Scenario: As a basic user, i can see team members
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/12/team-members"
    Then I should see "hr user"
    Then I should see "partie-depuis-peu jean"
    Then I should see "INACTIVE, LEFT THE COMPANY"
    Then I should see "pas-encore-la jean"
    Then I should see "INACTIVE ARRIVING SOON"

  Scenario: As a supervisor, i can manage acls of my team members
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/72/acls/delegate"
    Then the response status code should be 200
    And I should be on "/directory/people/72/acls/delegate"

  Scenario: As a user basic, i can't manage acls of someone not in my team
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/16/acls/delegate"
    Then the response status code should be 200
    Then I should not be on "/directory/people/16/acls/delegate"
    And I should see "You do not have permissions"

  Scenario: As a super user, i can see mentoring page of a people
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/12/mentoring"
    Then I should see "basic user"
    And I should see "hr user"

  Scenario: As a superuser, test that i can set a user password
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/4/define-password"
    Then the response status code should be 200
    And I should see "WILLIAMS Ellie"
    When I fill in the following:
      | app_account_password[password][first]  | newpassw0rd15cha |
      | app_account_password[password][second] | newpassw0rd15cha |
    And press "submit"
    Then the response status code should be 200

  Scenario: As a intranet admin, that i can modify a people located on the same location
    Given I authenticate as "user-ia@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/11/show"
    Then the response status code should be 200
    And I should see "Edit"
    And I should not see "Set Password"
    When I go to "/directory/people/11/edit"
    Then the response status code should be 200

  Scenario: As a intranet admin, that i cannot modify a people located on another location
    Given I authenticate as "user-ia@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/36/show"
    Then the response status code should be 200
    And I should not see "Edit"
    And I should not see "Set Password"

  Scenario: As a supervisor, test that i can update one of my team members
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/70/show"
    Then the response status code should be 200
    And I should see "Edit"
    And I should see "Set Password"
    When I go to "/directory/people/70/edit"
    Then I should see an "input[id='people_lastname'][disabled='disabled']" element
    And I select "en" from "people[locale]"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/directory/people/70/show"

  Scenario: Test that people can be searched by legacy ID
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/search/byid"
    Then the response status code should be 200
    When I fill in the following:
      | people_by_legacy_id[id]  | 2368 |
    And press "submit-legacy-id"
    Then the response status code should be 200
    And I should be on "/directory/people/21/show"

  Scenario: Test that people can be searched by ERP ID
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/search/byid"
    Then the response status code should be 200
    When I fill in the following:
      | people_by_erp_id[id]  | 203 |
    And press "submit-erp-id"
    Then the response status code should be 200
    And I should be on "/directory/people/13/show"

  Scenario: Test that user-superuser@tld.fr can access a user's activity report view
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/30/activity"
    Then the response status code should be 200
    And I should be on "/directory/people/30/activity"

  Scenario: Test that user-basic@tld.fr cannot access a user's activity report view
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/30/activity"
    Then the response status code should be 200
    Then I should not be on "/directory/people/30/activity"
    And I should see "You do not have permissions"

  Scenario: As a user basic, test i can access to map page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/map"
    Then the response status code should be 200
    And I should be on "/directory/people/map"
    And I should see "People closest airport"

  Scenario: As a basic user I should see filtered map page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/map"
    Then the response status code should be 200
    And I select "/positions/10" from "position"
    And I select "/business_units/10" from "businessUnit"
    And I select "/departments/2" from "department"
    And I select "/regions/3" from "region[]"
    And I select "/divisions/4" from "division[]"
    And I press "FILTER"
    And I should be on "/directory/people/map?position=%2Fpositions%2F10&businessUnit=%2Fbusiness_units%2F10&department=%2Fdepartments%2F1&region[]=%2Fregions%2F7&division[]=%2Fdivisions%2F4"
    And I should see "People closest airport"

  Scenario: As a basic user, I should see third party app page with only access list tab
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/30/third_party_apps"
    Then the response status code should be 200
    Then the response should contain "#accessList"
    Then the response should not contain "#updateTasksList"

  Scenario: As a super user, I should see third party app page with access list and update tasks tabs
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/30/third_party_apps"
    Then the response status code should be 200
    Then the response should contain "#accessList"
    Then the response should contain "#updateTasksList"

  Scenario: As a basic user, i don't see the 'Include hidden users' box on search people page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/search/byname"
    Then I should not see "Include hidden users"

  Scenario: As a superuser, i see the 'Include hidden users' box on search people page
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/search/byname"
    Then I should see "Include hidden users"

  @javascript
  Scenario: As a superuser, test that i can create a people
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/add"
    When I fill in the following:
      | people[lastname]     | LUCK               |
      | people[firstname]    | Skywalker          |
      | people[jobTitle]     | Gladiator          |
    And I select "/premises/1" from "people[premise]"
    Then I click on element ID "people_coefficient"
    When I fill in the following:
      | people[coefficient]  | 80                 |
      | people[enableAt]     | 11/21/2056         |
    And I select "/business_units/1" from "people[businessUnit]"
    And I select "/business_units/1" from "people[legalEntity]"
    And I select "/positions/1" from "people[position]"
    And I select "/departments/2" from "people[department]"
    And I hover "#people_enableAt"
    And I select "/people/11" from "people[supervisor]"
    And I select "en" from "people[locale]"
    And I select "/contract_types/1" from "people[contractType]"
    And press "submit"
    Then I wait until I see "The people have been created"
    And I should be on the exact url "/calendar/calendar.php?m%5B0%5D=seq&m%5B1%5D=new&m%5B2%5D=hr.user.add&id=2557"

  @javascript
  Scenario: As a superuser, test that i need to confirm to add an homonymous
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/add"
    When I fill in the following:
      | people[lastname]     | LUCK               |
      | people[firstname]    | Skywalker          |
      | people[jobTitle]     | Gladiator          |
    And I select "/premises/1" from "people[premise]"
    Then I click on element ID "people_coefficient"
    When I fill in the following:
      | people[coefficient]  | 80                 |
      | people[enableAt]     | 11/21/2056         |
    And I select "/business_units/1" from "people[businessUnit]"
    And I select "/business_units/1" from "people[legalEntity]"
    And I select "/positions/1" from "people[position]"
    And I select "/departments/2" from "people[department]"
    And I hover "#people_enableAt"
    And I select "/people/11" from "people[supervisor]"
    And I select "en" from "people[locale]"
    And I select "/contract_types/1" from "people[contractType]"
    And press "submit"
    Then I wait 3 seconds
    And I should be on "/directory/people/add"
    And I hover "#people_validHomonymousUser"
    And I wait until I see "Confirm homonymous"
    And click on the 1st "div.checkbox label" element
    And press "submit"
    Then I wait until I see "The people have been created"
    And I should be on the exact url "/calendar/calendar.php?m%5B0%5D=seq&m%5B1%5D=new&m%5B2%5D=hr.user.add&id=2558"

  Scenario: As a MIS Manager, I should see AI log history button
    Given I authenticate as "user-mism@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/30/show"
    Then the response status code should be 200
    And I should see an "a[href$='/directory/people/30/ai-history']" element
    Then I go to "/directory/people/30/ai-history"
    Then the response status code should be 200
    And I should be on "/directory/people/30/ai-history"
