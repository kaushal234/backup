Feature: Sales / contacts

  Scenario: As an anonymous user, test that i'm not allowed to see contact pages
    When I go to "/sales/contacts"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/sales/contacts/251/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see contact pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/contacts"
    Then the response status code should be 200
    And The module should be "XU"
    And I should see "Last 10 eContacts Created"
    When I go to "/sales/contacts/255/show"
    Then the response status code should be 200
    And I should see "Contact Status"
    And I should see "Extranet Access"
    And I should see "logs"

  Scenario: As a superuser, test that i'm allowed to see contacts pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/contacts"
    Then I should see 10 "table tbody tr" elements
    And the "table tbody tr:nth-child(2) td:nth-child(2)" element should contain a text

  Scenario: As a superuser, test that i can create a contact
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/contacts/add"
    Then the response status code should be 200
    When I fill in the following:
      | xu_add_form[lastname]                               | Pascal                            |
      | xu_add_form[firstname]                              | Le Grand Frere                    |
      | xu_add_form[email]                                  | pascal.legrandfrere@moumoutte.com |
      | xu_add_form[phones][0][number]                      | +33601020304                      |
      | xu_add_form[extranetUserProfile][department]        | Sales                             |
      | xu_add_form[extranetUserProfile][jobTitle]          | Sales Manager                     |
      | xu_add_form[extranetUserProfile][division]          | Ligue 1                           |
    And I select "/airports/62" from "xu_add_form[extranetUserProfile][airport]"
    And I select "/countries/10" from "xu_add_form[extranetUserProfile][country]"
    And I select "/sales/customers/1" from "xu_add_form[extranetUserProfile][customer]"
    And I select "reception" from "xu_add_form[phones][0][type]"
    And press "submit"
    Then the response status code should be 200
    And I should see "Pascal"
    And I should see "ACTIVE"
    And I should see "DISABLED"

  Scenario: As a superuser, test that i can open a sequence for extranet access
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/contacts/208/show"
    Then the response status code should be 200
    And I should see an "a[href$='/sales/contacts/208/extranet_request']" element
    When I go to "/sales/contacts/208/extranet_request"
    Then the response status code should be 200
    And I should be on "/sales/contacts/208/show"

  Scenario: As a basic user, test that i can't open a sequence to create contacts
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/contacts"
    Then the response status code should be 200
    And I should not see "New eContact"

  Scenario: As a superuser, test that i can edit an contact
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/contacts/253/edit"
    Then the response status code should be 200
    When I fill in the following:
      | extranet_user_form[lastname] | Maximus |
    And I select "/sales/customers/1" from "extranet_user_form[extranetUserProfile][customer]"
    And I select "/countries/10" from "extranet_user_form[extranetUserProfile][country]"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/sales/contacts/253/show"
    And I should see "Maximus"

  Scenario: As a basic user, test that i can't edit an contact
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/contacts/253/show"
    Then the response status code should be 200
    And I should not see "Edit"

  Scenario: As a basic user, test that i can see roles of an contact
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/contacts/251/crt_roles"
    Then the response status code should be 200
    And I should see "CRT and Roles"
    And I should not see "Add Role"

  Scenario: As a super user, test that i can add roles of an contact
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/contacts/251/crt_roles/add"
    Then the response status code should be 200
    And I should see "CRT and Roles"
    And I should see "Add Role"
    And I select "/sales/customers/1" from "xu_add_roles[customer]"
    And press "Submit"
    And I select "/sales/customer_relationship_teams/1" from "xu_add_roles[crt]"
    And press "Submit"
    And I select "/sales/extranet_user_groups/1" from "xu_add_roles[role][]"
    And press "Submit"
    Then the response status code should be 200
    And I should see "role_ST"

  Scenario: As a super user, test that i can delete roles of an contact
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/contacts/crt_roles/15/delete"
    Then the response status code should be 200
    And I should see "CRT and Roles"
    And I should not see "role_ST"

  Scenario: As a basic user, test that I can filter contacts
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/contacts"
    Then the response status code should be 200
    And I should see "Last 10 eContacts Created"
    When I go to "/sales/contacts?filter_extranet_user[customer][value]=/sales/customers/1"
    Then the response status code should be 200
    And I should see "eContact"

  Scenario: As a superuser, test that i can send confirmation email for contact
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/contacts/251/show"
    Then the response status code should be 200
    And I should see "Confirmation Email"
    When I go to "/sales/contacts/251/confirmation_mail"
    Then the response status code should be 200
    And I should see "https://extranet.tld-gse.com"
    When I fill in "xu_email[subject]" with "Salut les Musclés !"
    And press "Submit"
    Then the response status code should be 200
    And I should be on "/sales/contacts/251/show"

  Scenario: As a superuser, test that i can disable extranet access
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/contacts/207/show"
    Then the response status code should be 200
    And I should see an "a[href$='/sales/contacts/207/disable-account']" element
    When I go to "/sales/contacts/207/disable-account"
    Then the response status code should be 200
    And I should be on "/sales/contacts/207/show"
    And I should not see an "a[href$='/sales/contacts/207/disable-account']" element
    And I should see "DISABLED"

  Scenario: As a superuser, test that i can archive contact
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/contacts/211/show"
    Then the response status code should be 200
    And I should see an "a[href$='/sales/contacts/211/archive-account']" element
    When I go to "/sales/contacts/211/archive-account"
    Then the response status code should be 200
    And I should be on "/sales/contacts/211/show"
    And I should not see an "a[href$='/sales/contacts/211/archive-account']" element
    And I should see "INACTIVE"

  Scenario: As a superuser, test that i can delete contact
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/contacts/252/delete"
    Then the response status code should be 200
    And I should be on "/sales/contacts"

  Scenario: As a superuser, test that i can see contact roles
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/contacts/roles"
    Then the response status code should be 200
    And I should see "Roles"
    And I should see an "a[href$='/sales/contacts/roles/1/members']" element

  Scenario: As a superuser, test that i can see contact roles members
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/contacts/roles/1/members"
    Then the response status code should be 200

  Scenario: As a superuser, test that i can download contact vcard
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/contacts/253/vcard"
    Then the response status code should be 200
    Then I should see response headers "content-type" with "text/x-vcard;charset=UTF-8"
