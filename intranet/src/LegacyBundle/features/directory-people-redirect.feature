Feature: The directory legacy pages should be redirected

  Scenario: The directory should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/index.php"
    Then I should not be on a legacy page
    And I should be on the exact url "/directory"

  Scenario: The directory should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/index.php?m[0]=outPhoto&id=2358"
    Then I should not be on a legacy page
    And I should be on the exact url "/directory/people/11/show"
    When I go to "/directory/index.php?m[0]=outPhoto&id=326562"
    Then the response status code should be 404
    When I go to "/directory/index.php?m[0]=people&m[1]=view&m[2]=photo&m[3]=out&id=2358"
    Then I should not be on a legacy page
    And I should be on the exact url "/directory/people/11/show"
    When I go to "/directory/index.php?m[0]=people&m[1]=view&m[2]=log&id=2358"
    Then I should not be on a legacy page
    And I should be on the exact url "/directory/people/11/show"
    When I go to "/directory/index.php?m[0]=people&m[1]=view&m[2]=photo&m[3]=edit&id=2358"
    Then I should not be on a legacy page
    And I should be on the exact url "/directory/people/11/edit"
    When I go to "/directory/index.php?m[0]=people&m[1]=view&m[2]=photo&m[3]=delete&id=2358"
    Then I should not be on a legacy page
    And I should be on the exact url "/directory/people/11/edit"
    When I go to "/directory/index.php?m[0]=people&m[1]=form&m[2]=byNumber"
    Then I should not be on a legacy page
    And I should be on the exact url "/directory/people/search/byid"
    When I go to "/directory/index.php?m[0]=people&m[1]=listing&m[2]=search"
    Then I should not be on a legacy page
    And I should be on the exact url "/directory/people/search/byname"
    When I go to "/directory/index.php?m[0]=people&m[1]=form&m[2]=search"
    Then I should not be on a legacy page
    And I should be on the exact url "/directory/people/search/byname"
    When I go to "/directory/index.php?m[0]=people&m[1]=view&m[2]=outVcard&id=2358"
    Then I should not be on a legacy page
    And I should be on the exact url "/directory/people/11/vcard"
    When I go to "/directory/index.php?m[0]=entry&m[1]=view&m[2]=outVcard&id=2358"
    Then I should not be on a legacy page
    And I should be on the exact url "/directory/people/11/vcard"
    When I go to "/directory/index.php?m[0]=people&m[1]=view&m[2]=subordinates&id=2358"
    Then I should not be on a legacy page
    And I should be on the exact url "/directory/people/11/team-members"
    When I go to "/directory/index.php?m[0]=people&m[1]=view&m[2]=groups&id=2358"
    Then I should not be on a legacy page
    And I should be on the exact url "/directory/people/11/acls"
    When I go to "/directory/index.php?m[0]=people&m[1]=view&m[2]=groups&m[3]=add&id=2358"
    Then I should not be on a legacy page
    And I should be on the exact url "/directory/people/11/acls/add"
    When I go to "/directory/index.php?m[0]=people&m[1]=view&m[2]=groups&m[3]=import&id=2358"
    Then I should not be on a legacy page
    And I should be on the exact url "/directory/people/11/acls/import"
    When I go to "/directory/index.php?m[0]=people&m[1]=download"
    Then I should not be on a legacy page
    And I should be on the exact url "/directory/people/download"
