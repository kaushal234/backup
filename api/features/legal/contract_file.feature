Feature: Test Contract File
  @resetFileTable
#    BANK Scenarii
  Scenario: Upload a Bank contract attached file should be possible only for owner, subscriber, GTD, BU GTM, BU CFO, BU representative, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/1/files" with file "file" "file.doc"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/1/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
    #    GTD
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/1/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    GTM, but not right BU
    Given I authenticate as the intranet user "user-gtm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/1/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    GTM right BU
    Given I authenticate as the intranet user "user-gtm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/1/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/1/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/1/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/1/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/1/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/1/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201

#    IT Scenarii
  Scenario: Upload a IT contract attached file should be possible only for owner, subscriber, role CIO, MISM of the right BU, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/2/files" with file "file" "file.doc"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/2/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
    #    CIO
    Given I authenticate as the intranet user "user-cio@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/2/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    MISM, but not right BU
    Given I authenticate as the intranet user "user-mism-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/2/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    MISM right BU
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/2/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/2/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/2/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201


#    CUSTOMERS Scenarii
  Scenario: Upload a CUSTOMER contract attached file should be possible only for owner, subscriber, BU representative, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/3/files" with file "file" "file.doc"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/3/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/3/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    customer CP
    Given I authenticate as the intranet user "user-sales@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/3/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/3/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/3/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201

#    M&A Scenarii
  Scenario: Upload a M&A contract attached file should be possible only for owner, subscriber, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/4/files" with file "file" "file.doc"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/4/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/4/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/4/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201

#    Real Estate Scenarii
  Scenario: Upload a Real Estate contract attached file should be possible only for owner, subscriber, BU CFO, representative, LGM, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/5/files" with file "file" "file.doc"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/5/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/5/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/5/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/5/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/5/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/5/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/5/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/5/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/5/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201

#    Vendors Scenarii
  Scenario: Upload a Vendors contract attached file should be possible only for owner, subscriber, Vendor buyer, representative, LGM, CPO, MLM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/6/files" with file "file" "file.doc"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/6/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/6/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    MLM
    Given I authenticate as the intranet user "user-mlm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/6/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/6/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/6/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/6/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201

#    Interco Scenarii
  Scenario: Upload a Interco contract attached file should be possible only for owner, subscriber, GTD, BU GTM, BU CFO, and representative
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/7/files" with file "file" "file.doc"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/7/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/7/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    GTD
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/7/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/7/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/7/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/7/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/7/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201


#    Insurances, building subCategory Scenarii
  Scenario: Upload a Insurances contract attached file should be possible only for owner, subscriber, representative, LGM, BU CFO, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/9/files" with file "file" "file.doc"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/9/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/9/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/9/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/9/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/9/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/9/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/9/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/9/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/9/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201

#    Insurances, car subCategory Scenarii
  Scenario: Upload a Insurances contract attached file should be possible only for owner, subscriber, representative, LGM, BU CFO, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/10/files" with file "file" "file.doc"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/10/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/10/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/10/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/10/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/10/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/10/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/10/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/10/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/10/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201

#    Insurances, D&O subCategory Scenarii
  Scenario: Upload a Insurances contract attached file should be possible only for owner, subscriber, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/11/files" with file "file" "file.doc"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/11/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/11/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/11/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    LGM right BU
    Given I authenticate as the intranet user "user-lgm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/11/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/11/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/11/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201

#  Insurances, general subCategory Scenarii
  Scenario: Upload a Insurances contract attached file should be possible only for owner, subscriber, LGM, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/12/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/12/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/12/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/12/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/12/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/12/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/12/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/12/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/12/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201

#    Insurances, aero subCategory Scenarii
  Scenario: Upload a Insurances contract attached file should be possible only for owner, subscriber, LGM, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/13/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/13/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/13/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/13/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/13/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/13/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/13/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/13/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/13/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201

#    Insurances, worker subCategory Scenarii
  Scenario: Upload a Insurances contract attached file should be possible only for owner, subscriber, LGM, BU CFO, BU HRM, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/14/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/14/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/14/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/14/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/14/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/14/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/14/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/14/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/14/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    HRM, but not right BU
    Given I authenticate as the intranet user "user-hrm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/14/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403
#    HRM right BU
    Given I authenticate as the intranet user "user-hrm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/14/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
#    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/14/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
    
  Scenario: Upload an invalid file to a contract
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/1/files" with file "file" "image.gif"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "files: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "files"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

#    BANK Scenarii
  Scenario: Download a Bank contract attached file should be possible only for owner, subscriber, GTD, BU GTM, BU CFO, BU representative, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/1/files/1"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/1/files/1"
    Then the response status code should be 200
    #    GTD
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/1/files/1"
    Then the response status code should be 200
    #    GTM, but not right BU
    Given I authenticate as the intranet user "user-gtm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/1/files/1"
    Then the response status code should be 403
    #    GTM right BU
    Given I authenticate as the intranet user "user-gtm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/1/files/1"
    Then the response status code should be 200
    #    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/1/files/1"
    Then the response status code should be 403
    #    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/1/files/1"
    Then the response status code should be 200
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/1/files/1"
    Then the response status code should be 200
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/1/files/1"
    Then the response status code should be 200
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/1/files/1"
    Then the response status code should be 200

#    IT Scenarii
  Scenario: Download a IT contract attached file should be possible only for owner, subscriber, role CIO, MISM of the right BU, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/2/files/8"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/2/files/8"
    Then the response status code should be 200
    #    CIO
    Given I authenticate as the intranet user "user-cio@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/2/files/8"
    Then the response status code should be 200
    #    MISM, but not right BU
    Given I authenticate as the intranet user "user-mism-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/2/files/8"
    Then the response status code should be 403
    #    MISM right BU
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/2/files/8"
    Then the response status code should be 200
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/2/files/8"
    Then the response status code should be 200
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/2/files/8"
    Then the response status code should be 200

#    CUSTOMERS Scenarii
  Scenario: Download a CUSTOMER contract attached file should be possible only for owner, subscriber, BU representative, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/3/files/13"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/3/files/13"
    Then the response status code should be 200
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/3/files/13"
    Then the response status code should be 200
    #    customer CP
    Given I authenticate as the intranet user "user-sales@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/3/files/13"
    Then the response status code should be 200
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/3/files/13"
    Then the response status code should be 200
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/3/files/13"
    Then the response status code should be 200

#    M&A Scenarii
  Scenario: Download a M&A contract attached file should be possible only for owner, subscriber, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/4/files/18"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/4/files/18"
    Then the response status code should be 200
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/4/files/18"
    Then the response status code should be 200
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/4/files/18"
    Then the response status code should be 200

#    Real Estate Scenarii
  Scenario: Download a Real Estate contract attached file should be possible only for owner, subscriber, BU CFO, representative, LGM, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/5/files/21"
    Then the response status code should be 403
    #    Owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/5/files/21"
    Then the response status code should be 200
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/5/files/21"
    Then the response status code should be 200
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/5/files/21"
    Then the response status code should be 200
    #    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/5/files/21"
    Then the response status code should be 403
    #    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/5/files/21"
    Then the response status code should be 200
    #    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/5/files/21"
    Then the response status code should be 200
    #    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/5/files/21"
    Then the response status code should be 403
    #    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/5/files/21"
    Then the response status code should be 200
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/5/files/21"
    Then the response status code should be 200

#    Vendors Scenarii
  Scenario: Download a Vendors contract attached file should be possible only for owner, subscriber, Vendor buyer, representative, LGM, CPO, MLM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/6/files/28"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/6/files/28"
    Then the response status code should be 200
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/6/files/28"
    Then the response status code should be 200
    #    MLM
    Given I authenticate as the intranet user "user-mlm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/6/files/28"
    Then the response status code should be 200
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/6/files/28"
    Then the response status code should be 200
    #    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/6/files/28"
    Then the response status code should be 200
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/6/files/28"
    Then the response status code should be 200

#    Interco Scenarii
  Scenario: Download a Interco contract attached file should be possible only for owner, subscriber, GTD, BU GTM, BU CFO, and representative
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/7/files/34"
    Then the response status code should be 403
    #    Owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/7/files/34"
    Then the response status code should be 200
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/7/files/34"
    Then the response status code should be 200
    #    GTD
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/7/files/34"
    Then the response status code should be 200
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/7/files/34"
    Then the response status code should be 200
    #    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/7/files/34"
    Then the response status code should be 403
    #    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/7/files/34"
    Then the response status code should be 200
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/7/files/34"
    Then the response status code should be 200

#    Insurances, building subCategory Scenarii
  Scenario: Download a Insurances contract attached file should be possible only for owner, subscriber, representative, LGM, BU CFO, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/9/files/40"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/9/files/40"
    Then the response status code should be 200
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/9/files/40"
    Then the response status code should be 200
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/9/files/40"
    Then the response status code should be 200
    #    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/9/files/40"
    Then the response status code should be 200
    #    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/9/files/40"
    Then the response status code should be 403
    #    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/9/files/40"
    Then the response status code should be 200
    #    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/9/files/40"
    Then the response status code should be 403
    #    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/9/files/40"
    Then the response status code should be 200
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/9/files/40"
    Then the response status code should be 200

#    Insurances, car subCategory Scenarii
  Scenario: Download a Insurances contract attached file should be possible only for owner, subscriber, representative, LGM, BU CFO, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/10/files/47"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/10/files/47"
    Then the response status code should be 200
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/10/files/47"
    Then the response status code should be 200
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/10/files/47"
    Then the response status code should be 200
    #    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/10/files/47"
    Then the response status code should be 200
    #    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/10/files/47"
    Then the response status code should be 403
    #    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/10/files/47"
    Then the response status code should be 200
    #    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/10/files/47"
    Then the response status code should be 403
    #    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/10/files/47"
    Then the response status code should be 200
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/10/files/47"
    Then the response status code should be 200

#    Insurances, D&O subCategory Scenarii
  Scenario: Download a Insurances contract attached file should be possible only for owner, subscriber, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/11/files/54"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/11/files/54"
    Then the response status code should be 200
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/11/files/54"
    Then the response status code should be 200
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/11/files/54"
    Then the response status code should be 403
    #    LGM right BU
    Given I authenticate as the intranet user "user-lgm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/11/files/54"
    Then the response status code should be 403
    #    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/11/files/54"
    Then the response status code should be 403
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/11/files/54"
    Then the response status code should be 200

#  Insurances, general subCategory Scenarii
  Scenario: Download a Insurances contract attached file should be possible only for owner, subscriber, LGM, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/12/files/57"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/12/files/57"
    Then the response status code should be 200
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/12/files/57"
    Then the response status code should be 200
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/12/files/57"
    Then the response status code should be 403
    #    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/12/files/57"
    Then the response status code should be 200
    #    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/12/files/57"
    Then the response status code should be 403
    #    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/12/files/57"
    Then the response status code should be 200
    #    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/12/files/57"
    Then the response status code should be 403
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/12/files/57"
    Then the response status code should be 200

#    Insurances, aero subCategory Scenarii
  Scenario: Download a Insurances contract attached file should be possible only for owner, subscriber, LGM, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/13/files/62"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/13/files/62"
    Then the response status code should be 200
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/13/files/62"
    Then the response status code should be 200
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/13/files/62"
    Then the response status code should be 403
    #    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/13/files/62"
    Then the response status code should be 200
    #    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/13/files/62"
    Then the response status code should be 403
    #    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/13/files/62"
    Then the response status code should be 200
    #    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/13/files/62"
    Then the response status code should be 403
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/13/files/62"
    Then the response status code should be 200

#    Insurances, worker subCategory Scenarii
  Scenario: Upload a Insurances contract attached file should be possible only for owner, subscriber, LGM, BU CFO, BU HRM, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14/files/67"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14/files/67"
    Then the response status code should be 200
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14/files/67"
    Then the response status code should be 200
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14/files/67"
    Then the response status code should be 200
    #    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14/files/67"
    Then the response status code should be 200
    #    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14/files/67"
    Then the response status code should be 403
    #    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14/files/67"
    Then the response status code should be 200
    #    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14/files/67"
    Then the response status code should be 403
    #    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14/files/67"
    Then the response status code should be 200
    #    HRM, but not right BU
    Given I authenticate as the intranet user "user-hrm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14/files/67"
    Then the response status code should be 403
    #    HRM right BU
    Given I authenticate as the intranet user "user-hrm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14/files/67"
    Then the response status code should be 200
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contracts/14/files/67"
    Then the response status code should be 200

#    BANK Scenarii
  Scenario: Delete a Bank contract attached file should be possible only for owner, subscriber, GTD, BU GTM, BU CFO, BU representative, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/1/files/1"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/1/files/1"
    Then the response status code should be 204
    #    GTD
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/1/files/2"
    Then the response status code should be 204
    #    GTM, but not right BU
    Given I authenticate as the intranet user "user-gtm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/1/files/3"
    Then the response status code should be 403
    #    GTM right BU
    Given I authenticate as the intranet user "user-gtm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/1/files/3"
    Then the response status code should be 204
    #    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/1/files/4"
    Then the response status code should be 403
    #    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/1/files/4"
    Then the response status code should be 204
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/1/files/5"
    Then the response status code should be 204
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/1/files/6"
    Then the response status code should be 204
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/1/files/7"
    Then the response status code should be 204

#    IT Scenarii
  Scenario: Delete a IT contract attached file should be possible only for owner, subscriber, role CIO, MISM of the right BU, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/2/files/8"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/2/files/8"
    Then the response status code should be 204
    #    CIO
    Given I authenticate as the intranet user "user-cio@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/2/files/9"
    Then the response status code should be 204
    #    MISM, but not right BU
    Given I authenticate as the intranet user "user-mism-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/2/files/10"
    Then the response status code should be 403
    #    MISM right BU
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/2/files/10"
    Then the response status code should be 204
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/2/files/11"
    Then the response status code should be 204
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/2/files/12"
    Then the response status code should be 204

#    CUSTOMERS Scenarii
  Scenario: Delete a CUSTOMER contract attached file should be possible only for owner, subscriber, BU representative, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/3/files/13"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/3/files/13"
    Then the response status code should be 204
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/3/files/14"
    Then the response status code should be 204
    #    customer CP
    Given I authenticate as the intranet user "user-sales@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/3/files/15"
    Then the response status code should be 204
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/3/files/16"
    Then the response status code should be 204
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/3/files/17"
    Then the response status code should be 204

#    M&A Scenarii
  Scenario: Delete a M&A contract attached file should be possible only for owner, subscriber, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/4/files/18"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/4/files/18"
    Then the response status code should be 204
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/4/files/19"
    Then the response status code should be 204
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/4/files/20"
    Then the response status code should be 204

#    Real Estate Scenarii
  Scenario: Delete a Real Estate contract attached file should be possible only for owner, subscriber, BU CFO, representative, LGM, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/5/files/21"
    Then the response status code should be 403
    #    Owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/5/files/21"
    Then the response status code should be 204
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/5/files/22"
    Then the response status code should be 204
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/5/files/23"
    Then the response status code should be 204
    #    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/5/files/24"
    Then the response status code should be 403
    #    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/5/files/24"
    Then the response status code should be 204
    #    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/5/files/25"
    Then the response status code should be 204
    #    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/5/files/26"
    Then the response status code should be 403
    #    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/5/files/26"
    Then the response status code should be 204
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/5/files/27"
    Then the response status code should be 204

#    Vendors Scenarii
  Scenario: Delete a Vendors contract attached file should be possible only for owner, subscriber, Vendor buyer, representative, LGM, CPO, MLM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/6/files/28"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/6/files/28"
    Then the response status code should be 204
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/6/files/29"
    Then the response status code should be 204
    #    MLM
    Given I authenticate as the intranet user "user-mlm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/6/files/30"
    Then the response status code should be 204
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/6/files/31"
    Then the response status code should be 204
    #    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/6/files/32"
    Then the response status code should be 204
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/6/files/33"
    Then the response status code should be 204

#    Interco Scenarii
  Scenario: Delete a Interco contract attached file should be possible only for owner, subscriber, GTD, BU GTM, BU CFO, and representative
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/7/files/34"
    Then the response status code should be 403
    #    Owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/7/files/34"
    Then the response status code should be 204
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/7/files/35"
    Then the response status code should be 204
    #    GTD
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/7/files/36"
    Then the response status code should be 204
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/7/files/37"
    Then the response status code should be 204
    #    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/7/files/38"
    Then the response status code should be 403
    #    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/7/files/38"
    Then the response status code should be 204
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/7/files/39"
    Then the response status code should be 204

#    Insurances, building subCategory Scenarii
  Scenario: Delete a Insurances contract attached file should be possible only for owner, subscriber, representative, LGM, BU CFO, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/9/files/40"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/9/files/40"
    Then the response status code should be 204
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/9/files/41"
    Then the response status code should be 204
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/9/files/42"
    Then the response status code should be 204
    #    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/9/files/43"
    Then the response status code should be 204
    #    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/9/files/44"
    Then the response status code should be 403
    #    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/9/files/44"
    Then the response status code should be 204
    #    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/9/files/45"
    Then the response status code should be 403
    #    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/9/files/45"
    Then the response status code should be 204
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/9/files/46"
    Then the response status code should be 204

#    Insurances, car subCategory Scenarii
  Scenario: Download a Insurances contract attached file should be possible only for owner, subscriber, representative, LGM, BU CFO, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/10/files/47"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/10/files/47"
    Then the response status code should be 204
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/10/files/48"
    Then the response status code should be 204
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/10/files/49"
    Then the response status code should be 204
    #    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/10/files/50"
    Then the response status code should be 204
    #    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/10/files/51"
    Then the response status code should be 403
    #    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/10/files/51"
    Then the response status code should be 204
    #    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/10/files/52"
    Then the response status code should be 403
    #    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/10/files/52"
    Then the response status code should be 204
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/10/files/53"
    Then the response status code should be 204

#    Insurances, D&O subCategory Scenarii
  Scenario: Download a Insurances contract attached file should be possible only for owner, subscriber, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/11/files/54"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/11/files/54"
    Then the response status code should be 204
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/11/files/55"
    Then the response status code should be 204
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/11/files/56"
    Then the response status code should be 403
    #    LGM right BU
    Given I authenticate as the intranet user "user-lgm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/11/files/56"
    Then the response status code should be 403
    #    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/11/files/56"
    Then the response status code should be 403
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/11/files/56"
    Then the response status code should be 204

#  Insurances, general subCategory Scenarii
  Scenario: Download a Insurances contract attached file should be possible only for owner, subscriber, LGM, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/12/files/57"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/12/files/57"
    Then the response status code should be 204
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/12/files/58"
    Then the response status code should be 204
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/12/files/59"
    Then the response status code should be 403
    #    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/12/files/59"
    Then the response status code should be 204
    #    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/12/files/60"
    Then the response status code should be 403
    #    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/12/files/60"
    Then the response status code should be 204
    #    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/12/files/61"
    Then the response status code should be 403
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/12/files/61"
    Then the response status code should be 204

#    Insurances, aero subCategory Scenarii
  Scenario: Download a Insurances contract attached file should be possible only for owner, subscriber, LGM, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/13/files/62"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/13/files/62"
    Then the response status code should be 204
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/13/files/63"
    Then the response status code should be 204
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/13/files/64"
    Then the response status code should be 403
    #    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/13/files/64"
    Then the response status code should be 204
    #    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/13/files/65"
    Then the response status code should be 403
    #    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/13/files/65"
    Then the response status code should be 204
    #    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/13/files/66"
    Then the response status code should be 403
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/13/files/66"
    Then the response status code should be 204

#    Insurances, worker subCategory Scenarii
  Scenario: Upload a Insurances contract attached file should be possible only for owner, subscriber, LGM, BU CFO, BU HRM, BU LCM, LGS or any upper hierarchy member of these people
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/14/files/67"
    Then the response status code should be 403
    #    owner
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/14/files/67"
    Then the response status code should be 204
    #    LGS
    Given I authenticate as the intranet user "user-lgs@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/14/files/68"
    Then the response status code should be 204
    #    representative
    Given I authenticate as the intranet user "representative_william.adama@galactica.cap"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/14/files/69"
    Then the response status code should be 204
    #    LGM, but not right BU
    Given I authenticate as the intranet user "user-lgm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/14/files/70"
    Then the response status code should be 204
    #    LCM, but not right BU
    Given I authenticate as the intranet user "user-lcm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/14/files/71"
    Then the response status code should be 403
    #    LCM right BU
    Given I authenticate as the intranet user "user-lcm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/14/files/71"
    Then the response status code should be 204
    #    CFO, but not right BU
    Given I authenticate as the intranet user "user-cfo-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/14/files/72"
    Then the response status code should be 403
    #    CFO right BU
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/14/files/72"
    Then the response status code should be 204
    #    HRM, but not right BU
    Given I authenticate as the intranet user "user-hrm-bu@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/14/files/73"
    Then the response status code should be 403
    #    HRM right BU
    Given I authenticate as the intranet user "user-hrm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/14/files/73"
    Then the response status code should be 204
    #    Owner supervisor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contracts/14/files/74"
    Then the response status code should be 204

#    DIVISION REPRESENTATIVE Scenarii
  Scenario: A division representative can upload a file on a linked contract like a BU representative
    Given I authenticate as the intranet user "user-division-representative@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/1/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201

