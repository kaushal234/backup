@manual
Feature: The manual legacy pages should be redirected

  Scenario: Legacy manual URL are redirected to Symfony
  Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
  When I go to "/product_support/publications/manuals_admin.php"
  Then I should not be on a legacy page
  And I should be on the exact url "/support/manuals"
  And The module should be "PUBS"
  When I go to "/product_support/index.ps.php?m[0]=publications&m[1]=manuals"
  Then I should not be on a legacy page
  And I should be on "/support/manuals"
  And The module should be "PUBS"
  When I go to "/product_support/index.ps.php?m[0]=publications&m[1]=manuals&m[2]=view&id=17327"
  Then I should not be on a legacy page
  And I should be on the exact url "/support/manuals/1/show"
  And The module should be "PUBS"
  When I go to "/pickup.php"
  Then I should not be on a legacy page
  And I should be on the exact url "/support/manuals"
  And The module should be "PUBS"
