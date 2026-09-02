Feature: Test Contract

  Scenario: Filters are declared on Contract resource
    Given the class "App\Entity\Legal\Contract" is exposed on the API
    Then the filter "order[id]" should be available and its type should be "string"
    And the filter "order[value]" should be available and its type should be "string"
    And the filter "order[renewalPeriod]" should be available and its type should be "string"
    And the filter "indefinitePeriodType" should be available and its type should be "bool"
    And the filter "id" should be available and its type should be "int"
    And the filter "status" should be available and its type should be "string"
    And the filter "shortDescription" should be available and its type should be "string"
    And the filter "renewalUnit" should be available and its type should be "string"
    And the filter "externalParty" should be available and its type should be "string"
    And the filter "internalParty" should be available and its type should be "string"
    And the filter "createdBy" should be available and its type should be "string"
    And the filter "owner" should be available and its type should be "string"
    And the filter "jurisdiction" should be available and its type should be "string"
    And the filter "currency" should be available and its type should be "string"
    And the filter "subCategory" should be available and its type should be "string"
    And the filter "parentContract" should be available and its type should be "string"
    And the filter "businessUnits" should be available and its type should be "string"
    And the filter "regions" should be available and its type should be "string"
    And the filter "divisions" should be available and its type should be "string"
    And the filter "premises" should be available and its type should be "string"
    And the filter "createdAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "createdAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "startDate[after]" should be available and its type should be "DateTimeInterface"
    And the filter "startDate[before]" should be available and its type should be "DateTimeInterface"
    And the filter "expirationDate[after]" should be available and its type should be "DateTimeInterface"
    And the filter "expirationDate[before]" should be available and its type should be "DateTimeInterface"
    And the filter "q" should be available and its type should be "string"

  Scenario: Request all contracts should be possible only for basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contracts.json"

#   FULL READ ACCESS
  Scenario: Request a single contract should be possible only for moo, gku and Chairman whatever the category is
    Given I authenticate as the intranet user "user-moo-cat@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/8"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
    Given I authenticate as the intranet user "user-chairman@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"

#    BANK Scenarii
  Scenario: Request a single Bank contract should be possible only for owner, subscriber, GTD, BU GTM, BU CFO, BU representative, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "kevin@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/1"
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    GTD
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    GTM, but not right BU
    Given I authenticate as the intranet user "user-gtm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/1"
    Then the response status code should be 403
#    GTM right BU
    Given I authenticate as the intranet user "user-gtm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/1"
    Then the response status code should be 403
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"

##    IT Scenarii
  Scenario: Request a single IT contract should be possible only for owner, subscriber, role CIO, MISM of the right BU, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/2"
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    CIO
    Given I authenticate as the intranet user "user-cio@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    MISM, but not right BU
    Given I authenticate as the intranet user "user-mism-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/2"
    Then the response status code should be 403
#    MISM right BU
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"

#    CUSTOMERS Scenarii
  Scenario: Request a single CUSTOMER contract should be possible only for owner, subscriber, BU representative, LGS, Customer CP or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/3"
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/3"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/3"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    customer CP
    Given I authenticate as the intranet user "user-sales@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/3"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/3"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/3"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"

#    M&A Scenarii
  Scenario: Request a single M&A contract should be possible only for owner, subscriber, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/4"
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/4"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/4"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/4"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"

#    Real Estate Scenarii
  Scenario: Request a single Real Estate contract should be possible only for owner, subscriber, BU CFO, representative, LGM, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/5"
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/5"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/5"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/5"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/5"
    Then the response status code should be 403
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/5"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LGM
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/5"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/5"
    Then the response status code should be 403
#    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/5"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/5"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"

#    Vendors Scenarii
  Scenario: Request a single Vendors contract should be possible only for owner, subscriber, Vendor buyer, representative, LGM, CPO, MLM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/6"
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/6"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/6"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    MLM
    Given I authenticate as the intranet user "user-mlm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/6"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/6"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/6"
    Then the response status code should be 200
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/6"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"

#    Interco Scenarii
  Scenario: Request a single Interco contract should be possible only for owner, subscriber, representative, BU GTM, BU CFO, GTD, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/7"
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/7"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/7"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    GTD
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/7"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/7"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/7"
    Then the response status code should be 403
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/7"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/7"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"

#    Insurances, building subCategory Scenarii
  Scenario: Request a single Insurances contract should be possible only for owner, subscriber, representative, LGM, BU CFO, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/9"
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/9"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/9"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/9"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/9"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/9"
    Then the response status code should be 403
#    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/9"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/9"
    Then the response status code should be 403
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/9"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/9"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"

#    Insurances, car subCategory Scenarii
  Scenario: Request a single Insurances contract should be possible only for owner, subscriber, representative, LGM, BU CFO, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/10"
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/10"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/10"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/10"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/10"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/10"
    Then the response status code should be 403
#    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/10"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/10"
    Then the response status code should be 403
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/10"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/10"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"

#    Insurances, D&O subCategory Scenarii
  Scenario: Request a single Insurances contract should be possible only for owner, subscriber, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/11"
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/11"
    Then the response status code should be 403
#    LGM right BU
    Given I authenticate as the intranet user "user-lgm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/11"
    Then the response status code should be 403
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/11"
    Then the response status code should be 403
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"

#    Insurances, general subCategory Scenarii
  Scenario: Request a single Insurances contract should be possible only for owner, subscriber, LGM, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/12"
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/12"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/12"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/12"
    Then the response status code should be 403
#    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/12"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/12"
    Then the response status code should be 403
#    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/12"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/12"
    Then the response status code should be 403
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/12"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"

#    Insurances, aero subCategory Scenarii
  Scenario: Request a single Insurances contract should be possible only for owner, subscriber, LGM, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/13"
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/13"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/13"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/13"
    Then the response status code should be 403
#    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/13"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/13"
    Then the response status code should be 403
#    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/13"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/13"
    Then the response status code should be 403
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/13"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"

#    Insurances, worker subCategory Scenarii
  Scenario: Request a single Insurances contract should be possible only for owner, subscriber, representative, LGM, BU CFO, BU HRM, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14"
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14"
    Then the response status code should be 403
#    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14"
    Then the response status code should be 403
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    HRM, but not right BU
    Given I authenticate as the intranet user "user-hrm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14"
    Then the response status code should be 403
#    HRM right BU
    Given I authenticate as the intranet user "user-hrm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"

  Scenario: As a basic user, I can't create a contract without required data
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/contracts" with body:
    """
    {}
    """
    Then the response status code should be 422

  Scenario: a basic user could create a contract
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/contracts" with body:
    """
    {
      "shortDescription": "Contrat de prestation IT",
      "description": "Contrat concernant une prestation informatique",
      "startDate": "2025-10-01T00:00:00+00:00",
      "renewalPeriod": 12,
      "renewalUnit": "MONTH",
      "externalParty": "Société Externe SA",
      "owner": "/people/2",
      "value": 150000,
      "currency": "/finance/currencies/1",
      "subCategory": "/contract/sub_categories/1",
      "jurisdiction": "France"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should contain "This value should not be blank."
    And the JSON node "violations[1].message" should contain "This collection should contain 1 element or more."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/contracts" with body:
    """
    {
      "shortDescription": "Contrat de prestation IT",
      "description": "Contrat concernant une prestation informatique",
      "startDate": "2025-10-01T00:00:00+00:00",
      "renewalPeriod": 12,
      "renewalUnit": "MONTH",
      "externalParty": "Société Externe SA",
      "owner": "/people/2",
      "value": 150000,
      "currency": "/finance/currencies/1",
      "subCategory": "/contract/sub_categories/3",
      "jurisdiction": "France",
      "businessUnits": [
        "/business_units/18"
      ],
      "internalParty": { "user": "/people/4" }
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should contain "Customers must not be empty."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/contracts" with body:
    """
    {
      "shortDescription": "Contrat de prestation IT",
      "description": "Contrat concernant une prestation informatique",
      "startDate": "2025-10-01T00:00:00+00:00",
      "renewalPeriod": 12,
      "renewalUnit": "MONTH",
      "externalParty": "Société Externe SA",
      "owner": "/people/2",
      "value": 150000,
      "customers": ["/sales/customers/4"],
      "currency": "/finance/currencies/1",
      "subCategory": "/contract/sub_categories/3",
      "jurisdiction": "France",
      "businessUnits": [
        "/business_units/18"
      ],
      "internalParty": { "user": "/people/4" },
      "comment": "just a test"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract.json"
    And the JSON node "shortDescription" should be equal to the string "Contrat de prestation IT"
    And the JSON node "description" should be equal to the string "Contrat concernant une prestation informatique"
    And the JSON node "expirationDate" should be null
    And the JSON node "indefinitePeriodType" should be false
    And the JSON node "renewalPeriod" should be equal to 12
    And the JSON node "createdAt" should be newer than 1 minute ago
    And the JSON node "renewalUnit" should be equal to the string "MONTH"
    And the JSON node "externalParty" should be equal to the string "Société Externe SA"
    And the JSON node "createdBy.@id" should be equal to the string "/people/11"
    And the JSON node "owner.@id" should be equal to the string "/people/2"
    And the JSON node "value" should be equal to 150000
    And the JSON node "currency.name" should be equal to the string "USD"
    And the JSON node "subCategory.@id" should be equal to the string "/contract/sub_categories/3"
    And the JSON node "jurisdiction" should be equal to the string "France"
    And the JSON node "comment" should be equal to the string "just a test"

#    FULL EDIT ACCESS
  Scenario: Update contract whatever the category is should be possible only for moo, gku and chairman
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/3" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
    Given I authenticate as the intranet user "user-chairman@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/7" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
    Given I authenticate as the intranet user "user-moo-cat@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/12" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"


#    BANK Scenarii
  Scenario: Update a Bank contract should be possible only for owner, subscriber, GTD, BU GTM, BU CFO and BU representative
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/1" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/1" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    GTD
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/1" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS 1"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS 1"
#    GTM, but not right BU
    Given I authenticate as the intranet user "user-gtm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/1" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    GTM right BU
    Given I authenticate as the intranet user "user-gtm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/1" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/1" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/1" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/1" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS 2"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS 2"
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/1" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403

#    IT Scenarii
  Scenario: Update a IT contract should be possible only for owner, subscriber, role CIO, MISM of the right BU
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/2" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/2" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    CIO
    Given I authenticate as the intranet user "user-cio@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/2" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS 1"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS 1"
#    MISM, but not right BU
    Given I authenticate as the intranet user "user-mism-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/2" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    MISM right BU
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/2" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/2" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403

#    CUSTOMERS Scenarii
  Scenario: Update a CUSTOMER contract should be possible only for owner, subscriber and Customer CP
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/3" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/3" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    CP
    Given I authenticate as the intranet user "user-sales@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/3" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/3" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403

#    M&A Scenarii
  Scenario: Update a M&A contract should be possible only for owner, subscriber and LGS
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/4" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/4" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/4" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"

#    Real Estate Scenarii
  Scenario: Update a Real Estate contract should be possible only for owner, subscriber, representative and BU CFO
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/5" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/5" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/5" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/5" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/5" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/5" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403

#    Vendors Scenarii
  Scenario: Update a Vendors contract should be possible only for owner, subscriber, MLM, CPO, Vendor Buyer, representative and LGM
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/6" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/6" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/6" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    MLM
    Given I authenticate as the intranet user "user-mlm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/6" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/6" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/6" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403

#    Interco Scenarii
  Scenario: Update a Interco contract with CASH_POOLING_CONTRACT or MANAGEMENT_FEES_AGREEMENT sub category should be possible only for owner, subscriber, GTD, BU CFO, BU GTM, and representative
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/7" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/7" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/7" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    GTD
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/7" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/7" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/7" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    GTM, but not right BU
    Given I authenticate as the intranet user "user-gtm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/7" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    GTM right BU
    Given I authenticate as the intranet user "user-gtm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/7" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/7" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403

#    Interco 2 Scenarii
  Scenario: Update a Interco contract with other subCategory should be possible only for owner, subscriber, BU CFO, and representative
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/8" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/8" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/8" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    GTD
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/8" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/8" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/8" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    GTM right BU
    Given I authenticate as the intranet user "user-gtm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/8" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/8" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403

#    Insurances building subCategory Scenarii
  Scenario: Update a Insurances contract should be possible only for owner, subscriber, BU CFO, and representative
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/9" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/9" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/9" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    GTD
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/9" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/9" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/9" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    GTM right BU
    Given I authenticate as the intranet user "user-gtm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/9" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/9" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403

#    Insurances car subCategory Scenarii
  Scenario: Update a Insurances contract should be possible only for owner, subscriber, BU CFO, and representative
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/10" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/10" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/10" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    GTD
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/10" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/10" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/10" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    GTM right BU
    Given I authenticate as the intranet user "user-gtm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/10" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/10" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403

#    Insurances d&o subCategory Scenarii
  Scenario: Update a Insurances contract should be possible only for owner, subscriber and LGS
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/11" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/11" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/11" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/11" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"

#    Insurances general subCategory Scenarii
  Scenario: Update a Insurances contract should be possible only for owner, subscriber and LGS
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/12" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/12" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/12" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/12" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"

#    Insurances aero subCategory Scenarii
  Scenario: Update a Insurances contract should be possible only for owner, subscriber and LGS
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/13" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/13" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/13" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/13" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"

#    Insurances worker subCategory Scenarii
  Scenario: Update a Insurances contract should be possible only for owner, subscriber, BU CFO, BU HRM  and representative
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/14" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/14" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/14" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    GTD
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/14" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/14" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/14" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    HRM, but not right BU
    Given I authenticate as the intranet user "user-hrm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/14" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    HRM right BU
    Given I authenticate as the intranet user "user-hrm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/14" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Contrat de prestation MIS"
#    GTM right BU
    Given I authenticate as the intranet user "user-gtm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/14" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/14" with body:
    """
    {
      "shortDescription": "Contrat de prestation MIS"
    }
    """
    Then the response status code should be 403

#    BANK Scenarii
  Scenario: Update a Bank contract status with a mandatory comment should be possible only for owner, subscriber, GTD, BU GTM, BU CFO and BU representative
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/1/status" with body:
    """
    {
      "status": "ARCHIVED",
      "observationStatus": "Je change le status"
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/1/status" with body:
    """
    {
      "status": "ARCHIVED"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "observationStatus"
    And the JSON node "violations[0].message" should be equal to "This value should not be null."
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/1/status" with body:
    """
    {
      "status": "ARCHIVED",
      "observationStatus": "Je change le status"
    }
    """
    Then the response status code should be 200
    And the JSON node status should be equal to "ARCHIVED"
    And the JSON node observationStatus should be equal to "Je change le status"

#    IT Scenarii
  Scenario: Update a IT contract status with a mandatory comment should be possible only for owner, subscriber, role CIO, MISM of the right BU
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/2/status" with body:
    """
    {
      "status": "ARCHIVED",
      "observationStatus": "Je change le status"
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/2/status" with body:
    """
    {
      "status": "ARCHIVED"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "observationStatus"
    And the JSON node "violations[0].message" should be equal to "This value should not be null."
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/2/status" with body:
    """
    {
      "status": "ARCHIVED",
      "observationStatus": "Je change le status"
    }
    """
    Then the response status code should be 200
    And the JSON node status should be equal to "ARCHIVED"
    And the JSON node observationStatus should be equal to "Je change le status"

#    CUSTOMERS Scenarii
  Scenario: Update a CUSTOMER contract status with a mandatory comment should be possible only for owner, subscriber
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/3/status" with body:
    """
    {
      "status": "ARCHIVED",
      "observationStatus": "Je change le status"
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/3/status" with body:
    """
    {
      "status": "ARCHIVED"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "observationStatus"
    And the JSON node "violations[0].message" should be equal to "This value should not be null."
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/3/status" with body:
    """
    {
      "status": "ARCHIVED",
      "observationStatus": "Je change le status"
    }
    """
    Then the response status code should be 200
    And the JSON node status should be equal to "ARCHIVED"
    And the JSON node observationStatus should be equal to "Je change le status"

#    M&A Scenarii
  Scenario: Update a M&A contract status with a mandatory comment should be possible only for owner, subscriber and LGS
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/4/status" with body:
    """
    {
      "status": "ARCHIVED",
      "observationStatus": "Je change le status"
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/4/status" with body:
    """
    {
      "status": "ARCHIVED"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "observationStatus"
    And the JSON node "violations[0].message" should be equal to "This value should not be null."
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/4/status" with body:
    """
    {
      "status": "ARCHIVED",
      "observationStatus": "Je change le status"
    }
    """
    Then the response status code should be 200
    And the JSON node status should be equal to "ARCHIVED"
    And the JSON node observationStatus should be equal to "Je change le status"

#    Real Estate Scenarii
  Scenario: Update a Real Estate contract status with a mandatory comment should be possible only for owner, subscriber, representative and BU CFO
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/5/status" with body:
    """
    {
      "status": "ARCHIVED",
      "observationStatus": "Je change le status"
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/5/status" with body:
    """
    {
      "status": "ARCHIVED"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "observationStatus"
    And the JSON node "violations[0].message" should be equal to "This value should not be null."
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/5/status" with body:
    """
    {
      "status": "ARCHIVED",
      "observationStatus": "Je change le status"
    }
    """
    Then the response status code should be 200
    And the JSON node status should be equal to "ARCHIVED"
    And the JSON node observationStatus should be equal to "Je change le status"

#    Vendors Scenarii
  Scenario: Update a Vendors contract status with a mandatory comment should be possible only for owner, subscriber, MLM, CPO, Vendor Buyer, representative and BU LGM
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/6/status" with body:
    """
    {
      "status": "ARCHIVED",
      "observationStatus": "Je change le status"
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/6/status" with body:
    """
    {
      "status": "ARCHIVED"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "observationStatus"
    And the JSON node "violations[0].message" should be equal to "This value should not be null."
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/6/status" with body:
    """
    {
      "status": "ARCHIVED",
      "observationStatus": "Je change le status"
    }
    """
    Then the response status code should be 200
    And the JSON node status should be equal to "ARCHIVED"
    And the JSON node observationStatus should be equal to "Je change le status"

#    Interco Scenarii
  Scenario: Update a Interco contract status (CASH POOLING CONTRACT or MANAGEMENT FEES AGREEMENT sub category) with a mandatory comment should be possible only for owner, subscriber, GTD, BU GTM, BU CFO, and representative
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/7/status" with body:
    """
    {
      "status": "ARCHIVED",
      "observationStatus": "Je change le status"
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/7/status" with body:
    """
    {
      "status": "ARCHIVED"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "observationStatus"
    And the JSON node "violations[0].message" should be equal to "This value should not be null."
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/7/status" with body:
    """
    {
      "status": "ARCHIVED",
      "observationStatus": "Je change le status"
    }
    """
    Then the response status code should be 200
    And the JSON node status should be equal to "ARCHIVED"
    And the JSON node observationStatus should be equal to "Je change le status"

#    Interco 2 Scenarii
  Scenario: Update a Interco contract status with a mandatory comment should be possible only for owner, subscriber, BU CFO, and representative
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/8/status" with body:
    """
    {
      "status": "ARCHIVED",
      "observationStatus": "Je change le status"
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/8/status" with body:
    """
    {
      "status": "ARCHIVED"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "observationStatus"
    And the JSON node "violations[0].message" should be equal to "This value should not be null."
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/8/status" with body:
    """
    {
      "status": "ARCHIVED",
      "observationStatus": "Je change le status"
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/8/status" with body:
    """
    {
      "status": "ARCHIVED",
      "observationStatus": "Je change le status"
    }
    """
    Then the response status code should be 200
    And the JSON node status should be equal to "ARCHIVED"
    And the JSON node observationStatus should be equal to "Je change le status"

#    Insurances building subCategory Scenarii
  Scenario: Update a Insurances contract should be possible only for owner, subscriber, BU CFO, and representative
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/9/status" with body:
    """
    {
      "status": "ARCHIVED",
      "observationStatus": "Je change le status"
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/9/status" with body:
    """
    {
      "status": "ARCHIVED"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "observationStatus"
    And the JSON node "violations[0].message" should be equal to "This value should not be null."
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/9/status" with body:
    """
    {
      "status": "ARCHIVED",
      "observationStatus": "Je change le status"
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/9/status" with body:
    """
    {
      "status": "ARCHIVED",
      "observationStatus": "Je change le status"
    }
    """
    Then the response status code should be 200
    And the JSON node status should be equal to "ARCHIVED"
    And the JSON node observationStatus should be equal to "Je change le status"


  Scenario: I can't delete a contract
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/2"
    Then the response status code should be 405

#    DIVISION REPRESENTATIVE Scenarii
  Scenario: A division representative can read a contract of any category linked to their division
#    Bank
    Given I authenticate as the intranet user "user-division-representative@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/1"
    Then the response status code should be 200
#    IT
    Given I authenticate as the intranet user "user-division-representative@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/2"
    Then the response status code should be 200
#    Customer
    Given I authenticate as the intranet user "user-division-representative@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/3"
    Then the response status code should be 200
#    Real Estate
    Given I authenticate as the intranet user "user-division-representative@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/5"
    Then the response status code should be 200
#    Vendors
    Given I authenticate as the intranet user "user-division-representative@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/6"
    Then the response status code should be 200
#    Interco
    Given I authenticate as the intranet user "user-division-representative@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/7"
    Then the response status code should be 200

  Scenario: A division representative has the same edit rights as a BU representative on linked contracts
#    Bank: editable like a BU representative
    Given I authenticate as the intranet user "user-division-representative@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/1" with body:
    """
    {
      "shortDescription": "Updated by division representative"
    }
    """
    Then the response status code should be 200
    And the JSON node "shortDescription" should be equal to "Updated by division representative"
#    Real Estate: editable like a BU representative
    Given I authenticate as the intranet user "user-division-representative@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/5" with body:
    """
    {
      "shortDescription": "Updated by division representative"
    }
    """
    Then the response status code should be 200
#    Vendors: editable like a BU representative
    Given I authenticate as the intranet user "user-division-representative@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/6" with body:
    """
    {
      "shortDescription": "Updated by division representative"
    }
    """
    Then the response status code should be 200
#    Interco: editable like a BU representative
    Given I authenticate as the intranet user "user-division-representative@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/7" with body:
    """
    {
      "shortDescription": "Updated by division representative"
    }
    """
    Then the response status code should be 200
#    IT: not editable (a BU representative cannot edit IT contracts either)
    Given I authenticate as the intranet user "user-division-representative@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/2" with body:
    """
    {
      "shortDescription": "Updated by division representative"
    }
    """
    Then the response status code should be 403
#    Customer: not editable (a BU representative cannot edit Customer contracts either)
    Given I authenticate as the intranet user "user-division-representative@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contracts/3" with body:
    """
    {
      "shortDescription": "Updated by division representative"
    }
    """
    Then the response status code should be 403
