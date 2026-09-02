import { TEXT } from "../constants/strings";

const checkDetailsTab = () => {
  cy.getBySel("breadcrumb-bar-link")
    .should("have.length", TEXT.erDetail.breadcrumbs.length)
    .each(($value, idx) => {
      cy.wrap($value).contains(TEXT.erDetail.breadcrumbs[idx]);
    });

  cy.getBySel("er-detail-main-info-model-title").contains(
    TEXT.erDetail.details.model.title
  );
  cy.getBySel("er-detail-main-info-model-value").contains(
    TEXT.erDetail.details.model.value
  );

  cy.getBySel("er-detail-main-info-commissioned-date-title").contains(
    TEXT.erDetail.details.commissionDate.title
  );
  cy.getBySel("er-detail-main-info-commissioned-date-value").contains(
    TEXT.erDetail.details.commissionDate.value
  );

  cy.getBySel("er-detail-main-info-type-title").contains(
    TEXT.erDetail.details.type.title
  );
  cy.getBySel("er-detail-main-info-type-value").contains(
    TEXT.erDetail.details.type.value
  );

  cy.getBySel("er-detail-main-info-airport-title").contains(
    TEXT.erDetail.details.airport.title
  );
  cy.getBySel("er-detail-main-info-airport-value").contains(
    TEXT.erDetail.details.airport.value
  );

  cy.getBySel("er-detail-main-info-hour-meter-title").contains(
    TEXT.erDetail.details.hourMeter.title
  );
  cy.getBySel("er-detail-main-info-hour-meter-value").contains(
    TEXT.erDetail.details.hourMeter.value
  );

  cy.getBySel("er-detail-main-info-state-title").contains(
    TEXT.erDetail.details.state.title
  );
  cy.getBySel("er-detail-main-info-state-value").contains(
    TEXT.erDetail.details.state.value
  );

  cy.getBySel("er-detail-main-info-open-toc-title").contains(
    TEXT.erDetail.details.openToc.title
  );
  cy.getBySel("er-detail-main-info-open-toc-value").contains(
    TEXT.erDetail.details.openToc.value
  );

  cy.getBySel("er-detail-main-info-closed-toc-title").contains(
    TEXT.erDetail.details.closedToc.title
  );
  cy.getBySel("er-detail-main-info-closed-toc-value").contains(
    TEXT.erDetail.details.closedToc.value
  );

  cy.getBySel("er-detail-sub-accordion-serial-number-title").contains(
    TEXT.erDetail.details.serialNumber.title
  );
  cy.getBySel("er-detail-sub-accordion-serial-number-value").contains(
    TEXT.erDetail.details.serialNumber.value
  );

  cy.getBySel("er-detail-sub-accordion-status-title").contains(
    TEXT.erDetail.details.status.title
  );
  cy.getBySel("er-detail-sub-accordion-status-value").contains(
    TEXT.erDetail.details.status.value
  );

  cy.getBySel("er-detail-sub-accordion-emission-rating-title").contains(
    TEXT.erDetail.details.emissionRating.title
  );
  cy.getBySel("er-detail-sub-accordion-emission-rating-value").contains(
    TEXT.erDetail.details.emissionRating.value
  );

  cy.getBySel("er-detail-sub-accordion-manufacturing-location-title").contains(
    TEXT.erDetail.details.manufacturingLocation.title
  );
  cy.getBySel("er-detail-sub-accordion-manufacturing-location-value").contains(
    TEXT.erDetail.details.manufacturingLocation.value
  );

  cy.getBySel("er-detail-sub-accordion-ship-date-title").contains(
    TEXT.erDetail.details.shipDate.title
  );
  cy.getBySel("er-detail-sub-accordion-ship-date-value").contains(
    TEXT.erDetail.details.shipDate.value
  );

  cy.getBySel("er-detail-sub-accordion-gt-date-title").contains(
    TEXT.erDetail.details.gtDate.title
  );
  cy.getBySel("er-detail-sub-accordion-gt-date-value").contains(
    TEXT.erDetail.details.gtDate.value
  );

  cy.getBySel("er-detail-sub-accordion-buyer-title").contains(
    TEXT.erDetail.details.buyer.title
  );
  cy.getBySel("er-detail-sub-accordion-buyer-value").contains(
    TEXT.erDetail.details.buyer.value
  );

  cy.getBySel("er-detail-sub-accordion-end-user-title").contains(
    TEXT.erDetail.details.endUser.title
  );
  cy.getBySel("er-detail-sub-accordion-end-user-value").contains(
    TEXT.erDetail.details.endUser.value
  );

  cy.getBySel("er-detail-sub-accordion-installed-options-title").contains(
    TEXT.erDetail.details.installedOptions.title
  );
  cy.getBySel("er-detail-sub-accordion-installed-options-value").contains(
    TEXT.erDetail.details.installedOptions.value
  );

  cy.getBySel("er-detail-sub-accordion-dimensions-heading").contains(
    TEXT.erDetail.details.dimensionsHeading
  );

  cy.getBySel("er-detail-sub-accordion-length-title").contains(
    TEXT.erDetail.details.length.title
  );
  cy.getBySel("er-detail-sub-accordion-length-value").contains(
    TEXT.erDetail.details.length.value
  );

  cy.getBySel("er-detail-sub-accordion-width-title").contains(
    TEXT.erDetail.details.width.title
  );
  cy.getBySel("er-detail-sub-accordion-width-value").contains(
    TEXT.erDetail.details.width.value
  );

  cy.getBySel("er-detail-sub-accordion-height-title").contains(
    TEXT.erDetail.details.height.title
  );
  cy.getBySel("er-detail-sub-accordion-height-value").contains(
    TEXT.erDetail.details.height.value
  );

  cy.getBySel("er-detail-sub-accordion-weight-title").contains(
    TEXT.erDetail.details.weight.title
  );
  cy.getBySel("er-detail-sub-accordion-weight-value").contains(
    TEXT.erDetail.details.weight.value
  );
};

const setup = () => {
  cy.clearIndexedDB();
  cy.visit("/");
  cy.loginWithUser();
  cy.visit("/er/details/1");
};

describe("ER Page", () => {
  it("Detail Functionality", () => {
    setup();
    checkDetailsTab();
  });
});
