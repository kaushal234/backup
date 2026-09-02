Feature: Test contact campaign

Scenario: Resource should only be accessible for intranet users
  Given I add "Accept" header equal to "application/ld+json"
  Then the resource "App\Entity\Communication\ContactCampaign" should only be available for "intranet" user

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Communication\ContactCampaign" is exposed on the API
    Then the filter "order[id]" should be available and its type should be "string"
    Then the filter "order[name]" should be available and its type should be "string"
    Then the filter "order[startedAt]" should be available and its type should be "string"
    Then the filter "order[endedAt]" should be available and its type should be "string"
    Then the filter "order[status]" should be available and its type should be "string"
    Then the filter "order[owner.lastname]" should be available and its type should be "string"
    And the filter "status" should be available and its type should be "string"
    And the filter "owner" should be available and its type should be "string"
    And the filter "businessUnits" should be available and its type should be "string"
    And the filter "contacts" should be available and its type should be "string"
    And the filter "contacts.extranetUserProfile.customer" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"
    And the filter "startedAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "startedAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "endedAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "startedAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "createdAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "createdAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "columns" should be available and its type should be "string"

  Scenario: Request all contact campaigns as intranet user
  Given I authenticate as the intranet user "user-basic@tld.fr"
  And I add "Accept" header equal to "application/ld+json"
  When I send a "GET" request to "/contact_campaigns"
  Then the response status code should be 200
  And the JSON should be valid according to the schema "tests/fixtures/json/contact_campaign/schemas/contact_campaigns.json"

Scenario: Request a single contact campaign as intranet user
  Given I authenticate as the intranet user "user-basic@tld.fr"
  And I add "Accept" header equal to "application/ld+json"
  When I send a "GET" request to "/contact_campaigns/1"
  Then the response status code should be 200
  And the JSON should be valid according to the schema "tests/fixtures/json/contact_campaign/schemas/contact_campaign.json"

Scenario: As basic user I can't create(POST) a campaign
  Given I authenticate as the intranet user "user-basic@tld.fr"
  And I add "Accept" header equal to "application/ld+json"
  And I add "Content-Type" header equal to "application/ld+json"
  When I send a "POST" request to "/contact_campaigns" with body:
  """
  {}
  """
  Then the response status code should be 403

Scenario: As ceo user I can create(POST) a campaign
  Given I authenticate as the intranet user "user-ceo@tld.fr"
  And I add "Accept" header equal to "application/ld+json"
  And I add "Content-Type" header equal to "application/ld+json"
  When I send a "POST" request to "/contact_campaigns" with body:
  """
  {
    "name": "Christmas day",
    "startedAt": "2025-12-01 00:05:00",
    "endedAt": "2025-12-31 00:05:00",
    "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quorum sine causa fieri nihil putandum est.",
    "businessUnits": ["/business_units/1"],
    "contacts": ["/sales/extranet_users/209"],
    "owner": "/people/31",
    "createdAt": "2002-12-31 00:05:00"
  }
  """
  Then the response status code should be 201
  And the JSON should be valid according to the schema "tests/fixtures/json/contact_campaign/schemas/contact_campaign.json"
  And the JSON node "name" should be equal to the string "Christmas day"
  And the JSON node "createdAt" should be newer than 1 minute ago
  And the JSON node "startedAt" should be equal to the string "2025-12-01T00:05:00-05:00"
  And the JSON node "endedAt" should be equal to the string "2025-12-31T00:05:00-05:00"
  And the JSON node "status" should be equal to the string "DRAFT"
  And the JSON node "description" should be equal to the string "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quorum sine causa fieri nihil putandum est."
  And the JSON node "owner.@id" should be equal to the string "/people/36"
  And the JSON node "contacts[0].@id" should be equal to the string "/sales/extranet_users/209"
  And the JSON node "businessUnits[0].@id" should be equal to the string "/business_units/1"

Scenario: As basic user I can't update(PUT) a campaign
  Given I authenticate as the intranet user "user-basic@tld.fr"
  And I add "Accept" header equal to "application/ld+json"
  And I add "Content-Type" header equal to "application/ld+json"
  When I send a "PUT" request to "/contact_campaigns/3" with body:
  """
  {}
  """
  Then the response status code should be 403

Scenario: As ceo user I can't update(PUT) a campaign
  Given I authenticate as the intranet user "user-ceo@tld.fr"
  And I add "Accept" header equal to "application/ld+json"
  And I add "Content-Type" header equal to "application/ld+json"
  When I send a "PUT" request to "/contact_campaigns/3" with body:
  """
  {
    "name": "Christmas day edited",
    "startedAt": "2025-11-03 00:05:00",
    "endedAt": "2025-12-25 00:05:00",
    "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quorum sine causa fieri nihil putandum est edited.",
    "businessUnits": ["/business_units/2"],
    "contacts": ["/sales/extranet_users/210"],
    "owner": "/people/31",
    "createdAt": "2002-12-31 00:05:00"
  }
  """
  Then the response status code should be 200
  And the JSON should be valid according to the schema "tests/fixtures/json/contact_campaign/schemas/contact_campaign.json"
  And the JSON node "name" should be equal to the string "Christmas day edited"
  And the JSON node "startedAt" should be equal to the string "2025-11-03T00:05:00-05:00"
  And the JSON node "createdAt" should be newer than 1 minute ago
  And the JSON node "endedAt" should be equal to the string "2025-12-25T00:05:00-05:00"
  And the JSON node "status" should be equal to the string "DRAFT"
  And the JSON node "description" should be equal to the string "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quorum sine causa fieri nihil putandum est edited."
  And the JSON node "owner.@id" should be equal to the string "/people/31"
  And the JSON node "contacts[0].@id" should be equal to the string "/sales/extranet_users/210"
  And the JSON node "businessUnits[0].@id" should be equal to the string "/business_units/2"

  Scenario: As basic user I can download the list of campaign xls
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "contact_campaigns?columns=id,name,owner,status,startedAt,endedAt,description"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Name | Owner | Status | Started At | Ended At | Description |

  Scenario: As basic user I can download the list contact of a campaign xls
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "contact_campaign/1/contacts?columns=id,username,email,extranetUserProfile.customer.name,firstname,lastname,address,extranetUserProfile.country.name,isVerified"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Username | Email | Customer | Firstname | Lastname | Address | Country | Information Verified |

  Scenario: As basic user I cant create task for check contact
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/tasks/batch" with body:
  """
  {
    "type": "contact.validation.address",
    "module": "/modules/9",
    "referenceId": [209],
    "campaign": "/contact_campaigns/1"
  }
  """
    Then the response status code should be 403

  Scenario: As ceo user I can create task for check contact
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/tasks/batch" with body:
  """
  {
    "type": "contact.validation.address",
    "module": "/modules/9",
    "referenceId": [209],
    "campaign": "/contact_campaigns/1"
  }
  """
    Then the response status code should be 201
