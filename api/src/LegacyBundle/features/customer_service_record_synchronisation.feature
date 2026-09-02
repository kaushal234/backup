Feature: Test CSR double write API
  Scenario: Create a CSR in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/commissioning_customer_service_records" with body:
    """
    {
      "equipmentRecord": "/equipment_records/15",
      "airport": "/airports/62",
      "title": "Une petite histoire",
      "description": "Voici Gali l alligator, il arrive et sème la mort, éventre les oisillons et torture les papillons"
    }
    """
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "csr"
    And the column "dt" from the "csr" legacy table has been inserted
    And the column "short_desc" from the "csr" legacy table has been inserted with string "Une petite histoire"
    And the column "int_desc" from the "csr" legacy table has been inserted with string "Voici Gali l alligator, il arrive et sème la mort, éventre les oisillons et torture les papillons"
    And the column "status" from the "csr" legacy table has been inserted with string "PENDING"
    And the column "apc" from the "csr" legacy table has been inserted with string "CDG"
    And the column "sso_id" from the "csr" legacy table has been inserted with integer 74
    And the column "parent_id" from the "csr" legacy table has been inserted with integer 37469
    And the column "entered_by" from the "csr" legacy table has been inserted with integer 2359
    And the column "module" from the "mod_logs" legacy table has been inserted with string "CSR"

  Scenario: Update CSR in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/commissioning_customer_service_records/6" with body:
    """
    {
      "airport": "/airports/111",
      "description": "j ai pas la ref"
    }
    """
    Then the response status code should be 200
    And the column "int_desc" from the "csr" legacy table has been updated with string "j ai pas la ref"
    And 4 insert queries has been executed on the legacy table "mod_logs"
    And the column "airport_code" from the "service" legacy table has been updated with string "PLO"
    And the column "del_ctry" from the "service" legacy table has been updated with string "Mandalore"

  Scenario: Add survey to commissioning CSR
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/commissioning_customer_service_records/5" with body:
    """
    {
      "@id": "/service/commissioning_customer_service_records/5",
      "answerSurveyCustomerServiceRecords": [
          {
              "questionSurveyCustomerServiceRecord": "/service/question_survey_customer_service_records/1",
              "answer": "2",
              "comment": "Aspect"
          },
          {
              "questionSurveyCustomerServiceRecord": "/service/question_survey_customer_service_records/2",
              "answer": "3",
              "comment": "Conformity"
          },
          {
              "questionSurveyCustomerServiceRecord": "/service/question_survey_customer_service_records/3",
              "answer": "4",
              "comment": "Operational"
          }
      ],
      "type": "commissioning"
    }
    """
    Then the response status code should be 200
    And the column "parent_id" from the "mod_kpi" legacy table has been inserted with integer 58646
    And the column "comments" from the "mod_kpi" legacy table has been inserted with string "Aspect"
    And the column "key1" from the "mod_kpi" legacy table has been inserted with string "aspect"
    And the column "val" from the "mod_kpi" legacy table has been inserted with string "2"
    And the column "module" from the "mod_kpi" legacy table has been inserted with string "CSR"
    And the column "parent_id" from the "mod_kpi" legacy table has been inserted with integer 58646
    And the column "comments" from the "mod_kpi" legacy table has been inserted with string "Conformity"
    And the column "key1" from the "mod_kpi" legacy table has been inserted with string "conformity"
    And the column "val" from the "mod_kpi" legacy table has been inserted with string "3"
    And the column "module" from the "mod_kpi" legacy table has been inserted with string "CSR"
    And the column "parent_id" from the "mod_kpi" legacy table has been inserted with integer 58646
    And the column "comments" from the "mod_kpi" legacy table has been inserted with string "Operational"
    And the column "key1" from the "mod_kpi" legacy table has been inserted with string "operational"
    And the column "val" from the "mod_kpi" legacy table has been inserted with string "4"
    And the column "module" from the "mod_kpi" legacy table has been inserted with string "CSR"

  Scenario: Update survey to commissioning CSR
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/commissioning_customer_service_records/5" with body:
    """
    {
        "@id": "/service/commissioning_customer_service_records/5",
        "answerSurveyCustomerServiceRecords": [
            {
                "@id": "/service/answer_survey_customer_service_records/commissioningCustomerServiceRecord=5;questionSurveyCustomerServiceRecord=1",
                "@type": "AnswerSurveyCustomerServiceRecord",
                "commissioningCustomerServiceRecord": "/service/commissioning_customer_service_records/5",
                "questionSurveyCustomerServiceRecord": "/service/question_survey_customer_service_records/1",
                "answer": "false",
                "comment": "Change comment",
                "legacyId": 14714
            }
        ]
      }
    """
    Then the response status code should be 200
    And the column "comments" from the "mod_kpi" legacy table has been updated with string "Change comment"
    And the column "val" from the "mod_kpi" legacy table has been updated
    And the column "module" from the "mod_kpi" legacy table has been updated with string "CSR"

  Scenario: Complete a commissioning CSR update ER date commissioning
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/interventions/2" with body:
    """
    {
      "endedAt": "2026-07-09 12:00:00",
      "status": "SOLVED"
    }
    """
    Then the response status code should be 200
    And the column "dt_commissioned" from the "service" legacy table has been updated with string "2026-07-09"

  Scenario: Complete a commissioning CSR in a single request (PENDING to SOLVED) updates ER date commissioning
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/interventions/4" with body:
    """
    {
      "startedAt": "2026-08-01 10:00:00",
      "endedAt": "2026-08-01 12:00:00",
      "status": "SOLVED"
    }
    """
    Then the response status code should be 200
    And the column "dt_commissioned" from the "service" legacy table has been updated with string "2026-08-01"
