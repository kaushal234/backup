import { API_CALL } from "../constants/api";
import { TEXT } from "../constants/strings";

const checkBasicListing = () => {
  cy.getBySel("breadcrumb-bar-link")
    .should("have.length", TEXT.erList.breadcrumbs.length)
    .each(($value, idx) => {
      cy.wrap($value).contains(TEXT.erList.breadcrumbs[idx]);
    });
  cy.getBySel("er-list-results")
    .invoke("text")
    .should("match", /Results \(\d+\)/i);
  cy.getBySel("er-list-no-data").contains(TEXT.erList.noErAvailable);
  cy.waitForApiWithLoader(API_CALL.getErList, 200);
  cy.getBySel("er-list-refresh").click();
  cy.waitForApiWithLoader(API_CALL.getErList, 200);

  cy.getBySel("er-card").should("have.length", 5);

  cy.getBySel("er-card")
    .first()
    .findBySel("er-card-value")
    .should("have.length", TEXT.erList.cardValues.length)
    .each(($value, idx) => {
      cy.wrap($value).contains(TEXT.erList.cardValues[idx]);
    });
};

const checkPagination = () => {
  cy.getByClassName("er-list-pagination", "li")
    .should("have.length.at.least", 4)
    .eq(2)
    .click();
  cy.waitForApiWithLoader(API_CALL.getErList, 200);
  cy.getBySel("er-card").should("have.length.at.least", 1);
  cy.getByClassName("er-list-pagination", "li")
    .eq(2)
    .find("button")
    .should("have.class", "Mui-selected");
};

const checkSorting = () => {
  const noOfSort = TEXT.erList.sortValue.length;
  cy.getBySel("er-list-sort-icon").click();
  cy.getBySelLike("er-list-sort-option-").should("have.length", noOfSort);
  cy.getBySelLike("er-list-sort-option-")
    .eq(noOfSort - 1)
    .click();
  cy.waitForApiWithLoader(API_CALL.getErList, 200);

  const params: Array<{ [key: string]: string }> = [
    { "order[serialNumber]": "ASC" },
    { "order[serialNumber]": "DESC" },
  ];

  for (let i = 0; i < noOfSort; i++) {
    cy.getBySel("er-list-sort-icon").click();
    cy.getBySelLike("er-list-sort-option-")
      .eq(i)
      .contains(TEXT.erList.sortValue[i])
      .click();
    cy.waitForApiWithLoader(API_CALL.getErList, 200, params[i]);
  }

  cy.getBySel("er-list-sort-icon").click();
  cy.getBySelLike("er-list-sort-option-")
    .eq(noOfSort - 1)
    .click();
};

const checkNavigation = () => {
  cy.getBySel("er-card").eq(0).click();
  cy.url().should("include", TEXT.erList.cardLink);
};

const setup = () => {
  cy.clearIndexedDB();
  cy.interceptApi(API_CALL.getErList);
  cy.visit("/");
  cy.loginWithUser();
  cy.visit("/er");
};

describe("ER List Page", () => {
  it("Basic Functionality", () => {
    setup();
    checkBasicListing();
    checkPagination();
    checkSorting();
    checkNavigation();
  });
});
