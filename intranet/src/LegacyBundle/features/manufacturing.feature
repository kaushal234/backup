Feature: When a manufacturing legacy page has been migrated, its URL is redirected to the new Symfony URL

  Scenario: Download engineering drawing zip URL in legacy is redirected to the Symfony version
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&m[2]=ZipAllDiagram&erp=500&project=&pn=1051213&date=2022-08-24"
    Then I should be on "/manufacturing/engineering/zip/500/1051213/2022-08-24"
    And the response status code should be 200
    Then I should see response headers "content-type" with "application/zip"
    And I should see response headers "content-disposition" with 'inline; filename=1051213.zip'
