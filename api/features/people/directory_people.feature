Feature: Test people API

  Scenario: Request all people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    Given I add "Accept" header equal to "application/ld+json"
    And I go to "/people"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_peoples.json"
    And the JSON node "hydra:member[0].contractType" should not exist
    And the JSON node "hydra:member[0].mentees" should not exist
    And the JSON node "hydra:member[0].mentor" should not exist
    And the JSON node "hydra:member[0].coefficient" should not exist

  Scenario: Request all people for light list
    Given I authenticate as the intranet user "user-basic@tld.fr"
    Given I add "Accept" header equal to "application/ld+json"
    And I go to "/people?normalization_groups_override[]=people_list"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_peoples_list.json"
    And less than 13 database queries must have been executed

  Scenario: Request a single people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I go to "/people/5"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people.json"
    And the JSON node "contractType" should not exist
    And the JSON node "mentees" should not exist
    And the JSON node "mentor" should not exist
    And the JSON node "coefficient" should not exist
    And the JSON node "alternateEmail" should not exist

  Scenario: Request a single people as an authorized app should be granted
    Given I authenticate as the authorized application "PIO"
    And I add "Accept" header equal to "application/ld+json"
    And I go to "/people/5"
    Then the response status code should be 200

  Scenario: Request a single people as HR should display contract type
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I go to "/people/55"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people.json"
    And the JSON node "contractType" should exist
    And the JSON node "mentees" should exist
    And the JSON node "mentor" should exist
    And the JSON node "coefficient" should exist
    And the JSON node "alternateEmail" should exist

  Scenario: Request my account
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I go to "/me"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people_me.json"
    And the JSON node "username" should be equal to the string "user-basic@tld.fr"
    And the JSON node "contractType" should not exist
    And the JSON node "coefficient" should not exist
    And the JSON node "alternateEmail" should exist
    And less than 12 database queries must have been executed

  Scenario: Request a people that does not exist
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I go to "/people/9999999"
    Then the response status code should be 404

  Scenario: Request a random people should not be possible for extranet users
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/people/random"
    Then the response status code should be 403

  Scenario: Request a random people should not be possible for vendor users
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/people/random"
    Then the response status code should be 403

  Scenario: Request a random people should be possible for basic users
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/people/random?disabled=0"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people.json"

  Scenario: Create a people without Mentor and a position which requires it should fail
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/people" with body:
    """
    {
      "lastname": "MENTEE",
      "firstname": "user",
      "gender": "Mr",
      "nickname": null,
      "premise": "\/premises\/1",
      "closestAirport": null,
      "alternateEmail": null,
      "jobTitle": "Mentee",
      "phones": [],
      "address": {
        "street1": null,
        "street2": null,
        "postalCode": null,
        "town": null,
        "city": null,
        "state": null,
        "country": null
      },
      "businessUnit": "\/business_units\/23",
      "legalEntity": "\/business_units\/23",
      "position": "\/positions\/17",
      "department": "\/departments\/2",
      "supervisor": "\/people\/36",
      "mentor": null,
      "locale": "en",
      "contractType": "\/contract_types\/1",
      "coefficient": 100,
      "enableAt": "2030-01-01T00:00:00-0500"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should contain "mentor"
    And the JSON node "violations[0].message" should contain "A mentor is required for the selected position."

  Scenario: Edit a people without Mentor and a position which requires it should fail
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/16" with body:
    """
    {
      "position": "\/positions\/17"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should contain "mentor"
    And the JSON node "violations[0].message" should contain "A mentor is required for the selected position."

  Scenario: Create a people - wrong password
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/people" with the body "tests/fixtures/json/directory_people/dummies/post_wrong_password.json"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "clearPassword"
    And the JSON node "violations" should have 2 elements
    And the JSON node "violations[0].propertyPath" should be equal to "clearPassword"
    And the JSON node "violations[0].message" should contain "This value is too short. It should have 15 characters or more."
    And the JSON node "violations[1].propertyPath" should be equal to "clearPassword"
    And the JSON node "violations[1].message" should contain "Password must check 2 of these constraints: include both upper and lower case letters, include at least one number, include at least one special character."

  Scenario: Create a people - no closest airport - no premise with people with FEATURE_PEOPLE_UPDATE
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/people" with the body "tests/fixtures/json/directory_people/dummies/post_no_closest_airport_no_premise.json"
    Then the response status code should be 400
    And the JSON node "hydra:description" should contain "Premise is mandatory: Please select a premise for DOE John."

  Scenario: Create a people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/people" with the body "tests/fixtures/json/directory_people/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people.json"
    And the JSON node "root->email" should contain "john.doe@tld-europe.com"
    And the JSON node "root->username" should contain "john.doe@tld-europe.com"
    And the JSON node "root->lastname" should contain "DOE"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/people/323?normalization_groups[]=group_member"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people_group_name.json"
    And the JSON node "contractType.name" should be equal to the string "Sous-Fifre"
    And the JSON node "coefficient" should be equal to the number 98
    And the JSON node "premise.name" should be equal to the string "Andalouza"
    And the JSON node "acls" should have 3 elements
    And the JSON node "acls[0].group.name" should be equal to the string "SUPERUSER"

  Scenario: Create a people with same email than extranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/people" with the body "tests/fixtures/json/directory_people/dummies/post_same_email_xu.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people.json"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/people/324?normalization_groups[]=linked_account"
    Then the response status code should be 200
    And the JSON node "email" should be equal to "julien.lepers@tld.com"
    And the JSON node "extranetUserLinked.id" should be equal to 200

  Scenario: Create a people with same email than vendor user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/people" with the body "tests/fixtures/json/directory_people/dummies/post_same_email_vendor_user.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people.json"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/people/325?normalization_groups[]=linked_account"
    Then the response status code should be 200
    And the JSON node "email" should be equal to "vendor.user@vendor.fr"
    And the JSON node "vendorUserLinked.id" should be equal to 300

  Scenario: Create a people without specifying email
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/people" with the body "tests/fixtures/json/directory_people/dummies/post_no_email.json"
    Then the response status code should be 201
    And the JSON node "root->email" should contain "mark.doe@tld-gse.com"
    And the JSON node "root->username" should contain "mark.doe@tld-gse.com"

  Scenario: Create a people without specifying email - on another BU (TLD EUR)
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/people" with the body "tests/fixtures/json/directory_people/dummies/post_no_email_another_bu.json"
    Then the response status code should be 201
    And the JSON node "root->email" should contain "juan.silva@tld-europe.com"

  Scenario: Create a people without specifying email - with a special character
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/people" with the body "tests/fixtures/json/directory_people/dummies/post_no_email_spec_char.json"
    Then the response status code should be 201
    And the JSON node "root->email" should contain "juan-marco.dasilva@tld-america.com"

  Scenario: Get a token - wrong passsword
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/token" with parameters:
      | key      | value              |
      | username | john.doe@tld-europe.com   |
      | password | wrong pssword      |
      | portal   | intranet           |
    Then the response status code should be 401

  Scenario: Get a token - right passsword
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/token" with parameters:
      | key      | value              |
      | username | john.doe@tld-europe.com   |
      | password | M@ L0nger s3cr3T          |
      | portal   | intranet           |
    Then the response status code should be 200

  Scenario: Update my personal informations - permitted as basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/11" with body:
    """
    {
      "firstname": "Achille",
      "lastname": "Talon",
      "nickname": "Nick",
      "alternateEmail": "thisis@lterna.te",
      "contractType": "/contract_types/2",
      "coefficient": 33,
      "gender": "Mr",
      "legalEntity": "/business_units/3"
    }
    """
    Then the response status code should be 200
    And the JSON node "firstname" should be equal to the string "user"
    And the JSON node "lastname" should be equal to the string "BASIC"
    And the JSON node "nickname" should be equal to the string "Nick"
    And a message of class "App\Agile\Message\UpdateUserMessage" should have been sent in the bus
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/people/11"
    Then the response status code should be 200
    And the JSON node "contractType.@id" should be equal to the string "/contract_types/1"
    And the JSON node "coefficient" should be equal to the number 100
    And the JSON node "gender" should be equal to the string "Mr"
    And the JSON node "legalEntity.@id" should be equal to the string "/business_units/3"

  Scenario: Update my personal information - permitted as super user
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/12" with body:
    """
    {
      "firstname": "Jean Michel",
      "lastname": "Apeupres",
      "nickname": "Kad",
      "gender": "Mrs",
      "legalEntity": "/business_units/3"
    }
    """
    Then the response status code should be 200
    And the JSON node "firstname" should be equal to the string "Jean Michel"
    And the JSON node "lastname" should be equal to the string "APEUPRES"
    And the JSON node "nickname" should be equal to the string "Kad"
    And the JSON node "gender" should be equal to the string "Mrs"
    And the JSON node "legalEntity.@id" should be equal to the string "/business_units/3"
    # Restore the super user to his defaults values
    Then I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/12" with body:
    """
    {
      "firstname": "user",
      "lastname": "superuser",
      "nickname": "Kad",
      "gender": null
    }
    """
    Then the response status code should be 200
    And the JSON node "gender" should be null

  Scenario: Update my personal informations - permitted as hr user
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/13" with body:
    """
    {
      "firstname": "Barbara",
      "lastname": "Gould",
      "nickname": "Odile",
      "legalEntity": "/business_units/3"
    }
    """
    Then the response status code should be 200
    And the JSON node "firstname" should be equal to the string "Barbara"
    And the JSON node "lastname" should be equal to the string "GOULD"
    And the JSON node "nickname" should be equal to the string "Odile"
    And the JSON node "legalEntity.@id" should be equal to the string "/business_units/3"

  Scenario: Update another people informations - not permitted as basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/12" with body:
    """
    {
      "firstname": "Achille",
      "username" : "achilletalon@tld.fr",
      "email" : "achilletalon@tld.fr"
    }
    """
    Then the response status code should be 403

  Scenario: Update another people informations  - permitted as super user
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/11" with body:
    """
    {
      "firstname": "Achille",
      "lastname": "Talon",
      "username" : "achilletalon@tld.fr",
      "email" : "achilletalon@tld.fr",
      "legalEntity": "/business_units/7"
    }
    """
    Then the response status code should be 200
    And the JSON node "firstname" should be equal to the string "Achille"
    And the JSON node "lastname" should be equal to the string "TALON"
    And the JSON node "email" should be equal to the string "achilletalon@tld.fr"
    And the JSON node "username" should be equal to the string "achilletalon@tld.fr"
    And the JSON node "legalEntity.@id" should be equal to the string "/business_units/7"
    And a message of class "App\Agile\Message\UpdateUserMessage" should have been sent in the bus

  Scenario: Update another people informations  - permitted as supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/13" with body:
    """
    {
      "firstname": "Toto"
    }
    """
    Then the response status code should be 200
    And the JSON node "firstname" should be equal to the string "Toto"
    And a message of class "App\Agile\Message\UpdateUserMessage" should have been sent in the bus

  Scenario: Update another people information with no premise - not permitted as hr user with FEATURE_PEOPLE_UPDATE
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/2" with body:
    """
    {
      "firstname": "Peter",
      "lastname": "Mac Calloway",
      "username" : "user-basic@tld.fr",
      "email" : "user-basic@tld.fr",
      "contractType": "/contract_types/2",
      "coefficient": 99,
      "gender": "Mrs",
      "legalEntity": "business_units/24"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should contain "Premise is mandatory: Please select a premise for Mac Calloway Peter."


  Scenario: Update another people information  - permitted as hr user
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/11" with body:
    """
    {
      "firstname": "Peter",
      "lastname": "Mac Calloway",
      "username" : "user-basic@tld.fr",
      "email" : "user-basic@tld.fr",
      "contractType": "/contract_types/2",
      "coefficient": 99,
      "gender": "Mrs",
      "legalEntity": "business_units/24"
    }
    """
    Then the response status code should be 200
    And the JSON node "firstname" should be equal to the string "Peter"
    And the JSON node "lastname" should be equal to the string "MAC CALLOWAY"
    And the JSON node "email" should be equal to the string "user-basic@tld.fr"
    And the JSON node "username" should be equal to the string "user-basic@tld.fr"
    And the JSON node "contractType.@id" should be equal to the string "/contract_types/2"
    And the JSON node "coefficient" should be equal to the number 99
    And the JSON node "gender" should be equal to the string "Mrs"
    And the JSON node "legalEntity.@id" should be equal to the string "/business_units/24"

  Scenario: Update people information - permitted if ROLE_INTRANET_ADMIN and same location
    Given I authenticate as the intranet user "user-ia@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/11" with body:
    """
    {
      "nickname": "Will",
      "jobTitle": "Ramasseur de balle"
    }
    """
    Then the response status code should be 200
    And the JSON node "nickname" should be equal to the string "Will"
    And the JSON node "jobTitle" should be equal to the string "Ramasseur de balle"

  Scenario: Update people information - Not permitted if different BU
    Given I authenticate as the intranet user "user-ia@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/36" with body:
    """
    {
      "nickname": "Jean Michel Amoitié",
      "jobTitle": "Chanteur"
    }
    """
    Then the response status code should be 403

  Scenario: Update people information - permitted if supervisor and user without ACL_AUTH_INTRANET
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/acls?user=/people/70"
    Then the JSON node "hydra:member" should have 0 element
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/70" with body:
    """
    {
      "lastname": "Last",
      "erpIdentifier": "118218"
    }
    """
    Then the response status code should be 200
    And the JSON node "lastname" should be equal to the string "USER_WITHOUT_ACL_AUTH_INTRANET"
#    And the JSON node "erpIdentifier" should contain "118218"

  Scenario: Update a people
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/11" with the body "tests/fixtures/json/directory_people/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people.json"
    And the JSON node "firstname" should be equal to the string "new firstname"
    And the JSON node "lastname" should be equal to the string "NEW LASTNAME"
    And the JSON node "email" should be equal to the string "new.email@tld-gse.com"
    And the JSON node "username" should be equal to the string "new.email@tld-gse.com"
    And the JSON node "nickname" should be equal to the string "new nick"
    And the JSON node "jobTitle" should be equal to the string "new job"
    And the JSON node "erpLogin" should be equal to the string "newlogin"
#    And the JSON node "erpIdentifier" should be equal to the string "6969"
    And the JSON node "windowsLogin" should be equal to the string "windows95_4ever"
    And the JSON node "businessUnit.name" should be equal to the string "SAY_MY_NAME"
    And the JSON node "position.code" should be equal to the string "GCEO"
    And the JSON node "department.name" should be equal to the string "dpt_rastaman"
    And the JSON node "locale" should be equal to the string "zh"
    And the JSON node "supervisor.username" should be equal to the string "user-superuser@tld.fr"
    And the JSON node "premise.name" should be equal to the string "Andalouza"
    And the JSON node "gender" should be equal to the string "Mrs"
    #Restore the basic user to his defaults values
    Then I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/11" with body:
    """
    {
      "username": "user-basic@tld.fr",
      "email": "user-basic@tld.fr",
      "businessUnit": "/business_units/2"
    }
    """
    Then the response status code should be 200

  Scenario: Update a people with an invalid phone number
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/11" with the body "tests/fixtures/json/directory_people/dummies/put_invalid_phone.json"
    Then the response status code should be 422

  Scenario: Update a people with him as its own supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/11" with body:
    """
    {
      "supervisor": "/people/11"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "supervisor: Can not be your own supervisor"

  Scenario: Change the user password - invalid
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/17" with body:
    """
    {
      "username": "jane.doe@gmail.com",
      "email": "jane.doe@gmail.com",
      "password": " my bad secret"
    }
    """
    Then the response status code should be 422

  Scenario: Change the user password - valid
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/17" with body:
    """
    {
      "email": "jane.doe@gmail.com",
      "username": "jane.doe@gmail.com",
      "firstname": "Jane",
      "lastname": "Doe",
      "password": "M@ NEW L0NG s3cr3T"
    }
    """
    Then the response status code should be 200
    And a comment should have been inserted on resource "/people/17" with message "updated user password" by "user-superuser@tld.fr"

  Scenario: Can't get a token with the old password
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/token" with parameters:
      | key      | value              |
      | username | jane.doe@gmail.com |
      | password | M@ L0nger s3cr3T      |
      | portal   | intranet           |
    Then the response status code should be 401

  Scenario: Get a token with the new password for Jane Doe
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/token" with parameters:
      | key      | value              |
      | username | jane.doe@gmail.com |
      | password | M@ NEW L0NG s3cr3T      |
      | portal   | intranet           |
    Then the response status code should be 200

  Scenario: Change the user password as supervisor of people not granted ACL_AUTH_INTRANET
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/70" with body:
    """
    {
      "password": "mY Sh@meful secret"
    }
    """
    Then the response status code should be 200

  Scenario: Get a token with the new password for user-no-intranet@tld.fr
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/token" with parameters:
      | key      | value                   |
      | username | user-no-intranet@tld.fr |
      | password | mY Sh@meful secret      |
      | portal   | intranet                |
    Then the response status code should be 200

  Scenario: Auth on disabled users
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/17" with body:
    """
    {
      "disabled": true
    }
    """
    Then the response status code should be 200
    When I send a "POST" request to "/token" with parameters:
      | key      | value              |
      | username | jane.doe@gmail.com |
      | password | M@ NEW L0NG s3cr3T      |
      | portal   | intranet           |
    Then the response status code should be 401

  Scenario: Auth on non-disabled users
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/17" with body:
    """
    {
      "disabled": false
    }
    """
    Then the response status code should be 200
    When I send a "POST" request to "/token" with parameters:
      | key      | value              |
      | username | jane.doe@gmail.com |
      | password | M@ NEW L0NG s3cr3T      |
      | portal   | intranet           |
    Then the response status code should be 200

  Scenario: Regenerate password doesn't change password directly
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/reset_password" with body:
    """
    {
      "portal": "intranet",
      "email": "jane.doe@gmail.com"
    }
    """
    Then the response status code should be 204
    And an email should have been sent asynchronously with subject "Reset Password Confirmation"
    And this asynchronous email should contain an element matching selector "p.text-introduction" which value is "Hello Jane DOE!"
    And this asynchronous email should be sent from "noreply@tld-gse.com"
    And this asynchronous email should be sent to "jane.doe@gmail.com"
    And this asynchronous email body should contain a link to "https://www.tld-gse.com/en/private/reset-password-confirmation/"
    When I send a "POST" request to "/token" with parameters:
      | key      | value              |
      | username | jane.doe@gmail.com |
      | password | M@ NEW L0NG s3cr3T      |
      | portal   | intranet           |
    Then the response status code should be 200

  @resetFileTable
  Scenario: Change the user photo (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/people/17/photo" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
    And an update log should have been inserted on resource "/people/17" with a changeset on the property "files"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people/17"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people.json"
    And the JSON node photo should not be null

  Scenario: Change the user photo (gg_hr)
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/people/18/photo" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
    And an update log should have been inserted on resource "/people/18" with a changeset on the property "files"
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people/18"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people.json"
    And the JSON node photo should not be null

  Scenario: Change my photo
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/people/11/photo" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
    And an update log should have been inserted on resource "/people/11" with a changeset on the property "files"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people.json"
    And the JSON node photo should not be null

  Scenario: Change my photo with an invalid file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/people/11/photo" with file "file" "file.txt"
    Then the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string "This file is not a valid image."

  Scenario: Change the user photo with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/people/12/photo" with file "file" "image_1200x1200.jpg"
    And the response should be an error stating "Access Denied" with status code 403

  Scenario: Change the user photo with permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/people/70/photo" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 201

  Scenario: Delete user photo
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/people/18/photo/2"
    Then the response status code should be 204
    Then I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people/18/photo/6"
    Then the response status code should be 404

  Scenario: Delete user photo without authentication
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/people/17/photo/1"
    Then the response status code should be 401

  Scenario: Delete user photo with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/people/17/photo/1"
    Then the response status code should be 403

  Scenario: Delete my photo
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/people/11/photo/3"
    Then the response status code should be 204

  Scenario: Search people via simple search
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people?q=superuser"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_peoples.json"
    And the JSON node "hydra:totalItems" should be equal to "1"

  Scenario: Search people via simple search on partial content and several properties
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    # user service
    When I send a "GET" request to "/people?q=use serv"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_peoples.json"
    And the JSON node "hydra:totalItems" should be equal to "4"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people?q=Tonton Yourock"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to "0"

  Scenario: Search people via simple search on exact position code
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people?q=code_10"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_peoples.json"
    And the JSON node "hydra:totalItems" should be equal to "1"

  Scenario: Test the visibility of people during their career
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/20" with body:
    """
    {
      "disabled": true,
      "hidden": true
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people.json"
    And the JSON node "disabled" should be true
    And the JSON node "hidden" should be true
    Then I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/20" with body:
    """
    {
      "hidden": false
    }
    """
    Then the response status code should be 200
    And the JSON node "disabled" should be true
    And the JSON node "hidden" should be false
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people.json"
    Then I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/20" with body:
    """
    {
      "disabled": false
    }
    """
    Then the response status code should be 200
    And the JSON node "disabled" should be false
    And the JSON node "hidden" should be false
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people.json"
    Then I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/20" with body:
    """
    {
      "disabled": true
    }
    """
    Then the response status code should be 200
    And the JSON node "disabled" should be true
    And the JSON node "hidden" should be false
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people.json"
    Then I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/20" with body:
    """
    {
      "hidden": true
    }
    """
    Then the response status code should be 200
    And the JSON node "disabled" should be true
    And the JSON node "hidden" should be true
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people.json"

  Scenario: A specific serialization group exposes data for group member
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people?normalization_groups[]=group_member"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_peoples_group_name.json"

  Scenario: A specific serialization group exposes data of BU / region / subdivision / division
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people?normalization_groups[]=people:division"
    Then the response status code should be 200
    And the JSON node "hydra:member[10].businessUnit.region.subDivision.division.@id" should exist

  Scenario: Search all people by contract type
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people?contractType=/contract_types/1"
    Then the response should be an error stating "You are not granted the required permissions to search people by contract type."

  Scenario: update a people username (needed for the next test)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/17" with body:
    """
    {
      "username": "acbregeron@tld-group.com",
      "email": "acbregeron@tld-group.com",
      "hidden": false
    }
    """
    Then the response status code should be 200

  Scenario: update a people username with an extranet user email
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/17" with body:
    """
    {
      "username": "julien.lepers@tld.com",
      "email": "julien.lepers@tld.com"
    }
    """
    Then the response status code should not be 409
    And the response status code should be 422

  Scenario: update a people position update his acls
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/323" with body:
    """
    {
      "position": "/positions/3"
    }
    """
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/people/323?normalization_groups[]=group_member"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people_group_name.json"
    And the JSON node "acls" should have 4 elements
    And the JSON node "acls[3].group.name" should be equal to the string "ROLE_PSM"

  Scenario: Public pictures are accessible to everyone
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/public/people/17/photo/1"
    Then the response status code should be 200

  Scenario: Public pictures are accessible to everyone
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/public/people/18/photo/2"
    Then the response status code should be 404

  Scenario: Extranet user can't delete a user
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/people/199"
    Then the response status code should be 403

  Scenario: A User can request a user deletion
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/people/2"
    Then the response status code should be 422
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/people/199"
    Then the response status code should be 204

  Scenario: Delete a people enable or not hidden
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/people/11"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The people 'NEW LASTNAME new firstname' is not deletable"

  Scenario: Superuser can get contract type of any user
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people/40"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people.json"
    And the JSON node "contractType" should exist
    And the JSON node "contractType.name" should be equal to the string "Sous-Fifre"

  Scenario: Superuser can get contract type of all user
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_peoples.json"
    And the JSON node "hydra:member[0].contractType" should exist

  Scenario: GTCD can update contract type of any user
    Given I authenticate as the intranet user "user-gtcd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/40" with body:
    """
    {
      "contractType": "/contract_types/2",
      "firstname": "Monique"
    }
    """
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-gtcd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people/40"
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people.json"
    And the JSON node "contractType.name" should be equal to the string "Saisonnier"
    And the JSON node "firstname" should not be equal to the string "Monique"

  Scenario: Filter categorized/uncategorized people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people?categorized=false"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to the number 202
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_peoples.json"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people?categorized=true"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to the number 2
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_peoples.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Directory\People" is exposed on the API
    Then the filter "username" should be available and its type should be "string"
    And the filter "position" should be available and its type should be "string"
    And the filter "hidden" should be available and its type should be "bool"
    And the filter "disabled" should be available and its type should be "bool"
    And the filter "acls.group" should be available and its type should be "string"
    And the filter "acls.group.name" should be available and its type should be "string"
    And the filter "acls.group.features" should be available and its type should be "string"
    And the filter "acls.group.features.name" should be available and its type should be "string"
    And the filter "acls.location" should be available and its type should be "string"
    And the filter "acls.location.name" should be available and its type should be "string"
    And the filter "acls.location.erp" should be available and its type should be "int"
    And the filter "businessUnit" should be available and its type should be "string"
    And the filter "order[firstname]" should be available and its type should be "string"
    And the filter "order[lastname]" should be available and its type should be "string"
    And the filter "order[businessUnit.name]" should be available and its type should be "string"
    And the filter "order[position.description]" should be available and its type should be "string"
    And the filter "order[enableAt]" should be available and its type should be "string"
    And the filter "order[plannedDisableAt]" should be available and its type should be "string"
    And the filter "order[premise.supportTeam.name]" should be available and its type should be "string"
    And the filter "businessUnit.location.network" should be available and its type should be "string"
    And the filter "businessUnit.region" should be available and its type should be "string"
    And the filter "businessUnit.region.subDivision" should be available and its type should be "string"
    And the filter "businessUnit.region.subDivision.division" should be available and its type should be "string"
    And the filter "department" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "erpIdentifier" should be available and its type should be "string"
    And the filter "contractType" should be available and its type should be "string"
    And the filter "positionCategory" should be available and its type should be "string"
    And the filter "exists[mentor]" should be available and its type should be "bool"
    And the filter "exists[premise]" should be available and its type should be "bool"
    And the filter "excludeGroup" should be available and its type should be "string"
    And the filter "closestAirport" should be available and its type should be "string"
    And the filter "businessUnit.location" should be available and its type should be "string"
    And the filter "position.code" should be available and its type should be "string"
    And the filter "enableAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "enableAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "disabledAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "disabledAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "plannedDisableAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "plannedDisableAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "premise.supportTeam" should be available and its type should be "string"
    And the filter "peopleCurrentlyOrFutureDisabled[plannedDisableAt]" should be available and its type should be "string"
    And the filter "peopleCurrentlyOrFutureDisabled[disabledAt]" should be available and its type should be "string"

  Scenario: Request all people - filter 'normalizationGroupsOverride'
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "text/csv"
    When I send a "GET" request to "/people/download_directory?hidden=0&normalization_groups_override[]=expose_legacy&normalization_groups_override[]=people:export"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "text/csv"
    When I send a "GET" request to "/people/download_directory?hidden=0&normalization_groups_override[]=expose_legacy&normalization_groups_override[]=people:export"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "text/csv; charset=utf-8"
    Then the header "Content-Disposition" should be equal to "inline; filename=data.csv"
    And the csv file headers are:
      | ID | Legacy ID | Firstname | Lastname | Nickname | Email | Business unit | Legal entity | department | Position | Job title | Supervisor firstname | Supervisor lastname | Supervisor email | Mentor firstname | Mentor lastname | Mentor email | Address  | Premise | Support Team | Closest Airport | Reception | Mobile | Mobile_alternate | Fax | Phone | Home | Created At | Disabled At | User Division | Intranet Activation Date |
    Given I authenticate as the intranet user "user-gtcd@tld.fr"
    And I add "Accept" header equal to "text/csv"
    When I send a "GET" request to "/people/download_directory?hidden=0&normalization_groups_override[]=expose_legacy&normalization_groups_override[]=people:export"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "text/csv; charset=utf-8"
    Then the header "Content-Disposition" should be equal to "inline; filename=data.csv"
    And the csv file headers are:
      | ID | Legacy ID | Firstname | Lastname | Nickname | Email | Business unit | Legal entity | department | Position | Job title | Supervisor firstname | Supervisor lastname | Supervisor email | Mentor firstname | Mentor lastname | Mentor email | Address  | Premise | Support Team | Closest Airport | Reception | Mobile | Mobile_alternate | Fax | Phone | Home | Created At | Disabled At | User Division | Intranet Activation Date |
    Given I authenticate as the intranet user "user-fcg@tld.fr"
    And I add "Accept" header equal to "text/csv"
    When I send a "GET" request to "/people/download_directory?hidden=0&normalization_groups_override[]=expose_legacy&normalization_groups_override[]=people:export"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "text/csv; charset=utf-8"
    Then the header "Content-Disposition" should be equal to "inline; filename=data.csv"
    And the csv file headers are:
      | ID | Legacy ID | Firstname | Lastname | Nickname | Email | Business unit | Legal entity | department | Position | Job title | Supervisor firstname | Supervisor lastname | Supervisor email | Mentor firstname | Mentor lastname | Mentor email | Address  | Premise | Support Team | Closest Airport | Reception | Mobile | Mobile_alternate | Fax | Phone | Home | Created At | Disabled At | User Division | Intranet Activation Date |
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "text/csv"
    When I send a "GET" request to "/people/download_directory?hidden=0&normalization_groups_override[]=expose_legacy&normalization_groups_override[]=people:export"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "text/csv; charset=utf-8"
    Then the header "Content-Disposition" should be equal to "inline; filename=data.csv"
    And the csv file headers are:
      | ID | Legacy ID | Firstname | Lastname | Nickname | Email | Business unit | Legal entity | department | Position | Job title | Supervisor firstname | Supervisor lastname | Supervisor email | Mentor firstname | Mentor lastname | Mentor email | Address  | Premise | Support Team | Closest Airport | Reception | Mobile | Mobile_alternate | Fax | Phone | Home | Created At | Disabled At | User Division | Intranet Activation Date | Contract type | Coefficient | Gender | Disabled | Last login | Password updated at | EXCOM member | Report to EXCOM member |
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "text/csv"
    When I send a "GET" request to "/people/download_directory?hidden=0&normalization_groups_override[]=expose_legacy&normalization_groups_override[]=people:export"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "text/csv; charset=utf-8"
    Then the header "Content-Disposition" should be equal to "inline; filename=data.csv"
    And the csv file headers are:
      | ID | Legacy ID | Firstname | Lastname | Nickname | Email | Business unit | Legal entity | department | Position | Job title | Supervisor firstname | Supervisor lastname | Supervisor email | Mentor firstname | Mentor lastname | Mentor email | Address  | Premise | Support Team | Closest Airport | Reception | Mobile | Mobile_alternate | Fax | Phone | Home | Created At | Disabled At | User Division | Intranet Activation Date | Contract type | Coefficient | Gender | Disabled | Last login | Password updated at | EXCOM member | Report to EXCOM member |
   Given I authenticate as the intranet user "user-hr@tld.fr"
      And I add "Accept" header equal to "text/csv"
      When I send a "GET" request to "/people/download_directory?hidden=0&normalization_groups_override[]=expose_legacy&normalization_groups_override[]=people:export"
      Then the response status code should be 200
      And the header "Content-Type" should be equal to "text/csv; charset=utf-8"
      Then the header "Content-Disposition" should be equal to "inline; filename=data.csv"
      And the csv file headers are:
        | ID | Legacy ID | Firstname | Lastname | Nickname | Email | Business unit | Legal entity | department | Position | Job title | Supervisor firstname | Supervisor lastname | Supervisor email | Mentor firstname | Mentor lastname | Mentor email | Address  | Premise | Support Team | Closest Airport | Reception | Mobile | Mobile_alternate | Fax | Phone | Home | Created At | Disabled At | User Division | Intranet Activation Date | Contract type | Coefficient | Gender | Disabled | Last login | Password updated at | EXCOM member | Report to EXCOM member |
  Given I authenticate as the intranet user "user-lm@tld.fr"
      And I add "Accept" header equal to "text/csv"
      When I send a "GET" request to "/people/download_directory?hidden=0&normalization_groups_override[]=expose_legacy&normalization_groups_override[]=people:export"
      Then the response status code should be 200
      And the header "Content-Type" should be equal to "text/csv; charset=utf-8"
      Then the header "Content-Disposition" should be equal to "inline; filename=data.csv"
      And the csv file headers are:
        | ID | Legacy ID | Firstname | Lastname | Nickname | Email | Business unit | Legal entity | department | Position | Job title | Supervisor firstname | Supervisor lastname | Supervisor email | Mentor firstname | Mentor lastname | Mentor email | Address  | Premise | Support Team | Closest Airport | Reception | Mobile | Mobile_alternate | Fax | Phone | Home | Created At | Disabled At | User Division | Intranet Activation Date | Contract type | Coefficient | Gender | Disabled | Last login | Password updated at | EXCOM member | Report to EXCOM member |

  Scenario: A basic user can not set a mentor on another user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/82" with body:
    """
    {
      "mentor": "/people/12"
    }
    """
    Then the response status code should be 403

  Scenario: A basic user can create a user and set its mentor
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/people" with body:
    """
    {
      "password": "Johnzerzerzerz1",
      "firstname": "Johnny",
      "lastname": "Doe Mentor",
      "email": "johndoethementor@tld.fr",
      "phones": [
          {
              "type": "reception",
              "number": "+33 6 23 45 67 89",
              "professional": true
          }
      ],
      "businessUnit": "/business_units/8",
      "mentor": "/people/12",
      "premise": "/premises/1"
    }
    """
    Then the response status code should be 201
    And the JSON node "mentor" should not exist
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people.json"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/people/325"
    And the JSON node "mentor." should not exist

  Scenario: A granted user can set a mentor on another user
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/83" with body:
    """
    {
      "mentor": "/people/82"
    }
    """
    Then the response status code should be 200
    And the JSON node "mentor.@id" should be equal to "/people/82"
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people.json"

  Scenario: The evendors authorized application can access the people collection
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    Given I add "Accept" header equal to "application/ld+json"
    And I go to "/people"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_peoples.json"

  Scenario: Test linked account is disabled when user is disabled
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/people/87?normalization_groups[]=linked_account"
    Then the response status code should be 200
    And the JSON node "vendorUserLinked.disabled" should be false
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/87?normalization_groups[]=linked_account" with body:
    """
    {
      "disabled": true
    }
    """
    Then the response status code should be 200
    And the JSON node "vendorUserLinked.disabled" should be true

  Scenario: Basic users can't add acl
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/people/10/add_acl" with body:
    """
     {
       "group": "/groups/1",
       "location": "/locations/1"
     }
    """
    Then the response status code should be 403

  Scenario: Superusers can add acl
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/people/10/add_acl" with body:
    """
     {
       "group": "/groups/1",
       "location": "/locations/1",
       "expiredAt": "2099-02-02"
     }
    """
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acls?user=/users/10"
    And the JSON node "hydra:totalItems" should be equal to 1

  Scenario: HR can add acl
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/people/10/add_acl" with body:
    """
     {
       "group": "/groups/2",
       "location": "/locations/1"
     }
    """
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acls?user=/users/10"
    And the JSON node "hydra:totalItems" should be equal to 2

  Scenario: Superusers can add an existing acl
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/people/10/add_acl" with body:
    """
     {
       "group": "/groups/1",
       "location": "/locations/1"
     }
    """
    Then the response status code should be 400
    And the response should contain "User already has this ACL."

  Scenario: Basic users can't import acl
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/people/10/import_acl" with body:
    """
     {
       "acls": ["/acls/1", "/acls/2", "/acls/3"]
     }
    """
    Then the response status code should be 403

  Scenario: Superusers can import new acl without location
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/people/10/import_acl" with body:
    """
     {
       "acls": ["/acls/1", "/acls/2", "/acls/3"]
     }
    """
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acls?user=/users/10"
    And the JSON node "hydra:totalItems" should be equal to 5

  Scenario: Superusers can import new acl and specify a location
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/people/10/import_acl" with body:
    """
     {
       "acls": ["/acls/4"],
       "location": "locations/5"
     }
    """
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acls?user=/users/10"
    And the JSON node "hydra:totalItems" should be equal to 6
    And the JSON node "hydra:member[5].location.@id" should be equal to "/locations/5"

  Scenario: Superusers can not import empty acls
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/people/10/import_acl" with body:
    """
     {
       "acls": []
     }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "acls: You must specify at least one Acl"

  Scenario: Deactivating a People make them lose group_ACL_AUTH_INTRANET
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/people/104?normalization_groups%5B0%5D=group_member"
    Then the response status code should be 200
    And the JSON node "acls[0].group.name" should be equal to the string "ACL_AUTH_INTRANET"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/104" with body:
    """
    {
      "disabled": true
    }
    """
    Then the response status code should be 200
    And the JSON node "disabled" should be true
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/people/104?normalization_groups%5B0%5D=group_member"
    Then the response status code should be 200
    And the JSON node "acls[0].group.name" should not be equal to the string "ACL_AUTH_INTRANET"

  Scenario: Creating a person with the same email as another person automatically adds an increment on the email
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/people" with the body "tests/fixtures/json/directory_people/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_people.json"
    And the JSON node "root->email" should contain "john.doe1@tld-europe.com"
    And the JSON node "root->username" should contain "john.doe1@tld-europe.com"

  Scenario: Basic user don't see coming leavers - filter peopleCurrentlyOrFutureDisabled
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people?peopleCurrentlyOrFutureDisabled[plannedDisableAt]=2030-01-05&peopleCurrentlyOrFutureDisabled[disabledAt]=2030-04-05"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 0 elements

  Scenario: authorized user see coming leavers - filter peopleCurrentlyOrFutureDisabled
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people?peopleCurrentlyOrFutureDisabled[plannedDisableAt]=2030-01-05&peopleCurrentlyOrFutureDisabled[disabledAt]=2030-04-05"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_peoples.json"
    And the JSON node "hydra:member" should have 1 elements


  Scenario: PlannedDisableAt can be updated by supervisor or authorized user
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/31/update_planned_disable_date" with body:
    """
    { "plannedDisableAt": "2125-12-26" }
    """
    Then the response status code should be 200
    And the JSON node "plannedDisableAt" should be equal to the string "2125-12-26T00:00:00-05:00"
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/31/update_planned_disable_date" with body:
    """
    { "plannedDisableAt": "2125-12-27" }
    """
    Then the response status code should be 200
    And the JSON node "plannedDisableAt" should be equal to the string "2125-12-27T00:00:00-05:00"

  Scenario: Unauthorized user cannot update plannedDisableAt
    Given I authenticate as the intranet user "user-coo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/1/update_planned_disable_date" with body:
    """
    { "plannedDisableAt": "2125-12-27" }
    """
    Then the response status code should be 403

  Scenario: Request people with search route
    Given I authenticate as the intranet user "user-basic@tld.fr"
    Given I add "Accept" header equal to "application/ld+json"
    And I go to "/people/search?q=superuser&itemsPerPage=10&page=1&hidden=false&disabled=false"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_peoples_search.json"
    And the JSON node "hydra:member[0].email" should be equal to the string "user-superuser@tld.fr"
    And the JSON node "hydra:member[0].firstname" should be equal to the string "user"
    And the JSON node "hydra:member[0].lastname" should be equal to the string "SUPERUSER"
    And the JSON node "hydra:member[0].jobTitle" should exist
