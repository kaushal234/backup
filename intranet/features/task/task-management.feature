Feature: Tasks Management

  Scenario: As a user, I should see My Task Management from TTS on different modules
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/"
    Then the response status code should be 200
    And I should see "tasks management"
    And I should see "Migrated Tasks"
    And I should see "MIS"
    And I should see an "a[href$='/tasks/search?filter_task%5Bassignee%5D%5Bvalue%5D%5B0%5D=%2Fpeople%2F13&filter_task%5Bmodule%5D%5Bvalue%5D%5B0%5D=%2Fmodules%2F13']" element
    And I should see "TTS2"
    And I should see an "a[href$='/mis/trouble-tickets?filter_trouble_ticket%5Bassignee%5D%5Bvalue%5D%5B0%5D=%2Fpeople%2F13&filter_trouble_ticket%5Bstatus%5D%5Bvalue%5D%5B0%5D=PENDING&filter_trouble_ticket%5Bstatus%5D%5Bvalue%5D%5B1%5D=IN%20PROGRESS&filter_trouble_ticket%5Bstatus%5D%5Bvalue%5D%5B2%5D=AWAITING%20USER&filter_trouble_ticket%5Bstatus%5D%5Bvalue%5D%5B3%5D=SOLUTION%20PROPOSED&filter_trouble_ticket%5Bstatus%5D%5Bvalue%5D%5B4%5D=PENDING%20MOO%2FGKU&filter_trouble_ticket%5Bstatus%5D%5Bvalue%5D%5B5%5D=MOO%2FGKU%20SOLUTION%20PROPOSED&filter_trouble_ticket%5Bstatus%5D%5Bvalue%5D%5B6%5D=MOO%2FGKU%20AWAITING%20USER']" element
    When I go to "/mis/trouble-tickets?filter_trouble_ticket%5Bassignee%5D%5Bvalue%5D%5B0%5D=%2Fpeople%2F13"
    And I should see "TTS"
    When I go to "/tasks/search?filter_task%5Bassignee%5D%5Bvalue%5D%5B0%5D=%2Fpeople%2F13&filter_task%5Bmodule%5D%5Bvalue%5D%5B0%5D=%2Fmodules%2F13"
    And I should see "Tasks"
    And I should see an "a[href$='/tasks/9/show']" element
    When I go to "/tasks?filter_task%5Bassignee%5D%5Bvalue%5D%5B0%5D=%2Fpeople%2F13"
    And I should see "My tasks and trouble tickets"
    And I should see an "a[href$='/mis/trouble-tickets/3/show']" element
    And I should see an "a[href$='/tasks/9/show']" element

  Scenario: As a basic user, I should see My Task Management from Derogation on different modules
    Given I authenticate as "user-qam@tld.fr" with "P@ssw0rd15chars"
    When I go to "/"
    And I should see "Other Modules"
    And I should see "DEROGATION"
    And I should see an "a[href$='/quality/derogations?assignee=%2Fpeople%2F29']" element
    When I go to "/quality/derogations?assignee=%2Fpeople%2F29"
    And I should see "Filtered derogations"
