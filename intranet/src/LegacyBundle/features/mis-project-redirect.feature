Feature: MIS Project legacy pages should be redirected

  Scenario: MIS project pages should be redirected
    Given I authenticate as "user-cio@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=forms&m[2]=create"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects/add"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=forms&m[2]=byNumber"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=listing&m[2]=timesheetsIT"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=listing&m[2]=timekeeping"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=listing&m[2]=internal"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=listing&m[2]=closed"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=listing&m[2]=mis"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=gantt"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=gantt2"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=map"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=view&m[2]=reports&id=1"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects/1/show"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=view&m[2]=members&id=1"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects/1/show"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=view&m[2]=changeif&id=1"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects/1/show"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=view&m[2]=resc&id=1"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects/1/show"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=view&m[2]=changeStatus&id=1"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects/1/show"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=view&m[2]=delete&id=1"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects/1/show"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=view&m[2]=log&id=1"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects/1/show"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=view&m[2]=files&id=1"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects/1/show"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=view&m[2]=notes&id=1"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects/1/show"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=view&m[2]=conclusion&id=1"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects/1/show"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=view&m[2]=timesheets&id=1"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects/1/show"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=view&m[2]=tasks&id=1"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects/1/show"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=view&id=1"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/projects/1/show"
