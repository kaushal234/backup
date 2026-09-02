Feature: Some trouble ticket legacy pages should be redirected

  Scenario: Some trouble ticket pages should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=forms&m[2]=newticket"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/trouble-tickets/add"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=forms&m[2]=Comm"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/trouble-tickets"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=listing&m[2]=ticketQueues"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/trouble-tickets"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=taskListing&m[2]=byDomainCategory"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/trouble-tickets"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=taskListing&m[2]=myOpenTickets"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/trouble-tickets"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=taskListing&m[2]=allOpenTickets"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/trouble-tickets"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=taskListing&m[2]=latest"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/trouble-tickets"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=reports&m[2]=ttsKPI"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/trouble-tickets"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=reports&m[2]=closedTTS"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/trouble-tickets"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=reports&m[2]=statsByQueueDomain"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/trouble-tickets"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=reports&m[2]=taskStatsByYearByModule"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/trouble-tickets"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=reports&m[2]=TLDTaskDashboard"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/trouble-tickets"
    When I go to "/mis/mis.php?m[0]=tts&m[1]=reports&m[2]=internal"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/trouble-tickets"
    When I go to "/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=1"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/trouble-tickets/1/show"
