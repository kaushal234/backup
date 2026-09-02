Feature: Test Extranet users

  Scenario: Get a token - right password
    Given I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/token" with parameters:
      | key      | value              |
      | username | julien.lepers@tld.com   |
      | password | P@ssw0rd15chars          |
      | portal   | extranet              |
    Then the response status code should be 200

  Scenario: Get a token - wrong password
    Given I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/token" with parameters:
      | key      | value                  |
      | username | julien.lepers@tld.com  |
      | password | wrong                  |
      | portal   | extranet               |
    Then the response status code should be 401

  Scenario: Request all extranet users without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_users"
    Then the response status code should be 401

  Scenario: Request a single extranet user without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_users/251"
    Then the response status code should be 401

  Scenario: Request all extranet users
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_users"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user/schemas/extranet_users.json"
    And less than 10 database queries must have been executed

  Scenario: Request all extranet users as xlsx
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/sales/extranet_users?columns=id,username,lastname,firstname,email,extranetUserProfile.department,phones,extranetUserProfile.customer.name,extranetUserAcls,disabled"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Username | Lastname | Firstname | Email | Extranet User Profile.department | Phones | Extranet User Profile.customer.name | Extranet User Acls | Disabled |

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\ExtranetUser" is exposed on the API
    Then the filter "extranetUserAcls.crt" should be available and its type should be "string"
    And the filter "order[id]" should be available and its type should be "string"
    And the filter "order[lastname]" should be available and its type should be "string"
    And the filter "order[firstname]" should be available and its type should be "string"
    And the filter "order[email]" should be available and its type should be "string"
    And the filter "order[extranetUserProfile.customer.name]" should be available and its type should be "string"
    And the filter "email" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "extranetUserProfile.customer" should be available and its type should be "string"
    And the filter "extranetUserProfile.customer.name" should be available and its type should be "string"
    And the filter "extranetUserProfile.erpLocation.name" should be available and its type should be "string"
    And the filter "extranetUserProfile.erpLocation" should be available and its type should be "string"
    And the filter "extranetUserProfile.airport" should be available and its type should be "string"
    And the filter "extranetUserAcls.crt" should be available and its type should be "string"
    And the filter "extranetUserAcls.crt.erpLocation" should be available and its type should be "string"
    And the filter "extranetUserAcls.crt.partsLocation" should be available and its type should be "string"
    And the filter "extranetUserAcls.crt.serviceLocation" should be available and its type should be "string"
    And the filter "extranetUserAcls.crt.customer" should be available and its type should be "string"
    And the filter "extranetUserAcls.extranetUserGroup" should be available and its type should be "string"
    And the filter "extranetUserAcls.extranetUserGroup.name" should be available and its type should be "string"
    And the filter "hidden" should be available and its type should be "bool"
    And the filter "disabled" should be available and its type should be "bool"
    And the filter "extranetUserProfile.archived" should be available and its type should be "bool"
    And the filter "extranetUserAcls.extranetUserGroup.name" should be available and its type should be "string"
    And the filter "relatedToEquipmentRecord" should be available and its type should be "string"
    And the filter "relatedToCustomer" should be available and its type should be "string"

  Scenario: Request all extranet users by a XU should give a filtered collection
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_users"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user/schemas/extranet_users.json"
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0].username" should be equal to "julien.lepers@tld.com"

  Scenario: Get all contacts for entire customer hierarchy
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    #/sales/customers/33 is child of /sales/customers/1 so we should all contacts for both
    When I send a "GET" request to "/sales/extranet_users?customer_hierarchy=/sales/customers/33&q=@extra.net"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user/schemas/extranet_users.json"
    And the JSON node "hydra:totalItems" should be equal to 5
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_users?customer_hierarchy=/sales/customers/1&q=@extra.net"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user/schemas/extranet_users.json"
    And the JSON node "hydra:totalItems" should be equal to 5

  Scenario: Request a single extranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_users/249"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user/schemas/extranet_user.json"

  Scenario: basic users can't update an extranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_users/250" with the body "tests/fixtures/json/sales/extranet_user/dummies/put.json"
    Then the response status code should be 403

  Scenario: Search extranet users on lastname
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_users?q=Murray"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user/schemas/extranet_users.json"

  Scenario: Search extranet users on firstname
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_users?q=Roberta"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user/schemas/extranet_users.json"

  Scenario: Search extranet users on customer name
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_users?q=AIR DE RIEN"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user/schemas/extranet_users.json"

  Scenario: Display extranet users list
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_users?normalization_groups_override[]=extranet_user_list"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user/schemas/extranet_user_list.json"

  Scenario: Basic user can't create an extranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/extranet_users" with the body "tests/fixtures/json/sales/extranet_user/dummies/post.json"
    Then the response status code should be 403

  Scenario: As superuser I can send confirmation email to XU
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_users/200/confirmation_mail" with body:
    """
    {
      "subject": "Kikou les Loulous !",
      "ccs": ["/people/11"],
      "bccs": ["/people/29"]
    }
    """
    Then the response status code should be 204
    And an email should have been sent asynchronously with subject "Kikou les Loulous !"
    And this asynchronous email should be sent from "noreply@tld-gse.com"
    And this asynchronous email should be sent only to "julien.lepers@tld.com"
    And this asynchronous email should be sent as cc only to "user-superuser@tld.fr, user-basic@tld.fr"
    And this asynchronous email should be sent as bcc only to "user-qam@tld.fr"
    And this asynchronous email should contain "Your application for a TLD Extranet Site account has been authorized."
    And this asynchronous email should contain "https://extranet.tld-gse.com"

  Scenario: Sequence is generated when editing an XU to give him extranet access
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/extranet_users/208/extranet_request"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user/schemas/extranet_user.json"
    And an email should have been sent asynchronously with subject matching pattern "/SEQ #\S+: Extranet User application approvals/"
    And this asynchronous email should be sent only to "user-sa@tld.fr"

  Scenario: Sequence is not generated when sequence for access already exists for this user
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/extranet_users/208/extranet_request"
    Then the response status code should be 409

  Scenario: Superuser can create an extranet user
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/extranet_users" with the body "tests/fixtures/json/sales/extranet_user/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user/schemas/extranet_user.json"
    And no email should have been sent asynchronously
    And the JSON node "username" should be equal to "test-icule@tld-gse.com"
    And the JSON node "extranetUserProfile.@id" should be equal to "/sales/extranet_user_profiles/101"

  Scenario: Superuser can create an extranet user with existing people address
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/extranet_users" with the body "tests/fixtures/json/sales/extranet_user/dummies/post_with_existing_people_email.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user/schemas/extranet_user.json"
    And the JSON node "username" should be equal to "user-basic@tld.fr"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/people/11?normalization_groups[]=linked_account"
    Then the response status code should be 200
    And the JSON node "extranetUserLinked.email" should be equal to "user-basic@tld.fr"

  Scenario: Superuser can update an extranet user
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_users/254" with the body "tests/fixtures/json/sales/extranet_user/dummies/put_username.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user/schemas/extranet_user.json"
    And the JSON node "email" should be equal to "toutouyoutou@tld.fr"

  Scenario: All XU ACL are removed if the customer is changed
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_users/207?normalization_groups[]=extranet_user_acls"
    Then the response status code should be 200
    And the JSON node "extranetUserAcls" should have 2 elements
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_users/207" with body:
    """
    {
      "extranetUserProfile": {
        "@id": "/sales/extranet_user_profiles/1",
        "customer": "/sales/customers/2"
      }
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user/schemas/extranet_user.json"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_users/207?normalization_groups[]=extranet_user_acls"
    Then the response status code should be 200
    And the JSON node "extranetUserAcls" should have 0 element

  Scenario: Superuser can't update an extranet user with an existing extranet email
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_users/255" with the body "tests/fixtures/json/sales/extranet_user/dummies/put_username.json"
    Then the response status code should be 422

  Scenario: As an extranet user I can't edit another extranet user account
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_users/254" with body:
    """
    {
      "firstname": "Jul"
    }
    """
    Then the response status code should be 403

  Scenario: As an extranet user I can edit my own account
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_users/200" with body:
    """
    {
      "firstname": "Jul",
      "extranetUserProfile": {
        "@id": "/sales/extranet_user_profiles/94",
        "language": "FR",
        "country": "/countries/10"
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "extranetUserProfile.language" should be equal to "FR"

  Scenario: When the email is edited, the username changes to
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_users/254" with body:
    """
    {
      "email": "kikou@tld.fr"
    }
    """
    Then the response status code should be 200
    And the JSON node "username" should be equal to "kikou@tld.fr"
    And the JSON node "email" should be equal to "kikou@tld.fr"

  Scenario: As an Extranet User, I cannot edit my email
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_users/200" with body:
    """
    {
      "email": "change@mytrala.la"
    }
    """
    Then the response status code should be 200
    And the JSON node "username" should be equal to "julien.lepers@tld.com"
    And the JSON node "email" should be equal to "julien.lepers@tld.com"

  Scenario: When the contact is set to archived, it removes all ACL from contact
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_users/211/archive_account"
    Then the response status code should be 200
    And the JSON node "disabled" should be true
    And the JSON node "hidden" should be true
    And the JSON node "extranetUserProfile.archived" should be true
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_users/211?normalization_groups[]=extranet_user_acls"
    Then the response status code should be 200
    And the JSON node "extranetUserAcls" should have 0 element

  Scenario: The counter and last login are updated when an extranet user authenticate
    Given I authenticate as the extranet user "dounot.use@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_users/201"
    Then the response status code should be 200
    And the JSON node "extranetUserProfile.counter" should be equal to 1
    And the JSON node "lastLogin" should be newer than 1 minute ago

  Scenario: Extranet user can see his account
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I go to "/me"
    Then the response status code should be 200
    And the JSON node "username" should be equal to the string "julien.lepers@tld.com"

  Scenario: Extranet user can only see his account
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_users/202"
    Then the response status code should be 403

  Scenario: As an extranet user I can't update my password without a valid token
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_users/200" with body:
    """
    {
      "password": "FrançoisLeFrançais123"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Token is not valid, your password has not been updated"


  Scenario: As superuser I can disable extranet account
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_users/235/disable_account"
    Then the response status code should be 200
    And the JSON node "disabled" should be true
    And the JSON node "hidden" should be false

  Scenario: As an extranet user I can't update my password with a valid token and an invalid password
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_users/200" with body:
    """
    {
      "password": "lol",
      "token": "JohnRonaldReuelToken"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "clearPassword"

  Scenario: As an extranet user I can update my password with a valid token and a valid password
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_users/200" with body:
    """
    {
      "password": "AnotherP@ssw0rdBecauseICantUseTheS@me",
      "token": "JohnRonaldReuelToken"
    }
    """
    Then the response status code should be 200
    When I send a "POST" request to "/token" with parameters:
      | key      | value                                  |
      | username | julien.lepers@tld.com                  |
      | password | AnotherP@ssw0rdBecauseICantUseTheS@me  |
      | portal   | extranet                               |
    Then the response status code should be 200

  Scenario: As an extranet user I can t update my password with a valid token and same password
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_users/200" with body:
    """
    {
      "password": "AnotherP@ssw0rdBecauseICantUseTheS@me",
      "token": ""
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "clearPassword"
    And the JSON node "hydra:description" should be equal to the string "clearPassword: Password must be different than the last 3 previous ones."

  Scenario: As an extranet user I can request an email to update my password
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/extranet_users/200/update_password"
    Then the response status code should be 204
    And an email should have been sent asynchronously with subject "Update your TLD extranet password"
    And this asynchronous email should be sent only to "julien.lepers@tld.com"

  Scenario: As an extranet user I can't request an email to update someone else's password
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/extranet_users/201/update_password"
    Then the response status code should be 403
    And no email should have been sent asynchronously

  Scenario: As sales admin I can remove extranet user and if it's linked to a people, the link value is set to null
    Given I authenticate as the intranet user "user-sam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people/12?normalization_groups[]=linked_account"
    Then the response status code should be 200
    And the JSON node "extranetUserLinked.@id" should be equal to the string "/sales/extranet_users/207"
    Given I authenticate as the intranet user "user-sam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/extranet_users/207"
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-sam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people/12?normalization_groups[]=linked_account"
    Then the response status code should be 200
    And the JSON node "extranetUserLinked" should be null

  Scenario: As basic user I can get all contact associated to a campaign
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contact_campaign/94/contacts"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user/schemas/contact_campaign.json"
    
  Scenario: A basic user cannot create extranet user with CRT and group
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/extranet_users/with_crt_and_group" with the body "tests/fixtures/json/sales/extranet_ser_with_crt_and_group/dummies/post.json"
    Then the response status code should be 403

  Scenario: A superuser can create extranet user with CRT and group
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/extranet_users/with_crt_and_group" with the body "tests/fixtures/json/sales/extranet_ser_with_crt_and_group/dummies/post.json"
    Then the response status code should be 201
    And the JSON node "extranetUserProfile.customer.name" should be equal to "customer_for_er"
    And the JSON node "extranetUserProfile.companyName" should be equal to "customer_for_er"

  Scenario: A superuser cannot create extranet user with CRT and group with a email that already exists
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/extranet_users/with_crt_and_group" with the body "tests/fixtures/json/sales/extranet_ser_with_crt_and_group/dummies/post.json"
    Then the response status code should be 400
    And the JSON node "detail" should be equal to "A User already exists with this email address."
