Feature: Test lead time

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Manufacturing\LeadTime" should only be available for intranet user

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Manufacturing\LeadTime" is exposed on the API
    Then the filter "factory" should be available and its type should be "string"
    Then the filter "productFamily" should be available and its type should be "string"
    And the filter "productFamily.productType" should be available and its type should be "string"
    And the filter "productFamily.hidden" should be available and its type should be "bool"

  Scenario: Basic user can see lead times
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/lead_times"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/lead_time/schemas/lead_times.json"
    And the JSON node "hydra:totalItems" should be equal to "2"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/lead_times/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/lead_time/schemas/lead_time.json"

  Scenario: Basic user can't post a lead time
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/manufacturing/lead_times/batch" with body:
    """
    {}
    """
    Then the response status code should be 403

  Scenario: Basic user can't edit a lead time
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/manufacturing/lead_times/batch" with body:
    """
    {
      "leadTimes": [
         {
             "@id": "/lead_times/1",
             "description": "lide tayme fail insert"
         }
      ],
      "fullUpdate": false
    }
    """
    Then the response status code should be 403

  Scenario: Lead times that are posted for a factory which differs from the PSM's acl location are ignored
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/manufacturing/lead_times/batch" with body:
    """
    {
      "leadTimes": [
         {
             "weeks": 1,
             "description": "lydhe thyme not inserted",
             "factory": "/locations/30",
             "productFamily": "/sales/product_families/25"
         }
      ],
       "fullUpdate": false
    }
    """
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-psm@tld.fr"
    When I send a "GET" request to "/lead_times"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to "2"

  Scenario: PSM user can't edit a lead time if his location differ from the lead time location
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/manufacturing/lead_times/batch" with body:
    """
    {
      "leadTimes": [
         {
             "@id": "/lead_times/2",
             "description": "lit de temps well insert"
         }
      ],
       "fullUpdate":false
    }
    """
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-psm@tld.fr"
    When I send a "GET" request to "/lead_times/2"
    Then the response status code should be 200
    And the JSON node "description" should be equal to the string "This unit is made of file like file colins"

  Scenario: PSM user can't edit a lead time if the new lead time location differ from his location
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/manufacturing/lead_times/batch" with body:
    """
      {
        "leadTimes": [
           {
               "@id": "/lead_times/1",
               "factory": "/locations/30"
           }
        ],
         "fullUpdate": false
      }
    """
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-psm@tld.fr"
    When I send a "GET" request to "/lead_times/1"
    Then the response status code should be 200
    And the JSON node "factory.@id" should be equal to "/locations/29"

  Scenario: PSM user can post a lead time if his location is the same as the lead time location
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/manufacturing/lead_times/batch" with body:
    """
    {
      "leadTimes": [
         {
             "weeks": 1,
             "description": "lit au thym is good inserted",
             "factory": "/locations/29",
             "productFamily": "/sales/product_families/25"
         }
      ],
       "fullUpdate": false
    }
    """
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-psm@tld.fr"
    When I send a "GET" request to "/lead_times"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to "3"

  Scenario: PSM user can edit a lead time if his location is the same as the lead time location
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/manufacturing/lead_times/batch" with body:
    """
    {
      "leadTimes": [
         {
             "@id": "/lead_times/1",
             "weeks": 9
         }
      ],
       "fullUpdate": false
    }
    """
    Then the response status code should be 204
    And an update log should have been inserted on resource "/lead_times/1" with a changeset on the property "weeks"
    Given I authenticate as the intranet user "user-psm@tld.fr"
    When I send a "GET" request to "/lead_times/1"
    Then the response status code should be 200
    And the JSON node "weeks" should be equal to the number 9
    And the JSON node "previousValue" should be equal to the number 1

  Scenario: PSM user can delete a lead time if his location is the same as the lead time location
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/lead_times/1"
    Then the response status code should be 204



