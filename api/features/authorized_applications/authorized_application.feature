Feature: Test authorized applications API
  Scenario: Request all authorized applications without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/authorized_applications"
    Then the response status code should be 401

  Scenario: Request a single authorized application without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/authorized_applications/1"
    Then the response status code should be 401

  Scenario: Authorized applications should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/authorized_applications"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/authorized_applications" with body:
    """
    {}
    """
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/authorized_applications/1"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/authorized_applications/1" with body:
    """
    {}
    """
    Then the response status code should be 403

  Scenario: Authorized applications should not be accessible to basic users
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/authorized_applications"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/authorized_applications" with body:
    """
    {}
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/authorized_applications/1"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/authorized_applications/1" with body:
    """
    {}
    """
    Then the response status code should be 403

  Scenario: Superusers can get an authorized application
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/authorized_applications/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/authorized_application/schemas/authorized_application.json"
    And the JSON node "key" should not exist

  Scenario: Superusers can get all the authorized applications
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/authorized_applications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/authorized_application/schemas/authorized_applications.json"
    And the JSON node "hydra:member[0].key" should not exist

  Scenario: Superusers can't delete an application
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/authorized_applications"
    Then the response status code should be 405

  Scenario: Superusers can register a new authorized application
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/authorized_applications" with body:
    """
    {
        "name": "LN et les garçons",
        "keyExpiresOn": "2999-12-31T23:59:00-05:00",
        "features": [
          "/features/111",
          "/features/66"
        ]
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/authorized_application/schemas/authorized_application.json"
    And the JSON node "key" should exist
    And the JSON node "features" should have 2 element

  Scenario: Superusers can only edit the disabled field of an authorized application and add/remove features
    Given I authenticate as the authorized application "LN et les garçons"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/authorized_applications/7"
    Then the response status code should be 200
    And the JSON node "features" should have 2 element
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/authorized_applications/7" with body:
    """
    {
        "name": "LN des nôôôôôôtres",
        "disabled": true,
        "features": [
          "/features/111"
        ],
        "keyGeneratedOn": "1983-04-19",
        "keyExpiresOn": "2983-04-19",
        "createdBy": "/people/1"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/authorized_application/schemas/authorized_application.json"
    And the JSON node "key" should not exist
    And the JSON node "disabled" should be true
    And the JSON node "name" should be equal to the string "LN et les garçons"
    And the JSON node "keyGeneratedOn" should contain today's date
    And the JSON node "keyExpiresOn" should be equal to the string "2999-12-31T23:59:00-05:00"
    And the JSON node "createdBy.@id" should be equal to the string "/people/12"
    And the JSON node "features" should have 1 element
    Given I authenticate as the authorized application "LN et les garçons"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/authorized_applications/3"
    Then the response status code should be 401

  Scenario: A registered authorized application can access the API and has its own ACL logic
    Given I authenticate as the authorized application "PIO"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/authorized_applications/1"
    Then the response status code should be 403
    Given I authenticate as the authorized application "PIO"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/authorized_applications/3"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/authorized_application/schemas/authorized_application.json"
    And the JSON node "key" should not exist

  Scenario: Superusers can delete an authorized application
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/authorized_applications/4"
    Then the response status code should be 204

  Scenario: A registered authorized application can access the people csv report
    Given I authenticate as the authorized application "powerbi"
    And I add "Accept" header equal to "text/csv"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "people/download_directory?hidden=0&itemsPerPage=500&normalization_groups_override[0]=expose_legacy&normalization_groups_override[1]=people%3Aexport&page=1"
    Then the response status code should be 200
    And the csv file headers are:
      | ID | Legacy ID | Firstname | Lastname | Nickname | Email | Business unit | Legal entity | department | Position | Job title | Supervisor firstname | Supervisor lastname | Supervisor email | Mentor firstname | Mentor lastname | Mentor email | Address  | Premise | Support Team | Closest Airport | Reception | Mobile | Mobile_alternate | Fax | Phone | Home | Created At | Disabled At | User Division | Intranet Activation Date | Contract type | Coefficient | Gender | Disabled | Last login | Password updated at | EXCOM member | Report to EXCOM member |

  Scenario: A registered authorized application can access on equipment record public data by serial number
    Given I authenticate as the authorized application "EXTRANET"
    And I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/public/equipment_records/GT2023"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_record_public/schemas/equipment_record.json"

  Scenario: A registered non authorized application can't access on equipment record public data by serial number
    Given I authenticate as the authorized application "powerbi"
    And I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/public/equipment_records/GT2023"
    Then the response status code should be 403

  Scenario: A registered non authorized application can't access on equipment record public data by serial number
    Given I authenticate as the authorized application "PIO"
    And I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/public/equipment_records/GT2023"
    Then the response status code should be 403

  Scenario: A registered authorized application can access the public equipment record collection
    Given I authenticate as the authorized application "EXTRANET"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/public/equipment_records"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_record_public/schemas/equipment_records.json"

  Scenario: A registered non authorized application can't access the public equipment record collection
    Given I authenticate as the authorized application "powerbi"
    And I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/public/equipment_records"
    Then the response status code should be 403

  Scenario: A registered non authorized application can't access the public equipment record collection
    Given I authenticate as the authorized application "PIO"
    And I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/public/equipment_records"
    Then the response status code should be 403
