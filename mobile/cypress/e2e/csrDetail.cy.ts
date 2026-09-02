import { postTocAndCsrWithDummyData } from "../api/postTocWithCsr";
import { formRichTextField } from "../common/formRichTextField";
import { TEST_USER_CSM } from "../constants/constants";
import { TEXT } from "../constants/strings";

const checkDetailsTab = () => {
  cy.getBySel("breadcrumb-bar-link")
    .should("have.length", TEXT.csrDetail.breadcrumbs.length)
    .each(($value, idx) => {
      cy.wrap($value).contains(TEXT.csrDetail.breadcrumbs[idx]);
    });
  cy.getBySel("csr-detail-main-info-model-title").contains(
    TEXT.csrDetail.details.model.title
  );
  cy.getBySel("csr-detail-main-info-model-value").contains(
    TEXT.csrDetail.details.model.value
  );
  cy.getBySel("csr-detail-main-info-toc-title").contains(
    TEXT.csrDetail.details.toc.title
  );
  cy.getBySel("csr-detail-main-info-toc-value").contains(
    TEXT.csrDetail.details.toc.value
  );
  cy.getBySel("csr-detail-main-info-serial-number-title").contains(
    TEXT.csrDetail.details.serialNumber.title
  );
  cy.getBySel("csr-detail-main-info-serial-number-value").contains(
    TEXT.csrDetail.details.serialNumber.value
  );
  cy.getBySel("csr-detail-main-info-airport-code-title").contains(
    TEXT.csrDetail.details.aiport.title
  );
  cy.getBySel("csr-detail-main-info-airport-code-value").contains(
    TEXT.csrDetail.details.aiport.value
  );
  cy.getBySel("csr-detail-main-info-days-open-title").contains(
    TEXT.csrDetail.details.daysOpen.title
  );
  cy.getBySel("csr-detail-main-info-days-open-value").contains(
    TEXT.csrDetail.details.daysOpen.value
  );
  cy.getBySel("csr-detail-main-info-type-title").contains(
    TEXT.csrDetail.details.type.title
  );
  cy.getBySel("csr-detail-main-info-type-value").contains(
    TEXT.csrDetail.details.type.value
  );
  cy.getBySel("csr-detail-accordion-title").contains(
    TEXT.csrDetail.details.title
  );
  cy.getBySel("csr-detail-accordion-description").contains(
    TEXT.csrDetail.details.description
  );
  cy.getBySel("csr-detail-sub-accordion-created-by-title").contains(
    TEXT.csrDetail.details.createdBy.title
  );
  cy.getBySel("csr-detail-sub-accordion-created-by-value").contains(
    TEXT.csrDetail.details.createdBy.value
  );
  cy.getBySel("csr-detail-sub-accordion-created-at-title").contains(
    TEXT.csrDetail.details.createdAt.title
  );
  cy.getBySel("csr-detail-sub-accordion-created-at-value").contains(
    TEXT.csrDetail.details.createdAt.value
  );
  cy.getBySel("csr-detail-sub-accordion-buyer-title").contains(
    TEXT.csrDetail.details.buyer.title
  );
  cy.getBySel("csr-detail-sub-accordion-buyer-value").contains(
    TEXT.csrDetail.details.buyer.value
  );
  cy.getBySel("csr-detail-sub-accordion-user-title").contains(
    TEXT.csrDetail.details.endUser.title
  );
  cy.getBySel("csr-detail-sub-accordion-user-value").contains(
    TEXT.csrDetail.details.endUser.value
  );
  cy.getBySel("csr-detail-sub-accordion-maintainer-title").contains(
    TEXT.csrDetail.details.maintainer.title
  );
  cy.getBySel("csr-detail-sub-accordion-maintainer-value").contains(
    TEXT.csrDetail.details.maintainer.value
  );
  cy.getBySel("csr-detail-sub-accordion-hour-meter-title").contains(
    TEXT.csrDetail.details.hourMeter.title
  );
  cy.getBySel("csr-detail-sub-accordion-hour-meter-value").contains(
    TEXT.csrDetail.details.hourMeter.value
  );
  cy.getBySel("csr-detail-sub-accordion-manufacturing-location-title").contains(
    TEXT.csrDetail.details.manufacturingLocation.title
  );
  cy.getBySel("csr-detail-sub-accordion-manufacturing-location-value").contains(
    TEXT.csrDetail.details.manufacturingLocation.value
  );
  cy.getBySel("csr-detail-sub-accordion-sso-title").contains(
    TEXT.csrDetail.details.sso.title
  );
  cy.getBySel("csr-detail-sub-accordion-sso-value").contains(
    TEXT.csrDetail.details.sso.value
  );
  cy.getBySel("csr-detail-sub-accordion-sso-service-title").contains(
    TEXT.csrDetail.details.ssoService.title
  );
  cy.getBySel("csr-detail-sub-accordion-sso-service-value").contains(
    TEXT.csrDetail.details.ssoService.value
  );
  cy.getBySel("csr-detail-sub-accordion-planned-at-title").contains(
    TEXT.csrDetail.details.plannedAt.title
  );
  cy.getBySel("csr-detail-sub-accordion-planned-at-value").contains(
    TEXT.csrDetail.details.plannedAt.value
  );
  cy.getBySel("csr-detail-sub-accordion-commisionned-date-title").contains(
    TEXT.csrDetail.details.commissionDate.title
  );
  cy.getBySel("csr-detail-sub-accordion-commisionned-date-value").contains(
    TEXT.csrDetail.details.commissionDate.value
  );
  cy.getBySel("csr-detail-sub-accordion-technician-accordion-title").contains(
    TEXT.csrDetail.details.technicianAccordion
  );
  cy.getBySel("csr-detail-sub-accordion-full-name-title").contains(
    TEXT.csrDetail.details.fullName.title
  );
  cy.getBySel("csr-detail-sub-accordion-full-name-value").contains(
    TEXT.csrDetail.details.fullName.value
  );
  cy.getBySel("csr-detail-sub-accordion-email-title").contains(
    TEXT.csrDetail.details.email.title
  );
  cy.getBySel("csr-detail-sub-accordion-email-value").contains(
    TEXT.csrDetail.details.email.value
  );
};

const checkFactoryFlagPopUp = () => {
  cy.getBySel("csr-detail-main-info-factory-flag-value").click();

  formRichTextField({
    fieldName: "add-log-popup-log",
    checkLabel: TEXT.csrDetail.factoryFlagPopUp.log.title,
    requiredError: TEXT.csrDetail.factoryFlagPopUp.log.error,
    setValue: TEXT.csrDetail.factoryFlagPopUp.log.value,
  });

  cy.getBySel("add-log-popup-submit").click();

  cy.getBySel("csr-detail-main-info-factory-flag-value").contains(
    TEXT.csrDetail.factoryFlagPopUp.updatedValue
  );
};

const checkNavigation = () => {
  cy.getBySel("csr-detail-main-info-serial-number-value").click();
  cy.url().should("include", TEXT.csrDetail.serialLink);

  cy.get("@csrId").then((csrId) => {
    cy.visit(`/csr/details/${csrId}`);
    cy.getBySel("csr-detail-main-info-toc-value").click();
    cy.url().should("match", TEXT.csrDetail.tocLink);

    cy.visit(`/csr/details/${csrId}`);
    cy.getBySel("csr-detail-sub-accordion-full-name-value").find("a").click();
    cy.url().should("include", TEXT.csrDetail.technicianLink);
  });
};

const setup = () => {
  cy.clearIndexedDB();
  cy.visit("/");
  cy.loginWithUser(TEST_USER_CSM.username, TEST_USER_CSM.password);
  cy.window().then((window) => {
    postTocAndCsrWithDummyData(window);
  });
  cy.get("@csrId").then((csrId) => {
    cy.visit(`/csr/details/${csrId}`);
  });
};

describe("CSR Page", () => {
  it("Detail Functionality", () => {
    setup();
    checkDetailsTab();
    checkFactoryFlagPopUp();
    checkNavigation();
  });
});
