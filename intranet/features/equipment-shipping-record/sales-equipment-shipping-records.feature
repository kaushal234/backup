Feature: Sales / Equipment Shipping Records

  Scenario: As a basic user, I can access to esr homepage
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/equipment_shipping_records"
    And I should see an "a[href$='/sales/equipment_shipping_records']" element
    Then the response status code should be 200
    And I should see "Latest Equipment Shipping Records"
    And I should see "ESR by SSO by Status"
    And I should see "ESR by Manufacturer Factory by Status"

  Scenario: As a basic user, test that I can filter esr
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/equipment_shipping_records"
    Then the response status code should be 200
    Then I select "PENDING" from "status[]"
    Then I select "EXW - EXW, Ex Works" from "incoterm"
    Then I select "AIR" from "modality"
    Then I select "location_sso" from "sso"
    Then I select "Amazon" from "forwarder"
    Then I select "Amazon" from "carrier"
    Then I fill in "serialNumber" with "T56412"
    Then I select "YES" from "shipAuthorization"
    Then I fill in "loadingPlace" with "Zir Containar"
    Then I fill in "departurePlace" with "Sherbrooke du calice"
    Then I fill in "arrivalPlace" with "Montréal Tarbar naque"
    And press "filter"
    Then the response status code should be 200
    And I should be on "/sales/equipment_shipping_records"
    And I should see "Filtered Equipment Shipping Records"

  Scenario: Quick access should work with Id and legacyId
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/equipment_shipping_records"
    And I fill in the following:
      | by_id[id] | 1 |
    And press "id-quick-access-submit"
    Then I should be on "/sales/equipment_shipping_records/1/show"
    And the response status code should be 200
    Then I go to "/sales/equipment_shipping_records"
    And I fill in the following:
      | app_legacy_id_search[legacyId] | 718 |
    And press "legacy-id-quick-access-submit"
    Then I should be on "/sales/equipment_shipping_records/2/show"
    And the response status code should be 200

  Scenario: As a basic user, test that i'm allowed to see esr detail
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/equipment_shipping_records/1/show"
    Then the response status code should be 200
    And I should not see "CLOSED"

  Scenario: As an allowed user, test that I can add ESR
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/equipment_shipping_records/add"
    Then I select "AIR" from "esrForm[modality]"
    Then I select "EXW - EXW, Ex Works" from "esrForm[incoterm]"
    Then I select "/locations/23" from "esrForm[sso]"
    Then I select "/sales/customers/10" from "esrForm[customer]"
    Then I select "Amazon" from "esrForm[forwarder]"
    Then I select "Amazon" from "esrForm[carrier]"
    Then I fill in "esrForm[loadingPlace]" with "Zir Containar"
    Then I fill in "esrForm[departurePlace]" with "Sherbrooke du calice"
    Then I fill in "esrForm[arrivalPlace]" with "Montréal Tarbar naque"
    Then I fill in "esrForm[notes]" with "J'adore le syrop d'érable et Céline"
    And press "esrForm[submit]"
    Then the response status code should be 200
    And I should see "ESR has been successfully saved"
    And I should be on "/sales/equipment_shipping_records/5/show"

  Scenario: As an allowed user, test that I can edit ESR
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/equipment_shipping_records/4/edit"
    Then the response status code should be 200
    Then I select "/locations/28" from "esrForm[sso]"
    Then I select "/sales/incoterms/3" from "esrForm[incoterm]"
    Then I select "ROAD" from "esrForm[modality]"
    Then I select "La Poste" from "esrForm[forwarder]"
    Then I select "La Poste" from "esrForm[carrier]"
    Then I fill in "esrForm[loadingPlace]" with "Le port d'amsterdam où il y a des marins qui chantent"
    Then I fill in "esrForm[departurePlace]" with "Le Havre gris"
    Then I fill in "esrForm[arrivalPlace]" with "Au Mordpr"
    Then I fill in "esrForm[notes]" with "Ne pas oublier de faire appel aux aigles"
    And press "esrForm[submit]"
    Then the response status code should be 200
    And I should be on "/sales/equipment_shipping_records/4/show"

  Scenario: As an allowed user, I can manipulate statuses
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/equipment_shipping_records/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/sales/equipment_shipping_records/1/status/CLOSED']" element
    When I go to "/sales/equipment_shipping_records/1/status/CLOSED"
    Then the response status code should be 200
    And I should be on "/sales/equipment_shipping_records/1/show"
    And I should see "CLOSED"

  Scenario: As a superuser user, test that I can delete ESR
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/equipment_shipping_records/3/delete"
    Then the response status code should be 200
    And I should be on "/sales/equipment_shipping_records"
    And I should see "Equipment shipping record deleted"

  Scenario: As a basic user, test that i'm allowed to see planning of ESR
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/equipment_shipping_records/planning"
    Then the response status code should be 200
    Then I select "location_factory_400" from "smw_filter[location]"
    And I should see "Planning"

  Scenario: As an allowed user, test that i'm allowed to see dashboard of ESR
    Given I authenticate as "user-psm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/equipment_shipping_records/dashboard"
    Then the response status code should be 200
    Then I select "location_factory_400" from "smw_filter[location]"
    And I go to "/sales/equipment_shipping_records/dashboard"

  @javascript
  Scenario: As an allowed user, I cannot add an ESR when the selected ER is already linked to another open ESR and the customer does not match the ER buyer or end user
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/equipment_shipping_records/add"
    Then I select "AIR" from "esrForm[modality]"
    Then I select "EXW - EXW, Ex Works" from "esrForm[incoterm]"
    Then I select "/locations/23" from "esrForm[sso]"
    Then I select "/sales/customers/10" from "esrForm[customer]"
    When I click on the 1st ".btn[data-action='form-collection#addCollectionElement']" element
    And I select "/equipment_records/12" from "esrForm[equipmentShippingRecordLines][0][equipmentRecord]"
    And press "esrForm[submit]"
    Then I should be on "/sales/equipment_shipping_records/add"
    And I wait until I see "ESR has been not saved"
    And I wait until I see "Serial number PETER cannot be assigned to customer customer_10 because this customer is neither the Buyer nor the End User of this Equipment Record."
    And I wait until I see "Serial number PETER already belongs to another open Equipment Shipping Record: 4. This Equipment Record must be removed from one of the open ESR before saving."
