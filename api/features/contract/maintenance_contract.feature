Feature: Test maintenance Contract

  Scenario: Request all maintenance contracts without being authenticated
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/maintenance_contracts"
    Then the response status code should be 401

  Scenario: Request all maintenance contracts
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/maintenance_contracts"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/maintenance_contract/schemas/maintenance_contracts.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Support\MaintenanceContract" is exposed on the API
    Then the filter "equipmentRecords.legacyId" should be available and its type should be "int"
    And the filter "equipmentRecords.serialNumber" should be available and its type should be "string"
    And the filter "equipmentRecords" should be available and its type should be "string"
    And the filter "buyer" should be available and its type should be "string"
    And the filter "endUser" should be available and its type should be "string"
    And the filter "description" should be available and its type should be "string"
    And the filter "startDate[after]" should be available and its type should be "DateTimeInterface"
    And the filter "expirationDate[after]" should be available and its type should be "DateTimeInterface"

  Scenario: Request a single maintenance contract
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/maintenance_contracts/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/maintenance_contract/schemas/maintenance_contract.json"

  Scenario: Request all maintenance contracts linked to the XU (enduser)
    Given I authenticate as the extranet user "contract-enduser-extra@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/maintenance_contracts"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/maintenance_contract/schemas/maintenance_contracts_xu.json"
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0].id" should be equal to 1

  Scenario: Request a maintenance contracts linked to the XU (enduser)
    Given I authenticate as the extranet user "contract-enduser-extra@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/maintenance_contracts/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/maintenance_contract/schemas/maintenance_contract_xu.json"

  Scenario: Request a maintenance contracts not linked to the XU (enduser)
    Given I authenticate as the extranet user "contract-enduser-extra@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/maintenance_contracts/2"
    Then the response status code should be 403

  Scenario: Request all maintenance contracts linked to the XU (buyer)
    Given I authenticate as the extranet user "contract-buyer-extra@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/maintenance_contracts"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/maintenance_contract/schemas/maintenance_contracts_xu.json"
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0].id" should be equal to 2

  Scenario: Request a maintenance contracts linked to the XU (buyer)
    Given I authenticate as the extranet user "contract-buyer-extra@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/maintenance_contracts/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/maintenance_contract/schemas/maintenance_contract_xu.json"

  Scenario: Request a maintenance contracts not linked to the XU (buyer)
    Given I authenticate as the extranet user "contract-buyer-extra@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/maintenance_contracts/1"
    Then the response status code should be 403

  Scenario: Create maintenance contracts without perms
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/maintenance_contracts" with the body "tests/fixtures/json/maintenance_contract/dummies/post.json"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/maintenance_contracts" with the body "tests/fixtures/json/maintenance_contract/dummies/post.json"
    Then the response status code should be 403

  Scenario: Create maintenance contracts
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/maintenance_contracts" with the body "tests/fixtures/json/maintenance_contract/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/maintenance_contract/schemas/maintenance_contract.json"
    And the JSON node "representative.@id" should be equal to the string "/people/12"
    And the JSON node "description" should be equal to the string "decontract"
    And the JSON node "equipmentRecords" should have 3 elements
    And the JSON node "equipmentRecords[0].@id" should be equal to the string "/equipment_records/1"
    And the JSON node "endUserRepresentatives" should have 2 elements
    And the JSON node "endUserRepresentatives[0].@id" should be equal to the string "/sales/extranet_users/251"
    And the JSON node "buyerRepresentatives" should have 3 elements
    And the JSON node "buyerRepresentatives[0].@id" should be equal to the string "/sales/extranet_users/253"
    And the JSON node "buyer.@id" should be equal to the string "/sales/customers/1"
    And the JSON node "endUser.@id" should be equal to the string "/sales/customers/36"

  Scenario: Update maintenance contract without perms should fail
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/maintenance_contracts/2" with the body "tests/fixtures/json/maintenance_contract/dummies/put.json"
    Then the response status code should be 403
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/maintenance_contracts/2" with the body "tests/fixtures/json/maintenance_contract/dummies/put.json"
    Then the response status code should be 403

  Scenario: Update maintenance contracts
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    # We are updating the newly created
    When I send a "PUT" request to "/maintenance_contracts/2" with the body "tests/fixtures/json/maintenance_contract/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/maintenance_contract/schemas/maintenance_contract.json"
    And the JSON node "representative.@id" should be equal to the string "/people/11"
    And the JSON node "description" should be equal to the string "test"
    And the JSON node "equipmentRecords" should have 1 elements
    And the JSON node "equipmentRecords[0].@id" should be equal to the string "/equipment_records/5"
    And the JSON node "endUserRepresentatives" should have 1 elements
    And the JSON node "endUserRepresentatives[0].@id" should be equal to the string "/sales/extranet_users/230"
    And the JSON node "buyerRepresentatives" should have 2 elements
    And the JSON node "buyerRepresentatives[0].@id" should be equal to the string "/sales/extranet_users/231"
    And the JSON node "buyerRepresentatives[0].@id" should be equal to the string "/sales/extranet_users/231"
    # buyer and endUser shouldn't be updated
    And the JSON node "buyer.@id" should be equal to the string "/sales/customers/1"
    And the JSON node "endUser.@id" should be equal to the string "/sales/customers/36"
