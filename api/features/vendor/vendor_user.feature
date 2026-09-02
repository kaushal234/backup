Feature: Test Vendor users

  Scenario: Get a token - right password
    Given I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/token" with parameters:
      | key      | value                   |
      | username | vendor.user@vendor.fr   |
      | password | P@ssw0rd15chars         |
      | portal   | evendors                |
    Then the response status code should be 200
    And a total of 5 request has been sent to ION

  Scenario: Get a token - wrong password
    Given I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/token" with parameters:
      | key      | value                   |
      | username | vendor.user@vendor.fr   |
      | password | wrong                   |
      | portal   | evendors                |
    Then the response status code should be 401

  Scenario: Request all vendor users without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_users"
    Then the response status code should be 401

  Scenario: Request a single vendor user without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_users/300"
    Then the response status code should be 401

  Scenario: Request all vendor users as intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_users"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_user/schemas/vendor_users.json"
    And less than 10 database queries must have been executed

  Scenario: Request all vendor users as extranet user should not be permitted
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_users"
    Then the response status code should be 403

  Scenario: Request a single vendor user as extranet user should not be permitted
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_users/300"
    Then the response status code should be 403

  Scenario: As vendor user I should only see my profile when fetching collection
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_users"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_user/schemas/vendor_users.json"
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0].@id" should be equal to the string "/purchasing/vendor_users/300"

  Scenario: As vendor user I should only see my profile when fetching item
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_users/300"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_user/schemas/vendor_user.json"
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_users/301"
    Then the response status code should be 404

  Scenario: Request a single vendor user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_users/300"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_user/schemas/vendor_user.json"

  Scenario: Request all vendor users as authorized application should not be permitted
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_users"
    Then the response status code should be 403

  Scenario: Request a single vendor user as authorized application should not be permitted
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_users/300"
    Then the response status code should be 403

  Scenario: Regenerate password doesn't change password directly
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_users"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 20
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/reset_password" with body:
    """
    {
      "portal": "evendors",
      "email": "vendor.user@vendor.fr"
    }
    """
    Then the response status code should be 204
    And an email should have been sent asynchronously with subject "Reset Password Confirmation"
    And this asynchronous email should contain an element matching selector "p.text-introduction" which value is "Hello Philippe LAST!"
    And this asynchronous email should be sent from "noreply@tld-gse.com"
    And this asynchronous email should be sent to "vendor.user@vendor.fr"
    And this asynchronous email body should contain a link to "https://evendors.tld-gse.com/security/reset-password-confirmation/"

  Scenario: Regenerate password doesn't change password directly and create a new user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_users"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to "20"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/reset_password" with body:
    """
    {
      "portal": "evendors",
      "email": "cindy.qin@tld-asia.com"
    }
    """
    And a "Contact_v3" SOAP client has been created
    And this client has been called on the operation "List" with the following request:
    """
    {
      "ControlArea": {
        "maxNumberOfObjects": 1,
        "Filter": {
          "LogicalExpression": {
            "logicalOperator": "and",
            "ComparisonExpression": [
              {
                "comparisonOperator": "eq",
                "instanceValue": "cindy.qin@tld-asia.com",
                "attributeName": "Contact_v3.emailAddress"
              }
            ]
          }
        }
      }
    }
    """
    And this client has been called on the operation "Show" with the following request:
    """
    {
      "DataArea": {
        "Contact_v3": {
          "contactCode": "SUPL7012"
        }
      }
    }
    """
    And a total of 2 request has been sent to ION
    Then the response status code should be 204
    And an email should have been sent asynchronously with subject "Reset Password Confirmation"
    And this asynchronous email should contain an element matching selector "p.text-introduction" which value is "Hello Cindy QIN!"
    And this asynchronous email should be sent from "noreply@tld-gse.com"
    And this asynchronous email should be sent to "cindy.qin@tld-asia.com"
    And this asynchronous email body should contain a link to "https://evendors.tld-gse.com/security/reset-password-confirmation/"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_users"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to "21"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people/88?normalization_groups[]=linked_account"
    Then the response status code should be 200
    And the JSON node "vendorUserLinked.id" should be equal to "323"

  Scenario: Only allowed vendor user can reset Password
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/reset_password" with body:
    """
    {
      "portal": "evendors",
      "email": "balaprasad.thigulla@infor.com"
    }
    """
    And a "Contact_v3" SOAP client has been created
    And this client has been called on the operation "List" with the following request:
    """
    {
      "ControlArea": {
        "maxNumberOfObjects": 1,
        "Filter": {
          "LogicalExpression": {
            "logicalOperator": "and",
            "ComparisonExpression": [
              {
                "comparisonOperator": "eq",
                "instanceValue": "balaprasad.thigulla@infor.com",
                "attributeName": "Contact_v3.emailAddress"
              }
            ]
          }
        }
      }
    }
    """
    And a total of 1 request has been sent to ION
    Then the response status code should be 401

  Scenario: Disabled LN User with right category can regenerate password
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/reset_password" with body:
    """
    {
      "portal": "evendors",
      "email": "kris.geiger-blue@aerospecialties.com"
    }
    """
    Then the response status code should be 204
    And an email should have been sent asynchronously with subject "Reset Password Confirmation"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_users/303"
    Then the response status code should be 200
    And the JSON node "hidden" should be false
    And the JSON node "disabled" should be false


