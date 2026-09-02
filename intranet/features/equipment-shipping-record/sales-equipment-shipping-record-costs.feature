Feature: Sales / Equipment Shipping Record Cost

  Scenario: As a superuser user, test that I can add ESR cost
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/equipment_shipping_records/1/costs/add"
    Then the response status code should be 200
    And I fill in the following:
      | esrcForm[costDate] | 06/01/2028 |
      | esrcForm[type] | Customs duties |
      | esrcForm[description] | Rendez moi le flooze |
      | esrcForm[currency] | /finance/currencies/1 |
      | esrcForm[price] | 120 |
    And press "esrcForm_submit"
    Then the response status code should be 200
    And I should be on "/sales/equipment_shipping_records/1/show"
    And I should see "Cost has been successfully saved"

  Scenario: As a superuser user, test that I can edit ESR cost
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/equipment_shipping_records/1/costs/1/edit"
    Then the response status code should be 200
    And I fill in the following:
      | esrcForm[costDate] | 06/01/2029 |
      | esrcForm[type] | Others |
      | esrcForm[description] | La moula je veux |
      | esrcForm[currency] | /finance/currencies/2 |
      | esrcForm[price] | 30 |
    And press "esrcForm_submit"
    Then the response status code should be 200
    And I should be on "/sales/equipment_shipping_records/1/show"
    And I should see "Cost has been successfully saved"

  Scenario: As a superuser user, test that I can delete ESR cost
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/equipment_shipping_records/1/costs/1/delete"
    Then the response status code should be 200
    And I should be on "/sales/equipment_shipping_records/1/show"
    And I should see "Cost deleted"
