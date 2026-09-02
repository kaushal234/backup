import { formApiAutoCompleteDropdown } from "../common/formApiAutoCompleteDropdown";
import { formMultiSelectDropdown } from "../common/formMultiSelectDropdown";
import { API_CALL } from "../constants/api";
import { TEXT } from "../constants/strings";

const checkBasicListing = () => {
  cy.getBySel("breadcrumb-bar-link")
    .should("have.length", TEXT.tocList.breadcrumbs.length)
    .each(($value, idx) => {
      cy.wrap($value).contains(TEXT.tocList.breadcrumbs[idx]);
    });
  cy.getBySel("toc-list-results")
    .invoke("text")
    .should("match", /Results \(\d+\)/i);
  cy.getBySel("toc-list-no-data").contains(TEXT.tocList.noTocAvailable);
  cy.waitForApiWithLoader(API_CALL.getTocList, 200);
  cy.getBySel("toc-list-refresh").click();
  cy.waitForApiWithLoader(API_CALL.getTocList, 200);

  cy.getBySel("toc-list-filter-icon").click();
  formApiAutoCompleteDropdown({
    fieldName: "toc-filter-service-organisation",
    selectValue: TEXT.tocFilters.serviceOrganisation.value,
  });
  formApiAutoCompleteDropdown({
    fieldName: "toc-filter-service-organisation",
    clear: true,
  });
  cy.getBySel("toc-filter-submit").click();
  cy.waitForApiWithLoader(API_CALL.getTocList, 200);

  cy.getBySel("toc-card").should("have.length", 5);

  cy.getBySel("toc-card")
    .first()
    .findBySel("toc-card-value")
    .should("have.length", 8)
    .each(($value, idx) => {
      cy.wrap($value).contains(TEXT.tocList.cardValues[idx]);
    });

  cy.getBySel("toc-card")
    .first()
    .findBySel("toc-card-image")
    .should("be.visible");
};

const checkPagination = () => {
  cy.getByClassName("toc-list-pagination", "li")
    .should("have.length.at.least", 4)
    .eq(2)
    .click();
  cy.waitForApiWithLoader(API_CALL.getTocList, 200);
  cy.getBySel("toc-card").should("have.length.at.least", 1);
  cy.getByClassName("toc-list-pagination", "li")
    .eq(2)
    .find("button")
    .should("have.class", "Mui-selected");
};

const checkSorting = () => {
  const noOfSort = TEXT.tocList.sortValue.length;
  cy.getBySel("toc-list-sort-icon").click();
  cy.getBySelLike("toc-list-sort-option-").should("have.length", noOfSort);
  cy.getBySelLike("toc-list-sort-option-")
    .eq(noOfSort - 1)
    .click();
  cy.waitForApiWithLoader(API_CALL.getTocList, 200);

  const params: Array<{ [key: string]: string }> = [
    { "order[airport.code]": "ASC" },
    { "order[airport.code]": "DESC" },
    { "order[indiceFactor]": "ASC" },
    { "order[indiceFactor]": "DESC" },
    { "order[createdAt]": "ASC" },
    { "order[createdAt]": "DESC" },
    { "order[updatedAt]": "ASC" },
    { "order[updatedAt]": "DESC" },
  ];

  for (let i = 0; i < noOfSort; i++) {
    cy.getBySel("toc-list-sort-icon").click();
    cy.getBySelLike("toc-list-sort-option-")
      .eq(i)
      .contains(TEXT.tocList.sortValue[i])
      .click();
    cy.waitForApiWithLoader(API_CALL.getTocList, 200, params[i]);
  }

  cy.getBySel("toc-list-sort-icon").click();
  cy.getBySelLike("toc-list-sort-option-")
    .eq(noOfSort - 1)
    .click();
};

const checkNavigation = () => {
  cy.getBySel("toc-card").eq(0).click();
  cy.url().should("match", TEXT.tocList.cardLink);
};

const setup = () => {
  cy.clearIndexedDB();
  cy.interceptApi(API_CALL.getTocList);
  cy.visit("/");
  cy.loginWithUser();
  cy.visit("/toc");
};

describe("TOC List Page", () => {
  it("Basic Functionality", () => {
    setup();
    checkBasicListing();
    checkPagination();
    checkSorting();
    checkNavigation();
  });
});
