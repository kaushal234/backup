Feature: The sales areas legacy pages should be redirected

  Scenario: The sales areas should be redirected
  Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
  When I go to "/sales_service/salesareas/salesareas_admin.php"
  Then I should not be on a legacy page
  And I should be on the exact url "/sales/sales-areas"
