Feature: The iata codes legacy pages should be redirected

  Scenario: The aita codes should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/product_support/admin/airport_codes_admin.php"
    Then I should not be on a legacy page
    And I should be on the exact url "/support/iata-codes"
    When I go to "/product_support/admin/airport_codes_admin.php?mode=form_add"
    Then I should not be on a legacy page
    And I should be on the exact url "/support/iata-codes/add"
    When I go to "/product_support/admin/airport_codes_admin.php?mode=record_view&id=11715"
    Then I should not be on a legacy page
    And I should be on the exact url "/support/iata-codes/1/show"
    When I go to "/product_support/admin/airport_codes_admin.php?mode=record_view&id=1264562"
    Then the response status code should be 404
    When I go to "/product_support/admin/airport_codes_admin.php?mode=form_edit&id=11715"
    Then I should not be on a legacy page
    And I should be on the exact url "/support/iata-codes/1/edit"
