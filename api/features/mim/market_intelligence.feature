  Feature: Test Market intelligence module

    Scenario: Resource should only be accessible for intranet users
      Given I add "Accept" header equal to "application/ld+json"
      Then the resource "App\Entity\Sales\MarketIntelligence\MarketIntelligence" should only be available for intranet user

    Scenario: Request all market intelligences
      Given I authenticate as the intranet user "user-superuser@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "sales/market_intelligences"
      Then the response status code should be 200
      And the JSON should be valid according to the schema "tests/fixtures/json/sales/market_intelligence/schemas/market_intelligences.json"
      And the JSON node "hydra:totalItems" should be equal to 3
      And the JSON node "hydra:member[0].marketIntelligencesLinked" should have "2" element

    Scenario: As a basic user I can only see non confidential market intelligences
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "sales/market_intelligences"
      Then the response status code should be 200
      And the JSON should be valid according to the schema "tests/fixtures/json/sales/market_intelligence/schemas/market_intelligences.json"
      # 3 MIM are created, but 1 is confidential
      And the JSON node "hydra:totalItems" should be equal to 2
      And the JSON node "hydra:member[0].marketIntelligencesLinked" should have "1" element

    Scenario: Confidential market intelligence should not be accessible to basic user
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "sales/market_intelligences/3"
      Then the response status code should be 403

    Scenario: Non confidential market intelligence should be accessible to basic user
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "sales/market_intelligences/1"
      Then the response status code should be 200
      And the JSON should be valid according to the schema "tests/fixtures/json/sales/market_intelligence/schemas/market_intelligence.json"
      And the JSON node "marketIntelligencesLinked" should have "1" element

    Scenario: Filters are declared on resource
      Given the class "App\Entity\Sales\MarketIntelligence\MarketIntelligence" is exposed on the API
      Then the filter "customers" should be available and its type should be "string"
      And the filter "productTypes" should be available and its type should be "string"
      And the filter "competitors" should be available and its type should be "string"
      And the filter "suppliers" should be available and its type should be "string"
      And the filter "type" should be available and its type should be "string"
      And the filter "order[id]" should be available and its type should be "string"
      And the filter "id" should be available and its type should be "int"

    Scenario: Market intelligence can be filtered
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "sales/market_intelligences?q=mathy"
      Then the response status code should be 200
      And the JSON should be valid according to the schema "tests/fixtures/json/sales/market_intelligence/schemas/market_intelligences.json"
      And the JSON node "hydra:totalItems" should be equal to 1

    Scenario: Market intelligence can be filtered
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "sales/market_intelligences?normalizationGroupsOverride[]=market_intelligence:list"
      Then the response status code should be 200
      And the JSON should be valid according to the schema "tests/fixtures/json/sales/market_intelligence/schemas/market_intelligences_list.json"
      And the JSON node "hydra:totalItems" should be equal to 2

    Scenario: As a basic user, I can create a market intelligence
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "POST" request to "/sales/market_intelligences" with the body "tests/fixtures/json/sales/market_intelligence/dummies/post.json"
      Then the response status code should be 201
      And the JSON should be valid according to the schema "tests/fixtures/json/sales/market_intelligence/schemas/market_intelligence.json"
      And the JSON node "poster.@id" should be equal to the string "/people/11"
      And the JSON node "legacyId" should be equal to 10004
      And the JSON node "createdAt" should be newer than 1 minute ago
      And the JSON node "type.name" should be equal to the string "Pricing info"
      And the JSON node "shortDescription" should be equal to the string "Ceci est une courte description"
      And the JSON node "description" should be equal to the string "Ceci est une longue description"
      And the JSON node "customers[0].@id" should be equal to the string "/sales/customers/4"
      And the JSON node "divisions[0].@id" should be equal to the string "/divisions/2"
      And the JSON node "positionLevels[0]" should be equal to the string "/position_levels/2"
      And the JSON node "marketIntelligenceFiles" should have 0 element
      And the JSON node "url" should be equal to the string "http://www.justfortest.com"
      And a message of class "App\Message\Sales\NotifyMarketIntelligenceCreate" should have been sent in the bus

    Scenario: As a basic user, I can create a confidential market intelligence
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "POST" request to "/sales/market_intelligences" with body:
      """
      {
        "type": "/sales/market_intelligence_types/1",
        "shortDescription": "Test",
        "positionLevels": ["/position_levels/5"],
        "description": "Test aussi",
        "customers": ["/sales/customers/4"]
      }
      """
      Then the response status code should be 201
      And the JSON should be valid according to the schema "tests/fixtures/json/sales/market_intelligence/schemas/market_intelligence.json"
      And a message of class "App\Message\Sales\NotifyMarketIntelligenceCreate" should have been sent in the bus

    Scenario: As poster of market intelligence, I can update position levels of market intelligence
      Given I authenticate as the intranet user "user-superuser@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "PUT" request to "/sales/market_intelligences/5" with body:
      """
      {
        "positionLevels": ["/position_levels/1"]
      }
      """
      Then the response status code should be 200
      And the JSON node "positionLevels[0]" should not be equal to the string "/position_levels/1"
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "PUT" request to "/sales/market_intelligences/4" with body:
      """
      {
        "positionLevels": ["/position_levels/1"]
      }
      """
      Then the response status code should be 200
      And the JSON should be valid according to the schema "tests/fixtures/json/sales/market_intelligence/schemas/market_intelligence.json"
      And the JSON node "positionLevels[0]" should be equal to the string "/position_levels/1"

    Scenario: As a basic user, I can't create a market intelligence with at least one customer, competitor or product type
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "POST" request to "/sales/market_intelligences" with body:
      """
        {
          "type": "/sales/market_intelligence_types/1",
          "shortDescription": "Ceci est une courte description",
          "description": "Ceci est une longue description"
        }
      """
      Then the response status code should be 422
      And the JSON node "violations[0].message" should be equal to the string "One of Customer, Competitor, Supplier or Product Type should not be null."
      And the JSON node "violations[0].propertyPath" should be equal to the string "customers"

    Scenario: As a basic user, I can create a market intelligence without customer/product type/competitor if Miscellaneous type
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "POST" request to "/sales/market_intelligences" with body:
      """
        {
          "type": "/sales/market_intelligence_types/4",
          "shortDescription": "Ceci est une courte description",
          "description": "Ceci est une longue description"
        }
      """
      Then the response status code should be 201
      And the JSON node "shortDescription" should be equal to the string "Ceci est une courte description"
      And the JSON node "description" should be equal to the string "Ceci est une longue description"

    Scenario: As a basic user, I can edit a market intelligence I created
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "PUT" request to "/sales/market_intelligences/4" with the body "tests/fixtures/json/sales/market_intelligence/dummies/put.json"
      Then the response status code should be 200
      And the JSON should be valid according to the schema "tests/fixtures/json/sales/market_intelligence/schemas/market_intelligence.json"
      And the JSON node "type.name" should be equal to the string "Technical datasheet"
      And the JSON node "shortDescription" should be equal to the string "Ceci est une courte description, mais modifiée"
      And the JSON node "description" should be equal to the string "Ceci est une longue description, mais modifiée"
      And the JSON node "customers[0].@id" should be equal to the string "/sales/customers/3"
      And the JSON node "customers[1].@id" should be equal to the string "/sales/customers/4"
      And the JSON node "competitors[0].@id" should be equal to the string "/sales/competitors/1"
      And the JSON node "productTypes[0].@id" should be equal to the string "/sales/product_types/1"
      And the JSON node "marketIntelligenceFiles" should have 0 element
      And the JSON node "url" should be equal to the string "http://www.justforupdatetest.com"
      And a message of class "App\Message\Sales\NotifyMarketIntelligenceUpdate" should have been sent in the bus

    Scenario: Notification is sent when comment is posted on market intelligence
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "POST" request to "/comments" with body:
      """
      {
        "message": "Coucou",
        "resource": "/sales/market_intelligences/4"
      }
      """
      Then the response status code should be 201
      And a message of class "App\Message\Sales\NotifyMarketIntelligenceComment" should have been sent in the bus

    Scenario: As a basic user, I can't edit a market intelligence I did not created
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "PUT" request to "/sales/market_intelligences/3" with the body "tests/fixtures/json/sales/market_intelligence/dummies/put.json"
      Then the response status code should be 403

    Scenario: As a superuser, I can edit any market
      Given I authenticate as the intranet user "user-superuser@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "PUT" request to "/sales/market_intelligences/4" with body:
      """
        {
          "type": "/sales/market_intelligence_types/4"
        }
      """
      Then the response status code should be 200
      And the JSON should be valid according to the schema "tests/fixtures/json/sales/market_intelligence/schemas/market_intelligence.json"
      And the JSON node "type.name" should be equal to the string "Miscellaneous Information"
      And a message of class "App\Message\Sales\NotifyMarketIntelligenceUpdate" should have been sent in the bus

    Scenario: No notification sent when edition of only positionLevels property
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "PUT" request to "/sales/market_intelligences/4" with body:
      """
        {
          "positionLevels": ["/position_levels/3"]
        }
      """
      Then the response status code should be 200
      And the JSON should be valid according to the schema "tests/fixtures/json/sales/market_intelligence/schemas/market_intelligence.json"
      And the JSON node "positionLevels[0]" should be equal to the string "/position_levels/3"
      And no message of class "App\Message\Sales\NotifyMarketIntelligenceUpdate" should have been sent in the bus
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "PUT" request to "/sales/market_intelligences/4" with body:
      """
        {
          "positionLevels": ["/position_levels/4"],
          "description": "Just for test"
        }
      """
      Then the response status code should be 200
      And the JSON should be valid according to the schema "tests/fixtures/json/sales/market_intelligence/schemas/market_intelligence.json"
      And the JSON node "positionLevels[0]" should be equal to the string "/position_levels/4"
      And the JSON node "description" should be equal to the string "Just for test"
      And a message of class "App\Message\Sales\NotifyMarketIntelligenceUpdate" should have been sent in the bus

    Scenario: As a superuser, I can't change market poster
      Given I authenticate as the intranet user "user-superuser@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "PUT" request to "/sales/market_intelligences/2" with body:
      """
        {
          "poster":"/people/12"
        }
      """
      Then the response status code should be 200
      And the JSON should be valid according to the schema "tests/fixtures/json/sales/market_intelligence/schemas/market_intelligence.json"
      And the JSON node "poster.@id" should be equal to the string "/people/31"

    @resetFileTable
    Scenario: As a basic user, I can upload a file to a public market intelligence
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I add "Content-type" header equal to "multipart/form-data"
      When I send a "POST" request to "/sales/market_intelligences/2/files" with file "file" "file.doc"
      Then the response status code should be 201
      And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

    Scenario: As poster, I can't upload a file to a confidential market intelligence
      Given I authenticate as the intranet user "user-hr@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I add "Content-type" header equal to "multipart/form-data"
      When I send a "POST" request to "/sales/market_intelligences/3/files" with file "file" "file.doc"
      Then the response status code should be 201

    Scenario: As a superuser, I can't upload a file to a confidential market intelligence
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I add "Content-type" header equal to "multipart/form-data"
      When I send a "POST" request to "/sales/market_intelligences/3/files" with file "file" "file.doc"
      Then the response status code should be 403

    Scenario: Upload an invalid file to a market intelligence
      Given I authenticate as the intranet user "user-superuser@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "multipart/form-data"
      When I send a "POST" request to "/sales/market_intelligences/2/files" with file "file" "image.gif"
      Then the response status code should be 422
      And the JSON node "@type" should be equal to "ConstraintViolation"
      And the JSON node "hydra:description" should contain "marketIntelligenceFiles: The mime type of the file is invalid"
      And the JSON node "violations[0].propertyPath" should be equal to "marketIntelligenceFiles"
      And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

    Scenario: Download a market intelligence public attached file as an extranet user should not be possible
      Given I authenticate as the extranet user "julien.lepers@tld.com"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "/sales/market_intelligences/2/files/1"
      Then the response status code should be 404

    Scenario: Download a market intelligence public attached file as a basic user
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "/sales/market_intelligences/2/files/1"
      Then the response status code should be 200

    Scenario: Download a market intelligence confidential attached file as a basic user should not be permitted
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "/sales/market_intelligences/3/files/2"
      Then the response status code should be 403

    Scenario: Download a market intelligence confidential attached file as a superuser should not be permitted
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "/sales/market_intelligences/3/files/2"
      Then the response status code should be 403

    Scenario: Download a market intelligence confidential attached file as a poster should not be permitted
      Given I authenticate as the intranet user "user-hr@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "/sales/market_intelligences/3/files/2"
      Then the response status code should be 200

    Scenario: I can't delete a file from a non-confidential market intelligence if I'm not the poster of the MIM
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "DELETE" request to "/sales/market_intelligences/2/files/1"
      Then the response status code should be 403

    Scenario: As a basic user, I can't delete a file from a confidential market intelligence
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "DELETE" request to "/sales/market_intelligences/3/files/2"
      Then the response status code should be 403

    Scenario: As the poster of a confidential market intelligence, I can delete a file
      Given I authenticate as the intranet user "user-hr@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "DELETE" request to "/sales/market_intelligences/3/files/2"
      Then the response status code should be 204
      And an update log should have been inserted on resource "/sales/market_intelligences/3" with a changeset on the property "marketIntelligenceFiles"

    Scenario: As a user basic, I can delete a market intelligence I created
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "DELETE" request to "/sales/market_intelligences/4"
      Then the response status code should be 204

    Scenario: As a user basic, I can't delete any market intelligence
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "DELETE" request to "/sales/market_intelligences/2"
      Then the response status code should be 403

    Scenario: As a superuser, I can delete any market intelligence
      Given I authenticate as the intranet user "user-superuser@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "DELETE" request to "/sales/market_intelligences/2"
      Then the response status code should be 204

    Scenario: When I remove a customer, it is removed from MIM
      Given I authenticate as the intranet user "user-superuser@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "POST" request to "/sales/market_intelligences" with body:
      """
      {
        "type": "/sales/market_intelligence_types/1",
        "shortDescription": "Ceci est une courte description",
        "description": "Ceci est une longue description",
        "customers": ["/sales/customers/45"]
      }
      """
      Then the response status code should be 201
      Given I authenticate as the intranet user "user-superuser@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "DELETE" request to "/sales/customers/45"
      Then the response status code should be 204
      Given I authenticate as the intranet user "user-superuser@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "GET" request to "/sales/market_intelligences/6"
      Then the response status code should be 200
      And the JSON node "customers[0]" should not exist

    Scenario: Link a MIM with the same MIM should not be possible
      Given I authenticate as the intranet user "user-superuser@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "PUT" request to "/sales/market_intelligences/1" with body:
      """
      {
        "marketIntelligencesLinked": ["/sales/market_intelligences/1"]
      }
      """
      Then the response status code should be 422
      And the JSON node "violations[0].message" should be equal to the string "A MIM can't reference itself"
      And the JSON node "violations[0].propertyPath" should be equal to the string "marketIntelligencesLinked"


