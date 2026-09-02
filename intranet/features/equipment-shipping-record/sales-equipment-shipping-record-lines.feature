Feature: Sales / Equipment Shipping Record Line

  Scenario: As a superuser user, test that I can edit ESRL
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/equipment_shipping_records/4/edit"
    Then the response status code should be 200
    Then I select "La Poste" from "esrForm[forwarder]"
    Then I select "/equipment_records/12" from "esrForm[equipmentShippingRecordLines][0][equipmentRecord]"
    Then I fill in "esrForm[equipmentShippingRecordLines][0][estimatedPickUpDate]" with "07/01/2028"
    Then I fill in "esrForm[equipmentShippingRecordLines][0][vesselLoadingDate]" with "07/01/2028"
    Then I fill in "esrForm[equipmentShippingRecordLines][0][estimatedArrivalDate]" with "07/01/2028"
    Then I fill in "esrForm[equipmentShippingRecordLines][0][actualArrivalDate]" with "07/01/2028"
    Then I fill in "esrForm[equipmentShippingRecordLines][0][truckType]" with "GROS CAMION"
    And press "esrForm[submit]"
    Then the response status code should be 200
    And I should be on "/sales/equipment_shipping_records/4/show"
    And I should see "ESR has been successfully saved"

  Scenario: A user authorized to edit an ESR line but cannot change pickup confirmation
    Given I authenticate as "user-sa@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/equipment_shipping_records/4/edit"
    Then the response status code should be 200
    Then I select "La Poste" from "esrForm[forwarder]"
    Then I should see an "input[id='esrForm_equipmentShippingRecordLines_0_estimatedPickUpDateConfirmation'][disabled='disabled']" element
    And I should see "PSM team only"
    Then I fill in "esrForm[equipmentShippingRecordLines][0][truckType]" with "Velo"
    And press "esrForm[submit]"
    Then the response status code should be 200
    And I should be on "/sales/equipment_shipping_records/4/show"
    And I should see "ESR has been successfully saved"

  Scenario: A PSE user can change pickup confirmation from the ESR edit form
    Given I authenticate as "user-pse@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/equipment_shipping_records/4/edit"
    Then the response status code should be 200
    Then I select "La Poste" from "esrForm[forwarder]"
    And I check "esrForm[equipmentShippingRecordLines][0][estimatedPickUpDateConfirmation]"
    And press "esrForm[submit]"
    Then the response status code should be 200
    And I should be on "/sales/equipment_shipping_records/4/show"
    And I should see "ESR has been successfully saved"

  Scenario: As a superuser user, test that I can delete ESR line
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/equipment_shipping_records/4/lines/1/delete"
    Then the response status code should be 200
    And I should be on "/sales/equipment_shipping_records/4/show"
    And I should see "Line deleted"
