import { postTocAndCsrWithDummyData } from "../api/postTocWithCsr";
import { formRichTextField } from "../common/formRichTextField";
import { formSingleSelectDropdown } from "../common/formSingleSelectDropdown";
import { API_CALL } from "../constants/api";
import { TEST_USER_CSM } from "../constants/constants";
import { TEXT } from "../constants/strings";

const checkUnitOperationalStatusPopUp = () => {
  cy.waitForApiWithLoader(
    API_CALL.getUnitOperationalStatusList,
    200,
    null,
    true
  );

  cy.getBySel("toc-detail-main-info-unit-operational-status-value").click();

  cy.getBySel("unit-operational-status-popup-heading").contains(
    TEXT.tocDetail.unitOperationalStatusPopup.heading
  );

  formSingleSelectDropdown({
    fieldName: "update-unit-operational-status-form-unit-operational-status",
    checkLabel:
      TEXT.tocDetail.unitOperationalStatusPopup.unitOperationalStatus.title,
    checkValue:
      TEXT.tocDetail.unitOperationalStatusPopup.unitOperationalStatus
        .autoFilledValue,
    clear: true,
    requiredError:
      TEXT.tocDetail.unitOperationalStatusPopup.unitOperationalStatus.error,
    selectIndex: 1,
  });

  cy.getBySel("update-unit-operational-status-form-submit").click();

  formSingleSelectDropdown({
    fieldName: "update-unit-operational-status-form-ifactor",
    checkLabel: TEXT.tocDetail.unitOperationalStatusPopup.ifactor.title,
    checkValue:
      TEXT.tocDetail.unitOperationalStatusPopup.ifactor.autoFilledValue,
    clear: true,
    requiredError:
      TEXT.tocDetail.unitOperationalStatusPopup.ifactor.error.required,
    selectIndex: 0,
    valueError: TEXT.tocDetail.unitOperationalStatusPopup.ifactor.error.mcf,
  });

  formSingleSelectDropdown({
    fieldName: "update-unit-operational-status-form-ifactor",
    selectIndex: 1,
  });

  cy.getBySel("update-unit-operational-status-form-submit").click();

  cy.getBySel("toc-detail-main-info-unit-operational-status-value").contains(
    TEXT.tocDetail.unitOperationalStatusPopup.updatedValue
  );
};

const checkFactoryFlagPopUp = () => {
  cy.getBySel("toc-detail-main-info-factory-flag-value").click();

  formRichTextField({
    fieldName: "add-log-popup-log",
    checkLabel: TEXT.tocDetail.factoryFlagPopUp.log.title,
    requiredError: TEXT.tocDetail.factoryFlagPopUp.log.error,
    setValue: TEXT.tocDetail.factoryFlagPopUp.log.value,
  });

  cy.getBySel("add-log-popup-submit").click();

  cy.getBySel("toc-detail-main-info-factory-flag-value").contains(
    TEXT.tocDetail.factoryFlagPopUp.updatedValue
  );
};

const checkDetailsTab = () => {
  cy.getBySel("breadcrumb-bar-link")
    .should("have.length", TEXT.tocDetail.breadcrumbs.length)
    .each(($value, idx) => {
      cy.wrap($value).contains(TEXT.tocDetail.breadcrumbs[idx]);
    });

  cy.getBySel("toc-detail-main-info-model-title").contains(
    TEXT.tocDetail.details.model.title
  );
  cy.getBySel("toc-detail-main-info-model-value").contains(
    TEXT.tocDetail.details.model.value
  );

  cy.getBySel("toc-detail-main-info-csr-title").contains(
    TEXT.tocDetail.details.csr.title
  );
  cy.getBySel("toc-detail-main-info-csr-value").contains(
    TEXT.tocDetail.details.csr.value
  );

  cy.getBySel("toc-detail-main-info-serial-number-title").contains(
    TEXT.tocDetail.details.equipmentRecord.title
  );
  cy.getBySel("toc-detail-main-info-serial-number-value").contains(
    TEXT.tocDetail.details.equipmentRecord.value
  );

  cy.getBySel("toc-detail-main-info-airport-code-title").contains(
    TEXT.tocDetail.details.aiport.title
  );
  cy.getBySel("toc-detail-main-info-airport-code-value").contains(
    TEXT.tocDetail.details.aiport.value
  );

  cy.getBySel("toc-detail-main-info-days-open-title").contains(
    TEXT.tocDetail.details.daysOpen.title
  );
  cy.getBySel("toc-detail-main-info-days-open-value").contains(
    TEXT.tocDetail.details.daysOpen.value
  );

  cy.getBySel("toc-detail-main-info-ifactor-title").contains(
    TEXT.tocDetail.details.ifactor.title
  );
  cy.getBySel("toc-detail-main-info-ifactor-value").contains(
    TEXT.tocDetail.details.ifactor.value
  );

  cy.getBySel("toc-detail-main-info-unit-operational-status-title").contains(
    TEXT.tocDetail.details.unitOperationalStatus.title
  );
  cy.getBySel("toc-detail-main-info-unit-operational-status-value").contains(
    TEXT.tocDetail.details.unitOperationalStatus.value
  );

  cy.getBySel("toc-detail-main-info-time-nmc-title").contains(
    TEXT.tocDetail.details.timeNmc.title
  );
  cy.getBySel("toc-detail-main-info-time-nmc-value").contains(
    TEXT.tocDetail.details.timeNmc.value
  );

  cy.getBySel("toc-detail-main-info-warranty-title").contains(
    TEXT.tocDetail.details.warranty.title
  );
  cy.getBySel("toc-detail-main-info-warranty-value").contains(
    TEXT.tocDetail.details.warranty.value
  );

  cy.getBySel("toc-detail-accordion-title").contains(
    TEXT.tocDetail.details.title
  );
  cy.getBySel("toc-detail-accordion-description").contains(
    TEXT.tocDetail.details.description
  );

  cy.getBySel("toc-detail-sub-accordion-assignee-title").contains(
    TEXT.tocDetail.details.assignee.title
  );
  cy.getBySel("toc-detail-sub-accordion-assignee-value").contains(
    TEXT.tocDetail.details.assignee.value
  );

  cy.getBySel("toc-detail-sub-accordion-technician-title").contains(
    TEXT.tocDetail.details.technician.title
  );
  cy.getBySel("toc-detail-sub-accordion-technician-value").contains(
    TEXT.tocDetail.details.technician.value
  );

  cy.getBySel("toc-detail-sub-accordion-error-code-title").contains(
    TEXT.tocDetail.details.errorCode.title
  );
  cy.getBySel("toc-detail-sub-accordion-error-code-value").contains(
    TEXT.tocDetail.details.errorCode.value
  );

  cy.getBySel("toc-detail-sub-accordion-service-activity-title").contains(
    TEXT.tocDetail.details.serviceActivity.title
  );
  cy.getBySel("toc-detail-sub-accordion-service-activity-value").contains(
    TEXT.tocDetail.details.serviceActivity.value
  );

  cy.getBySel("toc-detail-sub-accordion-payer-title").contains(
    TEXT.tocDetail.details.payer.title
  );
  cy.getBySel("toc-detail-sub-accordion-payer-value").contains(
    TEXT.tocDetail.details.payer.value
  );

  cy.getBySel("toc-detail-sub-accordion-created-by-title").contains(
    TEXT.tocDetail.details.createdBy.title
  );
  cy.getBySel("toc-detail-sub-accordion-created-by-value").contains(
    TEXT.tocDetail.details.createdBy.value
  );

  cy.getBySel("toc-detail-sub-accordion-created-at-title").contains(
    TEXT.tocDetail.details.createdAt.title
  );
  cy.getBySel("toc-detail-sub-accordion-created-at-value").contains(
    TEXT.tocDetail.details.createdAt.value
  );

  cy.getBySel("toc-detail-sub-accordion-buyer-title").contains(
    TEXT.tocDetail.details.buyer.title
  );
  cy.getBySel("toc-detail-sub-accordion-buyer-value").contains(
    TEXT.tocDetail.details.buyer.value
  );

  cy.getBySel("toc-detail-sub-accordion-user-title").contains(
    TEXT.tocDetail.details.endUser.title
  );
  cy.getBySel("toc-detail-sub-accordion-user-value").contains(
    TEXT.tocDetail.details.endUser.value
  );

  cy.getBySel("toc-detail-sub-accordion-maintainer-title").contains(
    TEXT.tocDetail.details.maintainer.title
  );
  cy.getBySel("toc-detail-sub-accordion-maintainer-value").contains(
    TEXT.tocDetail.details.maintainer.value
  );

  cy.getBySel("toc-detail-sub-accordion-hour-meter-title").contains(
    TEXT.tocDetail.details.hourMeter.title
  );
  cy.getBySel("toc-detail-sub-accordion-hour-meter-value").contains(
    TEXT.tocDetail.details.hourMeter.value
  );

  cy.getBySel("toc-detail-sub-accordion-manufacturing-location-title").contains(
    TEXT.tocDetail.details.manufacturingLocation.title
  );
  cy.getBySel("toc-detail-sub-accordion-manufacturing-location-value").contains(
    TEXT.tocDetail.details.manufacturingLocation.value
  );

  cy.getBySel("toc-detail-sub-accordion-sso-title").contains(
    TEXT.tocDetail.details.sso.title
  );
  cy.getBySel("toc-detail-sub-accordion-sso-value").contains(
    TEXT.tocDetail.details.sso.value
  );

  cy.getBySel("toc-detail-sub-accordion-sso-service-title").contains(
    TEXT.tocDetail.details.ssoService.title
  );
  cy.getBySel("toc-detail-sub-accordion-sso-service-value").contains(
    TEXT.tocDetail.details.ssoService.value
  );

  cy.getBySel("toc-detail-sub-accordion-tags-title").contains(
    TEXT.tocDetail.details.tags.title
  );
  cy.getBySel("toc-detail-sub-accordion-tags-value").contains(
    TEXT.tocDetail.details.tags.value
  );

  cy.getBySel("toc-detail-sub-accordion-commissioned-date-title").contains(
    TEXT.tocDetail.details.commissionDate.title
  );
  cy.getBySel("toc-detail-sub-accordion-commissioned-date-value").contains(
    TEXT.tocDetail.details.commissionDate.value
  );

  cy.getBySel("toc-detail-sub-accordion-customer-title").contains(
    TEXT.tocDetail.details.customer.title
  );
  cy.getBySel("toc-detail-sub-accordion-customer-value").contains(
    TEXT.tocDetail.details.customer.value
  );

  cy.getBySel("toc-detail-sub-accordion-third-party-name-title").contains(
    TEXT.tocDetail.details.thirdPartyName.title
  );
  cy.getBySel("toc-detail-sub-accordion-third-party-name-value").contains(
    TEXT.tocDetail.details.thirdPartyName.value
  );

  cy.getBySel("toc-detail-sub-accordion-third-party-ref-title").contains(
    TEXT.tocDetail.details.thirdPartyRef.title
  );
  cy.getBySel("toc-detail-sub-accordion-third-party-ref-value").contains(
    TEXT.tocDetail.details.thirdPartyRef.value
  );

  cy.getBySel("toc-detail-sub-accordion-serial-number-title").contains(
    TEXT.tocDetail.details.serialNumber.title
  );
  cy.getBySel("toc-detail-sub-accordion-serial-number-value").contains(
    TEXT.tocDetail.details.serialNumber.value
  );

  cy.getBySelLike("toc-detail-sub-accordion-tag-pill").should(
    "have.length.at.least",
    1
  );

  cy.getBySel("toc-detail-sub-accordion-symptoms-title").contains(
    TEXT.tocDetail.details.symptoms.title
  );
  cy.getBySel("toc-detail-sub-accordion-symptoms-value").contains(
    TEXT.tocDetail.details.symptoms.value
  );

  cy.getBySel("toc-detail-sub-accordion-root-cause-title").contains(
    TEXT.tocDetail.details.rootCause.title
  );
  cy.getBySel("toc-detail-sub-accordion-root-cause-value").contains(
    TEXT.tocDetail.details.rootCause.value
  );

  cy.getBySel("toc-detail-sub-accordion-solution-title").contains(
    TEXT.tocDetail.details.solution.title
  );
  cy.getBySel("toc-detail-sub-accordion-solution-value").contains(
    TEXT.tocDetail.details.solution.value
  );

  cy.getBySel("toc-detail-sub-accordion-contact-title").contains(
    TEXT.tocDetail.details.contacts.title
  );
  cy.getBySel("toc-detail-sub-accordion-main-contact-heading").contains(
    TEXT.tocDetail.details.contacts.mainContact.title
  );
  cy.getBySel("toc-detail-sub-accordion-main-contact-pill").contains(
    TEXT.tocDetail.details.contacts.mainContact.value
  );
  cy.getBySel("toc-detail-sub-accordion-additional-contact-heading").contains(
    TEXT.tocDetail.details.contacts.additonalContacts.title
  );
  cy.getBySel("toc-detail-sub-accordion-additional-contact-pill").contains(
    TEXT.tocDetail.details.contacts.additonalContacts.value
  );

  cy.getBySel("toc-detail-linked-csr-title").contains(
    TEXT.tocDetail.csr.heading
  );
  cy.getBySel("toc-detail-linked-csr-0-id").contains(TEXT.tocDetail.csr.id);
  cy.getBySel("toc-detail-linked-csr-0-status").contains(
    TEXT.tocDetail.csr.status
  );
  cy.getBySel("toc-detail-linked-csr-0-closed").contains(
    TEXT.tocDetail.csr.isClosed
  );
  cy.getBySel("toc-detail-linked-csr-0-title").contains(
    TEXT.tocDetail.csr.title
  );

  cy.getBySel("toc-detail-sub-accordion-outdated-title").contains(
    TEXT.tocDetail.details.outdated.title
  );
  cy.getBySel("toc-detail-sub-accordion-outdated-value").contains(
    TEXT.tocDetail.details.outdated.value
  );

  cy.getBySel("toc-detail-sub-accordion-emission-rating-title").contains(
    TEXT.tocDetail.details.emissionRating.title
  );
  cy.getBySel("toc-detail-sub-accordion-emission-rating-value").contains(
    TEXT.tocDetail.details.emissionRating.value
  );
};

const checkServiceBulletins = () => {
  cy.visit("/toc/details/1");
  cy.getBySel("toc-detail-main-info-service-bulletins-title").contains(
    TEXT.tocDetail.serviceBulletins.title
  );
  cy.getBySel("toc-detail-main-info-service-bulletins-value")
    .contains(TEXT.tocDetail.serviceBulletins.value)
    .click();
  cy.getBySel("service-bulletins-popup-heading").contains(
    TEXT.tocDetail.serviceBulletins.popup.heading
  );
  cy.getBySel("service-bulletins-0-id").contains(
    TEXT.tocDetail.serviceBulletins.popup.id
  );
  cy.getBySel("service-bulletins-0-type").contains(
    TEXT.tocDetail.serviceBulletins.popup.type
  );
  cy.getBySel("service-bulletins-0-status").contains(
    TEXT.tocDetail.serviceBulletins.popup.status
  );
  cy.getBySel("service-bulletins-0-title").contains(
    TEXT.tocDetail.serviceBulletins.popup.title
  );
  cy.getBySel("service-bulletins-0-description").contains(
    TEXT.tocDetail.serviceBulletins.popup.description
  );
  cy.get("body").click(0, 0);
};

const checkNavigation = () => {
  cy.getBySel("toc-detail-main-info-serial-number-value").click();
  cy.url().should("include", TEXT.tocDetail.serialLink);

  cy.get("@tocId").then((tocId) => {
    cy.visit(`/toc/details/${tocId}`);
    cy.getBySel("toc-detail-main-info-csr-title").click();
    cy.url().should("match", TEXT.tocDetail.csrLink);
  });
};

const setup = () => {
  cy.clearIndexedDB();
  cy.interceptApi(API_CALL.getUnitOperationalStatusList);
  cy.visit("/");
  cy.loginWithUser(TEST_USER_CSM.username, TEST_USER_CSM.password);
  cy.window().then((window) => {
    postTocAndCsrWithDummyData(window);
  });
  cy.get("@tocId").then((tocId) => {
    cy.visit(`/toc/details/${tocId}`);
  });
};

describe("TOC Page", () => {
  it("Detail Functionality", () => {
    setup();
    checkDetailsTab();
    checkUnitOperationalStatusPopUp();
    checkFactoryFlagPopUp();
    checkServiceBulletins();
    checkNavigation();
  });
});
